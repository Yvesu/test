<?php

namespace App\Http\Controllers\NewAdmin;

use CloudStorage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CloudStorageController extends Controller
{
    //
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
}
