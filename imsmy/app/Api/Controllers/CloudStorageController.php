<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/25
 * Time: 12:18
 */

namespace App\Api\Controllers;

use CloudStorage;
use Illuminate\Http\Request;

/**
 * 云存储相关接口
 *
 * 本地192.168.1.80/api
 * 服务器 IP 101.200.75.163/api OR www.goobird.com/api
 * Headers Accept application/vnd.goobird.v1+json
 *
 * @Resource("CloudStorage",uri="/cloud-storage")
 */
class CloudStorageController extends BaseController
{
    /**
     * 获取token值
     * @Get("/token")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("url", description="如需回调，此处填写回调函数"),
     *     @Parameter("body", description="如需回调，此处填写对应的body返回值"),
     * })
     * @Transaction({
     *     @Response(200,body={"token":"TOKEN值"})
     * })
     */
    public function token(Request $request)
    {
        $type = $request->get('type');
        $location = $request->get('location');
        if(empty($type) || empty($location))
        {
            return response()->json(['error'=>'数据不和法']);
        }

        $policy = null;
        $parameters = $request->only(['url','body']);

        if(isset($parameters['url'])){
            $policy['callbackUrl'] = $request->get('url');
        }
        if(isset($parameters['body'])){
            $policy['callbackBody'] = $request->get('body');
        }
        return response()->json([
            'token' => CloudStorage::getToken($policy,$type,$location)
        ]);
    }

    /**
     * 获取privateDownloadUrl
     * @Get("/private-download-url")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("key=key值&format=格式参数不带？并且可空"),
     *     @Response(200,body={"url":"privateDownloadUrl"})
     * })
     */
    public function privateDownloadUrl(Request $request)
    {
        $key = $request->get('key');
        $format = $request->get('format');
        if(null != $format){
            $key .= '?' .$format;
        }
        return response()->json(['url' => CloudStorage::privateDownloadUrl($key)]);
    }

    /**
     * 删除单个文件
     * @Delete("/file")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("key=key值"),
     *     @Response(204)
     * })
     */
//    public function deleteFile(Request $request)
//    {
//        $key = $request->get('key');
//        if(null == $key){
//            return response()->json(['error' => 'bad_request'],400);
//        }
//        //TODO 权限判断
//        $error = CloudStorage::delete($key);
//        if($error !== null){
//            return response()->json(['error' => $error],400);
//        }
//        return response()->json('',204);
//    }

    /**
     * 根据前缀删除，相当于删除文件夹
     * @Delete("/directory")
     * @Versions({"v1"})
     * @Transaction({
     *     @Request("prefix=prefix前缀值"),
     *     @Response(204)
     * })
     */
//    public function deleteDirectory(Request $request)
//    {
//        try {
//            $prefix = $request->get('prefix');
//            if(null == $prefix){
//                return response()->json(['error' => 'bad_request'],400);
//            }
//            //TODO 权限判断
//            CloudStorage::deleteDirectory($prefix);
//            return response()->json('',204);
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()],$e->getCode());
//        }
//
//    }
}