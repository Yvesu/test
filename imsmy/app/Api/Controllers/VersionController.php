<?php
namespace App\Api\Controllers;

use App\Models\AppType;
use App\Models\AppVersionUpgrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class VersionController extends BaseController
{
    /**
     * 检查最新版本
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        try {

            // 设备名称，android、iphone、ipad....
            $name = removeXSS($request->get('name'));
            $versionOld = (int)$request->get('version_id');

            // 客户端设备号
            $did = removeXSS($request->get('did'));

            // 加密后的客户端设备号,key是提前设定在app中的,加密方式：md5('设备号' . 'key')
            $encryptDid = $request->get('token');

            $new = AppType::where('name', $name)
                -> where('status', 1)
                -> firstOrFail(['id', 'is_encryption', 'key']);

            // 如果需要加密,并加密后的设备号不匹配
            if($new -> is_encryption && $encryptDid != md5($did . $new -> key))
                return response() -> json(['error' => 'bad_request'], 403);

            // 获取相关最新的版本信息
            $version = Cache::remember('app_version_newest_'.$name, 1440, function () use ($new) {

                $version_new = AppVersionUpgrade::where('app_id', $new->id)
                    -> where('status', 1)
                    -> orderBy('id', 'DESC')
                    -> firstOrFail(['version_id', 'version_mini', 'version_code', 'type', 'apk_url', 'upgrade_point']);

                    return [
                        'version_id'    => $version_new -> version_id,
                        'version_mini'  => $version_new -> version_mini,
                        'version_code'  => $version_new -> version_code,
                        'type'          => $version_new -> type,
                        'apk_url'       => $version_new -> apk_url,
                        'upgrade_point' => $version_new -> upgrade_point,
                        'time_add'      => $version_new -> time_add,

                        // 加密，让app端判断是否为服务器发出的链接
                        'token'         => md5($version_new -> version_code . $new -> key),
                    ];
            });

            // 比较版本号,返回最新版本信息
            if($version['version_id'] > $versionOld) {
                return response() -> json(['data' => $version], 200);
            }

            // 已是最新版本
            return response() -> json([], 204);

        } catch(\Exception $e){
            return response() -> json(['error' => 'not_found'], 404);
        }
    }


}