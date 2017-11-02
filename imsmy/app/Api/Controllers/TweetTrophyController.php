<?php

namespace App\Api\Controllers;

use App\Models\Tweet;
use App\Models\Blacklist;
use App\Models\TweetTrophyConfig;
use App\Models\TweetTrophyLog;
use App\Models\GoldAccount;
use App\Models\User;
use App\Models\GoldTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Api\Transformer\TweetsTrophyLogTransformer;
use App\Api\Transformer\ProfilesTrophyLogTransformer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use CloudStorage;
use Auth;
use DB;

/**
 * 奖杯相关接口
 *
 * @Resource("TweetTrophy")
 */
class TweetTrophyController extends BaseController
{
    // 页码
    protected $paginate = 20;

    private $tweetsTrophyLog;
    private $profilesTrophyLogTransformer;

    public function __construct(
        TweetsTrophyLogTransformer $tweetsTrophyLog,
        ProfilesTrophyLogTransformer $profilesTrophyLogTransformer
    )
    {
        $this -> tweetsTrophyLog = $tweetsTrophyLog;
        $this -> profilesTrophyLogTransformer = $profilesTrophyLogTransformer;
    }

    /**
     * 发送奖杯配置详情
     */
    public function information()
    {
        try{
            // 获取数据
            $trophy_data = TweetTrophyConfig::status() -> orderBy('num') -> get(['id', 'name', 'num', 'picture']);

            $trophy = [];

            // 对数据进行筛选
            foreach($trophy_data as $key=>$value){

                // 接收新数据
                $trophy[] = [
                    'trophy_id' => $value->id,
                    'name'      => $value->name,
                    'num'       => $value->num,
                    'picture'   => CloudStorage::downloadUrl($value->picture)
                ];
            }

            // 初始化数据
            $golds = 0;

            // 如果用户为登录状态
            if($user_from = Auth::guard('api')->user()){

                // 返回用户的金币数量
                $golds = GoldAccount::where('user_id',$user_from->id)->firstOrFail(['gold_total'])->gold_total;
            }

            // 返回数据
            return response()->json([
                'data'  => $trophy,
                'golds' => $golds
            ],201);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'bad_request'], 400);
        }
    }

    /**
     * 赠送奖杯
     *
     */
    public function present(Request $request)
    {
        try{

            // 获取奖杯id
            $trophy_id = (int)$request -> get('trophy');

            // 获取动态id
            $tweet_id = (int)$request -> get('tweet');

            // 获取赠送礼物的用户 id
            $user_id = Auth::guard('api') -> user() -> id;

            // 获取用户是否为匿名
            $anonymity = (int)$request -> get('anonymity',0);

            // 判断是否符合规范
            if(!in_array($anonymity,[0,1]))
                throw new \Exception('bad_request',400);

            // 获取动态相关集合
            $tweet = Tweet::able() -> findOrFail($tweet_id);

            // 获取奖杯对应的集合
            $trophy = TweetTrophyConfig::status() -> findOrFail($trophy_id);

            // 判断是否为自己给自己的作品颁奖
            if($user_id == $tweet->user_id) throw new \Exception('self_to_self',403);

            // 判断是否在黑名单内
            if(Blacklist::ofBlackIds($user_id,$tweet->user_id)->first()){

                // 在自己的黑名单中
                return response()->json(['error'=>'in_own_black_list'],431);
            }elseif(Blacklist::ofBlackIds($tweet->user_id,$user_id)->first()){

                // 在对方的黑名单中
                return response()->json(['error'=>'in_his_black_list'],432);
            }

            // 获取奖杯对应的需要的金币数量
            $num = $trophy ->num;

            $time = getTime();

            // 获取接收奖杯的用户集合
            $user_receive = User::findOrFail($tweet->user_id);

            // 获取用户账户下的金币集合
            $golds_send = GoldAccount::where('user_id',$user_id) -> firstOrFail();

            // 接收者金币表集合
            $golds_receive = GoldAccount::where('user_id',$user_receive->id) -> firstOrFail();

            // 判断金币数量是否符合 TODO 金币不足，请充值，跳转
            if($golds_send -> gold_avaiable < $num) throw new \Exception('gold_insufficient',401);

            // 写入奖杯日志表的数据
            $tweetTrophyLog = [
                'from'      => $user_id,
                'to'        => $user_receive->id,
                'tweet_id'  => $tweet_id,
                'trophy_id' => $trophy_id,
                'anonymity' => $anonymity,
                'date'      => date('Ymd',$time),
                'time_add'  => $time
            ];

            // 金币流通日志
            $goldsLog = [
                'user_from'   => $user_id,
                'user_to'     => $user_receive->id,
                'num'         => $num,
                'intro'       => 1,
                'time_add'    => $time,
                'time_update' => $time,
            ];

            ## 开启事务
            DB::beginTransAction();

            // 扣除赠奖杯用户账户相应金币
            $golds_send -> gold_avaiable -= $num;
            $golds_send -> gold_total -= $num;
            $golds_send -> gold_used += $num;
            $golds_send -> time_update = $time;

            // 保存赠送奖杯的人的账户信息
            $golds_send -> save();

            // 增加接收奖杯者账户相应金币,后期如果开放可以给自己颁奖，需在此重新获取接收奖杯者用户集合
            $golds_receive -> gold_avaiable += $num;
            $golds_receive -> gold_total += $num;
            $golds_receive -> time_update = $time;

            // 保存接收奖杯的人的账户信息
            $golds_receive -> save();

            // TODO 金币分成给关注者

            // 写入奖杯日志表
            $trophy = TweetTrophyLog::create($tweetTrophyLog);

            // 写入金币日志表
            GoldTransaction::create($goldsLog);

            // 发给动态作者的提醒消息
            $newNotice = [
                'user_id'           => $user_id,
                'notice_user_id'    => $user_receive->id,
                'type'              => 6,
                'type_id'           => $trophy -> id,
                'created_at'        => new Carbon(),
                'updated_at'        => new Carbon()
            ];

            // 发给评论者的提醒消息
            DB::table('notification')->insert($newNotice);

            ## 提交事务
            DB::commit();

            // 返回赠送成功的消息
            return response()->json(['balance'=>$golds_send -> gold_avaiable],201);

        }  catch (ModelNotFoundException $e){

            // 事务回滚
            DB::rollBack();

            return response()->json(['error' => 'bad_request'], 400);

        } catch (\Exception $e) {

            // 事务回滚
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 单条动态 颁奖嘉宾详情页数据
     */
    public function details(Request $request)
    {
        try{

            // 获取动态id
            $tweet_id = (int)$request -> get('tweet');

            // 判断动态是否存在
            if(!Tweet::able()->find($tweet_id))
                return response()->json(['error'=>'not_found'],404);

            // 获取颁奖记录集合
            $trophy_logs = TweetTrophyLog::with('belongsToUser','belongsToTrophy')
                            -> where('tweet_id',$tweet_id)
                            -> orderBy('id','desc')
                            -> ofSecond((int)$request -> get('last'))
                            -> take($this->paginate)
                            -> get();

            // 总数据量
            $count = $trophy_logs -> count();

            // 返回数据
            return [

                // 数据
                'data'  => $count ? $this -> tweetsTrophyLog -> transformCollection($trophy_logs -> all()) : [],

                // 总量
                'count' => $count,

                // 下一次加载的链接地址
                'link'  => $count ? $request -> url() .
                           '?tweet=' . $tweet_id .
                           '&last=' . $trophy_logs -> last() -> id
                           : ''
            ];

        }catch(\Exception $e){

            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }

    /**
     * 个人中心 奖项荣誉明细
     */
    public function profiles(Request $request)
    {
        try{

            // 获取颁奖记录集合
            $trophy_logs = TweetTrophyLog::with('belongsToUser','belongsToTrophy','belongsToTweet')
                -> where('to',Auth::guard('api')->user()->id)
                -> orderBy('id','desc')
                -> forPage((int)$request -> get('page',1),$this->paginate)
                -> get()
                -> groupBy('date')
                -> all();

            // 初始化
            $new_array = [];

            // 按日期排序
            if(!empty($trophy_logs)){

                foreach ($trophy_logs as $key => $value) {

                    $new_array[] = [
                        'date' => strtotime($key),
                        'data' => $this -> profilesTrophyLogTransformer -> transformCollection($value -> all())
                    ];
                };
            }

            // 返回数据
            return response() -> json([

                // 数据
                'data'  => $new_array,

                // 应返回总量
                'count' => $this->paginate,

            ],200);

        }catch(\Exception $e){

            return response()->json(['error' => $e->getMessage()],$e->getCode());
        }
    }
}