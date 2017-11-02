<?php
namespace App\Api\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use CloudStorage;
use DB;
use App\Models\{XmppUserFiles};
use Illuminate\Http\Request;

/**
 * Xmpp聊天方面的数据处理
 * Class XmppController
 * @package App\Api\Controllers
 */
class XmppController extends BaseController
{
    /**
     * 用户通过xmpp聊天之间传送文件的接口，方便七天后删除使用
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($id, Request $request)
    {
        try{
            $validator = Validator::make($request -> all(), [
                'user_to'   => 'required|numeric',
                'address'   => 'required|max:255',
                'type'      => 'required|numeric',
            ]);

            if ($validator->fails()) {
                // 返回json数据错误信息
                return response()->json('bad_request',403);
            }

            $time = getTime();

            DB::beginTransaction();

            $file = XmppUserFiles::create([
                'user_from'     => $id,
                'user_to'       => $request -> get('user_to'),
                'address'       => $request -> get('address'),
                'type'          => $request -> get('type'),
                'time_add'      => $time,
                'time_update'   => $time,
            ]);

            $arr = explode('/',$file->address);

            $new_key = 'xmpp/' . $file -> id . '/' . $arr[sizeof($arr) - 1];

            $data[$file->address] = $new_key;
            $file->address = $new_key;

            // 将存在七牛云上的内容进行重命名
            CloudStorage::batchRename($data);
            $file->save();

            DB::commit();

            return response()->json(['data'=>[
                'address'   => $file->address
            ]]);

        }catch(ModelNotFoundException $e){
            DB::rollback();
            return response()->json('not_found',404);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json('not_found',404);
        }
    }
}