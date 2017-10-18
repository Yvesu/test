<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/29
 * Time: 18:28
 */

namespace app\Http\Controllers\Admin\APP\Camera;


use App\Http\Controllers\Controller;
use App\Models\Blur;
use CloudStorage;
use Illuminate\Http\Request;
use App\Models\BlurClass;
use Image;
use File;
class BlurClassController extends Controller
{
    /**
     * 上传图片名称转换，名称中有空格会出现bug
     * @param $request  请求信息
     * @param $name     文件名称
     * @param $size     转换大小
     * @return null|string  返回存储的名称或空
     */
    private function image_name($request,$name,$size,$id)
    {
        if($request->hasFile($name)){
            $path = public_path() .  '/temp-images/';
            $file = $request->file($name);
            $filename = $file->getClientOriginalName();
            $str_array = explode(' ',$filename);
            $filename = str_random(10).$str_array[sizeof($str_array) - 1];
            $file->move($path, $filename);
            Image::make($path . '/' . $filename)->fit($size)->save();
            CloudStorage::putFile(
                'blur_class/' . $id . '/' .$filename,
                $path . '/' . $filename
            );
            File::delete('temp-images/' . $filename);
            return $filename;
        }
        return null;
    }

    /**
     * 添加滤镜类型页面显示
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addClass()
    {
        return view('admin.app.camera.blur.addClass');
    }

    /**
     * 添加新滤镜
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void
     */
    public function add(Request $request)
    {
        $name_zh = $request->input('name-zh');
        $name_en = $request->input('name-en');
        if(is_null($name_zh) || is_null($name_en)){
            return;
        }

        $name_zhCount = BlurClass::where('name_zh',$name_zh)->count();
        $name_enCount = BlurClass::where('name_en',$name_en)->count();
        if($name_zhCount){
            \Session::flash('name_zh', '"'.$name_zh.'"'.trans('common.has_been_existed'));
        }
        if($name_enCount){
            \Session::flash('name_en', '"'.$name_en.'"'.trans('common.has_been_existed'));
        }
        if(\Session::has('name_en') || \Session::has('name_zh')){
            return redirect('/admin/app/camera/blur/class')->withInput();
        }


        $file_sm = null;
        $file_lg = null;

        $blur_class = new BlurClass();
        $blur_class->name_zh = $name_zh;
        $blur_class->name_en = $name_en;
        //2016-5-3 修改 滤镜类型添加完成后为停用
        $blur_class->active = 0;
        $blur_class->save();

        $file_sm = $this->image_name($request,'blur-class-icon-sm',96,$blur_class->id);
        $file_lg = $this->image_name($request,'blur-class-icon-lg',200,$blur_class->id);
        $blur_class->icon_sm = $file_sm;
        $blur_class->icon_lg = $file_lg;
        $blur_class->save();

        return redirect('/admin/app/camera/blur');
    }

    public function classShow(Request $request)
    {
        $type = $request->input('type');
        if(!is_null($type) && 0 == $type){
            $classes = BlurClass::where('active',0)->orderBy('updated_at','desc')->paginate(7);
        }else{
            $classes = BlurClass::where('active',1)->paginate(7);
        }
        return view('admin.app.camera.blur.class')
            ->with([
                'classes'   =>  $classes,
                'type'      =>  $type,
            ]);
    }

    public function disabled($id)
    {
        $class = BlurClass::find($id);
        if(null == $class){
            return redirect('/admin/app/camera/blur/class/management?type=0');
        }
        $class->active = 0;
        $class->save();

        //滤镜一并停用操作
        $blurs = Blur::where('blur_class_id',$id)->get();
        foreach($blurs as $blur){
            if(1 == $blur->active){
                $blur->active = 0;
                $blur->save();
            }
        }
        return redirect('/admin/app/camera/blur/class/management?type=0');
    }

    public function enabled($id)
    {
        $class = BlurClass::find($id);
        if(null == $class){
            return redirect('/admin/app/camera/blur/class/management?type=1');
        }
        $class->active = 1;
        $class->save();

        //滤镜一并启用操作
        $blurs = Blur::where('blur_class_id',$id)->get();
        foreach($blurs as $blur){
            if(0 == $blur->active){
                $blur->active = 1;
                $blur->save();
            }
        }

        return redirect('/admin/app/camera/blur/class/management?type=1');
    }

    public function editShow($id)
    {
        $class = BlurClass::find($id);
        return view('admin.app.camera.blur.editClass')
                    ->with([
                        'class' =>  $class
                    ]);
    }

    public function edit(Request $request,$id)
    {
        $name_zh = $request->input('name-zh');
        $name_en = $request->input('name-en');
        $is_clear = $request->input('is_clear');
        if(is_null($name_zh) || is_null($name_en)){
            return;
        }

        $name_zhCount = BlurClass::where('name_zh',$name_zh)->where('id','!=',$id)->count();
        $name_enCount = BlurClass::where('name_en',$name_en)->where('id','!=',$id)->count();
        if($name_zhCount){
            \Session::flash('name_zh', '"'.$name_zh.'"'.trans('common.has_been_existed'));
        }
        if($name_enCount){
            \Session::flash('name_en', '"'.$name_en.'"'.trans('common.has_been_existed'));
        }
        if(\Session::has('name_en') || \Session::has('name_zh')){
            return redirect('/admin/app/camera/blur/class/edit/' . $id)->withInput();
        }
        $blur_class = BlurClass::find($id);
        $blur_class->name_zh = $name_zh;
        $blur_class->name_en = $name_en;

        $file_sm = $this->image_name($request,'blur-class-icon-sm',96,$blur_class->id);

        $file_lg = $this->image_name($request,'blur-class-icon-lg',200,$blur_class->id);


        if(is_null($is_clear) || 0 == $is_clear){
            if(!is_null($file_sm)){
                /*$file_sm_array = explode('/',$blur_class->icon_sm);
                File::delete('temp-images/'.$file_sm_array[sizeof($file_sm_array) - 1]);*/
                CloudStorage::delete('blur_class/' . $blur_class->id . '/'. $blur_class->icon_sm);
                $blur_class->icon_sm = $file_sm;
            }
            if(!is_null($file_lg)){
                /*$file_lg_array = explode('/',$blur_class->icon_lg);
                File::delete('temp-images/'.$file_lg_array[sizeof($file_lg_array) - 1]);*/
                CloudStorage::delete('blur_class/' . $blur_class->id . '/'. $blur_class->icon_lg);
                $blur_class->icon_lg = $file_lg;
            }
        }else{
            /*$file_sm_array = explode('/',$blur_class->icon_sm);
            $file_lg_array = explode('/',$blur_class->icon_lg);
            File::delete('temp-images/'.$file_sm_array[sizeof($file_sm_array) - 1],'temp-images/'.$file_lg_array[sizeof($file_lg_array) - 1]);*/
            CloudStorage::delete('blur_class/' . $blur_class->id . '/'. $blur_class->icon_sm);
            CloudStorage::delete('blur_class/' . $blur_class->id . '/'. $blur_class->icon_lg);
            $blur_class->icon_sm = $file_sm;
            $blur_class->icon_lg = $file_lg;
        }

        $blur_class->save();
        return redirect('/admin/app/camera/blur/class/management?type=1');
    }
}