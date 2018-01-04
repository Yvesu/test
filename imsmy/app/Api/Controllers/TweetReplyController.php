<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 20:01
 */

namespace App\Api\Controllers;

use App\Api\Transformer\TweetThirdlyRepliesTransformer;
use App\Api\Transformer\TweetOriginRepliesTransformer;
use App\Models\Notification;
use App\Models\{Tweet,Blacklist};
use App\Models\Friend;
use App\Models\TweetReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Api\Controllers\CommonController;
use Auth;
use DB;

/**
 * 动态-回复相关接口
 *
 * @Resource("TweetsReplies",uri="users/{id}/tweets/{tweet_id}/replies")
 */
class TweetReplyController extends BaseController
{
    // 页码
    protected $paginate = 20;

    protected $tweetThirdlyRepliesTransformer;
    protected $tweetOriginRepliesTransformer;

    public function __construct(

        TweetThirdlyRepliesTransformer $tweetThirdlyRepliesTransformer,
        TweetOriginRepliesTransformer $tweetOriginRepliesTransformer
    )
    {
        $this->tweetThirdlyRepliesTransformer = $tweetThirdlyRepliesTransformer;
        $this->tweetOriginRepliesTransformer = $tweetOriginRepliesTransformer;
    }

    /**
     * 回复动态或动态中的评论
     *
     * @Post("/")
     * @Versions({"v1"})
     *  @Parameters({
     *      @Parameter("reply", description="回复评论时不为空，为回复人的ID"),
     *      @Parameter("content",required=true, description="回复内容，不能为空"),
     *      @Parameter("grade", description="评分"),
     *      @Parameter("barrage",description="如果发的是弹幕，添加上发弹幕时视频时间")
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(201,body={"id":"该回复ID"}),
     *     @Response(404,body={"error":"tweet_not_found"}),
     * })
     */
    public function create($id,$tweet_id,Request $request)
    {
        try{
            // 获取评论内容,过滤，防止SQL注入
            $content = removeXSS($request->get('content'));

            // 判断内容是否为空或者长度超过144个汉字，返回错误
            if(null == $content || mb_strlen($content,'utf-8') > 300){
                return response()->json(['error' => 'content_not_standard'], 403);
            }

            $tweet = Tweet::able()->findOrFail($tweet_id);

            // 判断是否在黑名单内
            if(Blacklist::ofBlackIds($id,$tweet->user_id)->first()){

                // 在自己的黑名单中
                return response()->json(['error'=>'in_own_black_list'],431);
            } elseif (Blacklist::ofBlackIds($tweet->user_id,$id)->first()){

                // 在对方的黑名单中
                return response()->json(['error'=>'in_his_black_list'],432);
            }

            // 判断是否允许陌生人评论，朋友关系可以评论
            $personalAllow = new CommonController();
            if(!$personalAllow->personalAllow($id,$tweet->user_id,'stranger_comment'))
                return response()->json(['error'=>'stranger_cannot_reply'],433);

            // 获取用户是否为匿名评论
            $anonymity = 1 == $request -> get('anonymity',0) ? 1 : 0;

            // 初始化存入tweet表中的评分数据
            $grade_tweet = 0;

            // 如果用户评分不为空
            if($grade = $request->get('grade',0)){

                // 判断用户评分是否在范围内
                if($grade < 0 || $grade > 10) return response() -> json(['error' => 'illegal_grade'],401);

                // 判断用户是否已经对该动态进行评分
                $grade_able = TweetReply::where('user_id',$id)
                    -> where('tweet_id',$tweet_id)
                    -> where('grade','<>',0)
                    -> first();

                // 如果用户未对该动态评过分，将分数计入tweet表
                if(!$grade_able) $grade_tweet = $grade;
            }

            // 创建新评论
            $newReply = [
                'user_id'  => $id,
                'tweet_id' => $tweet_id,
                'grade'    => $grade,
                'reply_id' => $request->get('reply_id'),
                'reply_user_id' => $request->get('reply_user_id'),
//                'barrage'  => $request->get('barrage'),
                'content'  => $content,
                'anonymity'=> $anonymity,
            ];

            $time = new Carbon();

            // 发给被评论者的提醒消息
            $newNotice = [
                'user_id'           => (int)$id,
                'notice_user_id'    => $tweet->user_id,
                'type'              => 2,
                'type_id'           => $tweet_id,
                'created_at'        => $time,
                'updated_at'        => $time
            ];

//            DB::beginTransaction();

            // 判断内容是否有@人信息
            $at = $this->regexAt($newReply['content']);

            // 将评论存入评论表
            $reply = TweetReply::create($newReply);

            // 判断是否开启了评论消息提醒
            if(1 === User::findOrFail($tweet->user_id)->new_message_comment){

                // 将新生成的评论信息id作为提醒表中的type_id
                $newNotice['type_id'] = $reply->id;
                $data[] = $newNotice;

                if(null != $newReply['reply_id']){
                    $data[] = [
                        'user_id'           => (int)$newNotice['user_id'],
                        'notice_user_id'    => $request->get('reply_user_id'),
                        'type'              => 4,
                        'type_id'           => $newNotice['type_id'],
                        'created_at'        => $time,
                        'updated_at'        => $time,
                    ];
                    // 发给评论者的提醒消息
                    Notification::create($data[1]);

                    $this->createNotification($reply,$at);
                }else{
                    // 发给评论者的提醒消息
                    Notification::create($data[0]);

                    $this->createNotification($reply,$at);
                }

            }

            // 将tweet表中的评论总数加1
            $tweet->reply_count ++;

            // 将评分存入tweet表中
            if($grade_tweet) {

                // 将tweet表中的评分总次数加1
                $tweet->tweet_grade_times ++;

                // 将新增评分存入tweet表中
                $tweet->tweet_grade_total += $grade_tweet;
            }

            // 保存到数据表中
            $tweet->save();

            // 获取更新后的评分
            if($tweet->tweet_grade_times) {

                $reply -> tweet_grade_total = number_format($tweet->tweet_grade_total/$tweet->tweet_grade_times,1);
            } else {
                $reply -> tweet_grade_total = 0;
            }

//            DB::commit();

            // 返回新生成评论数据的id
            return response()->json([
                'id'            => $reply->id,
                'content'       => $reply->content,
                'tweet_grade'   => $reply -> tweet_grade_total <= 9.8 ? $reply -> tweet_grade_total : 9.8,
                'anonymity'     => $reply->anonymity,
            ],201);

        } catch (ModelNotFoundException $e){
            DB::rollBack();
            return response()->json(['error' => 'tweet_not_found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 删除回复
     * @Delete("/{id}")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(404,body={"error":"tweet_reply_not_found"}),
     * })
     *
     */
    public function destroy($id,$tweet_id,$reply_id)
    {
        try{
            // 从tweet_reply表中获取该评论信息
            $reply = TweetReply::where('tweet_id',$tweet_id)
                            ->where('user_id',$id)
                            ->status()
                            ->findOrFail($reply_id);

            // 修改状态
            $reply -> status  = 2;

            // 删除提醒（在本评论中被@的）
            Notification::where('type',3)->where('type_id',$tweet_id)->delete();

            // 自己发的动态 删除被评论的提醒
            Notification::where('type',2)->where('type_id',$reply_id)->delete();

            // 查询 tweet 表中该动态数据
            $tweet = Tweet::findOrFail($tweet_id);

            // 将tweet表中的评论总数减1
            $tweet->reply_count --;

            // 判断该评论中积分是否为0
            if($reply->grade && empty($reply->reply_id)){

                // 判断此评分前是否还有其他评分
                $grade = TweetReply::where('tweet_id',$tweet_id)
                    ->where('user_id',$id)
                    ->where('grade','<>',0)
                    ->where('id','<',$reply_id)
                    ->whereNull('reply_id')
                    ->status()
                    ->first();

                // 如果有则不需要减总评分
                if(!$grade){

                    // 将tweet表中相应动态总积分减少相应值
                    $tweet -> tweet_grade_total -= $reply->grade;

                    // 将tweet表中相应评分次数减1
                    $tweet -> tweet_grade_times --;
                }
            }

            // 保存
            $tweet->save();

            // 删除评论
            $reply->save();

            return response('',204);

        } catch (ModelNotFoundException $e){
            return response()->json(['error' => 'tweet_reply_not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 评论详情
     *
     * @param $id 动态id
     * @param $reply_id 评论id
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function details($id,$reply_id,Request $request)
    {
        try {

            // 获取要查询的动态详情
            $tweets_data = Tweet::able()->findOrFail($id);

            // 判断是否只有好友可看
            if(0 !== $tweets_data->visible){

                // 判断用户是否为登录状态
                if(!$user = Auth::guard('api')->user()) return response() -> json(['error'=>'forbid'],403);

                // 判断是否为好友关系
                if(!Friend::ofIsFriend($user->id, $tweets_data->user_id)->first())
                    return response()->json(['error'=>'forbid'],403);

                // 好友关系下，是否有权限观看该动态 是否在指定好友可看范围内
                if(4 === $tweets_data->visible){
                    if(!substr_count($tweets_data->visible_range,$user->id))
                        return response()->json(['error'=>'forbid'],403);
                }

                // 好友关系下，是否有权限观看该动态 是否不在非可看名单内
                if(5 === $tweets_data->visible){
                    if(substr_count($tweets_data->visible_range,$user->id))
                        return response()->json(['error'=>'forbid'],403);
                }
            }

            // 判断原始评论是否存在
            if(!$origin = TweetReply::status() -> whereNull('reply_id') -> find($reply_id))
                return response()->json(['error'=>'not_found'],404);

            // 取id大于$last_id的20条评论，按id倒叙
            $replys = TweetReply::with('belongsToUser')
                -> where('reply_id',$reply_id)
                -> orderBy('id','desc')
                -> ofSecond((int)$request -> get('last_id'))
                -> status()
                -> take($this->paginate)
                -> get();

            // 统计或获取数据的数量
            $count = $replys->count();

            // 返回评论数据
            return [

                // 本条一级评论的数据
                'origin'  => $this->tweetOriginRepliesTransformer->transform($origin),

                // 本次评论的回复数据
                'replys'  =>  $count ? $this->tweetThirdlyRepliesTransformer->transformCollection($replys->all()) : [],

                // 本次获取数据的总数量
                'count'   => $count,

                // 下次请求的链接，如果本次获取条数不为0，将请求条件附带上
                'link'    => $count
                    ? $request->url() .
                    '?last_id=' . $replys -> last() -> id // 最后一条信息的id
                    : null      // 如果数量为0，则不附带搜索条件
            ];

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 验证动态 content中内容是否包括@数据 并返回@人的id
     * @param $content
     * @return null|array
     */
    protected function regexAt($content)
    {
        $match_at = regex_at($content);
        if ($match_at[0]) {
            $arr = array_map(function($value){
                return str_replace(' ','',str_replace('@','',$value));
            },$match_at[1][0]);
            return User::whereIn('nickname',$arr)->get(['id'])->pluck('id')->all();
        }
        return null;
    }

    /**
     * 创建提醒信息，存入提醒 notification表
     * @param $reply 新产生的评论信息
     * @param $at
     */
    protected function createNotification($reply, $at)
    {
        if (! empty($at)) {
            $data = [];
            $time = new Carbon();
            foreach($at as $item){

                // 如果允许@
                $personalAllow = new CommonController();
                if($personalAllow->personalAllow($reply->user_id,$item,'stranger_at')){
                    $data[] = [
                        'user_id'        => $reply->user_id,
                        'notice_user_id' => $item,
                        'type'           => 3,
                        'type_id'        => $reply->id,
                        'created_at'     => $time,
                        'updated_at'     => $time
                    ];
                }
            }

            // 如果不为空
            if(!empty($data)) DB::table('notification')->insert($data);
        }
    }
}