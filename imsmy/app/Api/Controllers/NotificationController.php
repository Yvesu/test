<?php


namespace App\Api\Controllers;

use App\Api\Transformer\NotificationsTransformer;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Auth;
/**
 * 助手、提醒 相关接口
 *
 * @Resource("Notification",uri="users/{id}/notifications")
 */
class NotificationController extends BaseController
{
    protected $notificationsTransformer;

    public function __construct(NotificationsTransformer $notificationsTransformer)
    {
        $this->notificationsTransformer = $notificationsTransformer;
    }

    /**
     * 获取提醒
     *
     * @Get("/{?timestamp,limit}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id",required=true,description="当前用户ID"),
     *      @Parameter("timestamp", description="从某一时刻倒叙获取，输入有误，会使用默认值",default="当前时间"),
     *      @Parameter("limit", description="获取个数，输入有误，会使用默认值",default=5)
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(200,body={
     *                      {
     *                           "id": 5,
     *                           "type": {
     *                                  "类型种类",
     *                                  "0 : tweet @               别人发的动态 被@              需求 ==> 动态",
     *                                  "1 : tweet_like            自己发的动态 被点赞            需求 ==> 动态",
     *                                  "2 : tweet_comment         自己发的动态 被评论            需求 ==> 评论",
     *                                  "3 : tweet_comment @       任何动态    在评论中被@        需求 ==> 评论",
     *                                  "4 : tweet_comment_reply   自己发的评论被回复             需求 ==> 评论"
     *                           },
     *                           "type_id": 1,
     *                           "created_at": 1462782518,
     *                           "data": {
     *                                   "id": 1,
     *                                   "user_id": 10001,
     *                                   "tweet_id": 2,
     *                                   "reply_id": null,
     *                                   "content": "已赞",
     *                                   "created_at": 1462782518
     *                           },
     *                           "user": {
     *                                   "id": 10001,
     *                                   "nickname": "test"
     *                           }
     *                       },
     *                       {
     *                           "id": 4,
     *                           "type": 1,
     *                           "type_id": 2,
     *                           "created_at": 1462777565,
     *                           "data": {
     *                                   "id": 2,
     *                                   "user_id": 10003,
     *                                   "retweet": null,
     *                                   "content": "快赞我！",
     *                                   "image": null,
     *                                   "video": null,
     *                                   "location": null,
     *                                   "type": 0,
     *                                   "type_range": null,
     *                                   "created_at": 1462777618
     *                           },
     *                           "user": {
     *                                   "id": 10001,
     *                                   "nickname": "test"
     *                           }
     *                       },
     *     }),
     *     @Response(401,body={"error":"unauthorized"}),
     * })
     */
    public function index($id,Request $request)
    {
        try {
            // 接收数据并检测是否为纯数字
            $timestamp = $request->get('timestamp');
            $limit = $request->get('limit');
            $limit = isset($limit) && ctype_digit($limit) ? $limit : 5;
            $timestamp = isset($timestamp) && ctype_digit($timestamp) ? $timestamp : time();

            // 将时间转换格式
            $date = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            $notifications = Notification::with('belongsToUser')
                                           ->where('notice_user_id',$id)
                                           ->where('created_at','<',$date)
                                           ->take($limit)
                                           ->orderBy('created_at','desc')
                                           ->get();
            return response()->json($this->notificationsTransformer->transformCollection($notifications->all()));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 获取具体提醒信息
     * @Post("/notice")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id",required=true,description="当前用户ID"),
     *      @Parameter("style", description="提醒类型，1为评论，2为@我，3为点赞,4：被关注 5：被送奖杯"),
     *      @Parameter("limit", description="获取个数，输入有误，会使用默认值",default=10)
     * })
    */
    public function message($id,Request $request)
    {
        try {
            // 接收数据并检测是否为纯数字
            $style = $request->get('style');
            $limit = $request->get('limit');
            $limit = isset($limit) && ctype_digit($limit) ? $limit : 10;
            $style = isset($style) && ctype_digit($style) ? $style : null;

            // 判断style为何种类型
            switch ($style)
            {
                // 评论
                case 1:
                    $type = [2,4];
                    break;
                // @我
                case 2:
                    $type = [0,3];
                    break;
                // 点赞
                case 3:
                    $type = [1];
                    break;

                case 4:
                    $type = [5];
                    break;
                // 被送奖杯
                case 5:
                    $type = [6];
                    break;
                default:
                    $type = '';
            }

            // 判断类型是否为空
            if(!$type) return response()->json(['error' => 'style_not_null'], 403);

            // 获取数据
            $notifications = Notification::with('belongsToUser')
                ->where('notice_user_id',$id)
//                ->where('status',0)
                ->whereIn('type',$type)
                ->orderBy('created_at','desc')
                ->take($limit)
                ->get();

            // 将获取的数据标记为已读
            $notifications -> each(function($notification){

                // 判断是否为未读消息，如果为未读消息，标记为已读
                if($notification -> status == 0){

                    // 标记为已读
                    $notification -> status = 1;

                    // 保存
                    $notification -> save();
                }
            });

            return response()->json([
                'data'=> $this->notificationsTransformer->transformCollection($notifications->all())
            ],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 删除提醒
     * @Delete("/{notice_id}")
     * @Versions({"v1"})
     * @Parameters({
     *      @Parameter("id", description="当前用户ID"),
     *      @Parameter("notice_id", description="要删除的提醒ID"),
     * })
     * @Transaction({
     *     @Request(headers={"Authorization": "Bearer TOKEN"}),
     *     @Response(204),
     *     @Response(401,body={"error":"unauthorized"}),
     *     @Response(404,body={"error":"notification_not_found"})
     * })
     */
    public function destroy($id,$notice_id)
    {
        try {
            $notification = Notification::findOrFail($notice_id);
            $notification->delete();
            return response('',204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'notification_not_found'],404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}