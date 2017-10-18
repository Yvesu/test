<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/9
 * Time: 10:55
 */

namespace App\Api\Controllers;


use App\Models\{Blacklist,Friend,Subscription};
use App\Api\Transformer\UsersTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
//use App\Models\Friend;
use Illuminate\Http\Request;
use DB;
use Auth;
use EaseMob;
use CloudStorage;

/**
 * 黑名单相关接口
 *
 * @Resource("Blacklist",uri="users/{id}/blacklists")
 */
class BlacklistController extends BaseController
{
    protected $usersTransformer;
    public $paginate = 20;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
    }

    /**
     * 获取黑名单
     * @Get("/")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     * })
     * @Transaction({
     *     @Response(200,body={"data":{"username1","username2"}}),
     *     @Response(401,body={"error":"unauthorized"})
     * })
     */
    public function index($id,Request $request)
    {
        try {
            // 获取页码
            $page = (int)$request -> get('page',1);

            $users = Blacklist::with('hasOneTo')->where('from',$id)->forPage($page,$this->paginate)->get();

            // 判断是否有数据
            if(!$users -> count())
                return response()->json(['data'=>[],'count'=>$this->paginate],200);

            foreach($users as $value){
                $data[] = [
                    'id'        => $value -> hasOneTo -> id,
                    'avatar'    => CloudStorage::downloadUrl($value -> hasOneTo -> avatar),
                    'nickname'  => $value -> hasOneTo -> nickname,
                    'verify'    => $value -> hasOneTo -> verify,
                ];
            }

            return response()->json(['data'=>$data,'count'=>$this->paginate],200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 添加黑名单
     * @Post("/{blocked_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID"),
     *     @Parameter("blocked_id", description="被拉入黑名单的ID"),
     * })
     * @Transaction({
     *     @Response(201,body={"data":{"username"}}),
     *     @Response(401,body={"error":"unauthorized"})
     * })
     */
    public function store($id,$blocked_id)
    {
        try {
//            $blacklist = [(string)$blocked_id];
            // 暂停环信业务  搜索号：备忘录
//            $result = EaseMob::addUserForBlacklist($id,$blacklist);

            // 不能设置自己
            if(!is_numeric($blocked_id) || $id == $blocked_id)
                return response()->json(['error' => 'bad_request'],403);

            // 判断是否已经添加黑名单
            if(Blacklist::where('from',$id)->where('to',(int)$blocked_id)->first())
                return response()->json(['error' => 'already_exist'],407);

            // 开启事务
            DB::beginTransaction();

            // 将数据添加至blacklist表
            $data = Blacklist::create([
                'from' => $id,
                'to'   => $blocked_id,
            ]);

            // 判断是否已经为好友或者关注关系
            Friend::where('from',$id)->where('to',$blocked_id)->delete();
            Friend::where('from',$blocked_id)->where('to',$id)->delete();

            Subscription::where('from',$id)->where('to',$blocked_id)->delete();
            Subscription::where('from',$blocked_id)->where('to',$id)->delete();

            Log::info('Add User To Black List: from '.$id.' to '.$blocked_id);

            // 事务提交
            DB::commit();

            return response()->json(['status' => 'ok'],201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 从黑名单中删除人
     * @Delete("/{blocked_id}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("id", description="添加者的ID即登录环信的用户名"),
     *     @Parameter("blocked_id", description="被拉入黑名单的ID即环信用户名"),
     * })
     * @Transaction({
     *     @Response(204),
     *     @Response(401,body={"error":"unauthorized"})
     * })
     */
    public function destroy($id,$blocked_id)
    {
        try {
            // 暂停环信业务  搜索号：备忘录
//            EaseMob::deleteUserFromBlacklist($id,$blocked_id);

            Blacklist::where('from',$id)->where('to',$blocked_id)->delete();

            Log::info('Remove User From Black List: from '.$id.' to '.$blocked_id);

            return response('',204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}