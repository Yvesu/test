<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/26
 * Time: 11:04
 */

namespace app\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use File;
use Image;
class ImageController extends Controller
{
    /**
     * 存储于storage中的文件，已路由形式获取信息。
     * @param $id 文件夹名称，已用户ID命名，默认为default
     * @param $filename 文件名称
     * @return mixed
     */
    public function show($id,$filename)
    {
        $path = storage_path() . '/app/public/administrator/' . $id . '/' . $filename;

        return Image::make($path)->response();

    }

    public function showTemp($id,$filename)
    {
        $path = storage_path() . '/app/public/administrator/' . $id . '/temp/' . $filename;

        return Image::make($path)->response();
    }

}