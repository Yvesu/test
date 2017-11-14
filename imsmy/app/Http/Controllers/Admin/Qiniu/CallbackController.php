<?php
namespace App\Http\Controllers\Admin\Qiniu;

use App\Http\Controllers\PremiseController;
use App\Models\Cloud\CloudStorageFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use CloudStorage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 七牛的回调
 * Class CallbackController
 * @package App\Http\Controllers\Admin\Qiniu
 */
class CallbackController extends PremiseController
{
    /**
     * 七牛截屏的回调
     */
    public function screenshot(Request $request)
    {
        // 接收异步返回数据
        $callback = file_get_contents('php://input');

//        Log::debug('七牛返回截屏数据',['info' => $callback]);

        $key = json_decode($callback,true)['items'][0]['key'];

        // 从缓存中获取 效果id
        $id = Cache::get(json_decode($callback) -> id);

        try{
            // 获取效果集合
            $file = CloudStorageFile::findOrFail($id);

            // 判断是否已经接收过
            if(!$file -> screenshot) {

                $time = getTime();

                $new_name = 'folder/'.$file->folder_id.'/'.$file->id.'/'.str_random(10).$time.'.jpg';

                // 对截屏进行重命名
                CloudStorage::rename($key,$new_name);

                // 更改截图地址
                $file -> update(['screenshot' => $new_name]);

                // 释放缓存
                Cache::forget(json_decode($callback) -> id);
            }
        } catch (ModelNotFoundException $e) {
            Log::error('七牛返回截屏数据失败',['id' => $id]);
        } catch (\Exception $e) {
            Log::error('七牛返回截屏数据失败',['id' => $id]);
        }
    }
}