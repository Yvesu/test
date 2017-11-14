<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/11
 * Time: 17:34
 */

namespace app\Http\Controllers\Admin\APP\Camera\Blur;

use App\Http\Controllers\Controller;
use App\Models\Blur;
use App\Models\BlurClass;
use App\Models\GPUImage;
use App\Models\GPUImageValue;
use CloudStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Image;
use Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use File;
class AddController extends Controller
{
    private $expiration_time = 30;

    private function upload($key,$path)
    {
        list($ret,$error) = CloudStorage::putFile($key,$path);
        //todo失败处理
    }
    //以下部分为添加滤镜功能，分为6步骤，较为繁琐
    /**
     * 添加滤镜页面
     * 第一部分为，之前缓存的图片，删除超过一天时间
     * @return $this
     */
    public function index()
    {
        $directories = File::directories(public_path().'/blur/temp');
        foreach($directories as $directory){
            $now = time();
            $lastModified = File::lastModified($directory);
            $difference = $now - $lastModified;
            if($difference > $this->expiration_time * 60){        //默认30分钟前修改的，都是过期文件，删除!!! 与缓存同步
                File::deleteDirectory($directory);
            }
        }

        //2016-5-3 修改 滤镜添加，滤镜类型全部可选
        $classes = BlurClass::get();
        /*$classes = BlurClass::where('active',1)->get();*/

        $GPUImage = GPUImage::with('hasManyGPUImageValue')->get();
        $key = str_random(15);
        $blur = Cache::get($key);
        //防止重复
        while(!is_null($blur)){
            $key = str_random(15);
            $blur = Cache::get($key);
        }
        return view('admin.app.camera.blur.addBlur')
            ->with([
                'classes'       =>  $classes,
                'GPUImage'      =>  $GPUImage,
                'key'           =>  $key,
            ]);
    }

    /**
     * 添加滤镜路由，分为多步骤，由ajax分部添加并缓存到redis中
     * @param Request $request
     */
    public function blurClass(Request $request)
    {
        $key = $request->input('key');
        $name_zh = $request->input('name_zh');
        $name_en = $request->input('name_en');
        $blur_class = $request->input('blur_class');

        $zhCount = Blur::where('name_zh',$name_zh)->count();
        $enCount = Blur::where('name_en',$name_en)->count();
        if($enCount || $zhCount){
            return Response::json([
                'success' => false,
                'error'   => [
                    'zh'        => $zhCount,
                    'en'        => $enCount,
                    'name_zh'   => $name_zh,
                    'name_en'   => $name_en
                ]
            ]);
        }

        $blur = new Blur();
        $blur->name_zh = $name_zh;
        $blur->name_en = $name_en;
        $blur->blur_class_id = $blur_class;
        Cache::put($key,$blur,$this->expiration_time);
        return Response::json([
            'success' => true
        ]);
    }

    /**
     * 添加GPUImage 到缓存中，并把支持纹理图片的图片暂存到本地
     * @param Request $request
     * @return mixed
     */
    public function GPUImage(Request $request)
    {
        $_key = $request->input('key');
        $input = $request->except(['_token','key']);
        $blur = Cache::get($_key);
        //缓存过期
        $dic = [];
        foreach($input as $key => $value){
            if(is_int($key)){
                $GPUImageValue = GPUImageValue::with('belongsToGPUImage')->where('id',$key)->first();
                $flag = true;
                foreach($dic as &$item){
                    if(isset($item['name']) && $item['name'] == $GPUImageValue->belongsToGPUImage->name_en){
                        $arr = [
                            'name'  =>  $GPUImageValue->name_en,
                            'value' =>  $value
                        ];
                        array_push($item['data'],$arr);
                        $flag  = false;
                        break;
                    }
                }
                unset($item);
                if($flag){
                    $arr = [
                        'name'  =>   $GPUImageValue->belongsToGPUImage->name_en,
                        'data'  =>   [
                            [

                                'name'  =>  $GPUImageValue->name_en,
                                'value' =>  $value
                            ]
                        ]
                    ];
                    array_push($dic,$arr);
                }
            }else{
                $str = explode('_',$key);
                $texture = $str[0];
                $file = $request->file($key);

                $rules = array('photo' => 'mimes:jpg,png');
                $input = array('image' => $file);

                $validation = Validator::make($input,$rules);

                if($validation->fails()){
                    return Response::make($validation->errors->first(), 400);
                }
            }
        }

        if(isset($texture)){
            $name = GPUImage::find($texture)->name_en;
            foreach($dic as $index => $item){
                if($name == $item['name']){
                    $destinationPath = public_path().'/blur/temp/'.$_key;
                    $filename = $file->getClientOriginalName();
                    $file->move($destinationPath, $filename);
                    $dic[$index] = array_add($item,'imgUrl','/' . $filename);
                }
            }
        }


        $blur->parameter = json_encode($dic);
        Cache::put($_key,$blur,$this->expiration_time);
        return Response::json([
            'success' => true
        ]);
    }

    /**
     * 添加重力感应图片
     * 一、判断图片格式合法
     * 二、判断重复上传
     * 三、判断比例是否正确
     * 四、判断与之前图片的比例是否一致
     *
     * @param Request $request
     * @return mixed
     */
    public function gravityImage(Request $request)
    {
        $file = $request->file('file');
        $key = $request->input('key');
        $rules = array('file' => 'image');
        $input = array('image' => $file);
        $blur = Cache::get($key);

        $validation = Validator::make($input,$rules);

        if($validation->fails()){
            return Response::make($validation->errors->first(), 400);
        }

        $directory = public_path().'/blur/temp/' . $key . '/gravity';
        $filename = $file->getClientOriginalName();

        if(file_exists($directory . '/'.$filename)){
            return Response::json(trans('app.fileExist'),400);
        }

        if(!isset($blur->gravity_scale)){
            $height = Image::make($file)->height();
            $width = Image::make($file)->width();
            $scale = $width * 1000 / $height;
            if($scale != 1000 && $scale != 625 && $scale != 750){
                return Response::json(trans('app.imgScaleCorrect'),400);
            }
            $blur->gravity_scale = $scale;
        }else{
            $height = Image::make($file)->height();
            $width = Image::make($file)->width();
            $scale = $width * 1000 / $height;
            if($blur->gravity_scale !=  $scale){
                return Response::json(trans('app.imgScaleSame'),400);
            }
        }

        if($blur->sequence_diagram == null){
            $blur->sequence_diagram = [];
        }
        $arr = $blur->sequence_diagram;
        array_push($arr , '/gravity/'.$filename);
        $blur->sequence_diagram = $arr;

        $file->move($directory,$filename);

        Cache::put($key,$blur,$this->expiration_time);
        return Response::json('success',200);
    }

    /**
     * 删除 重力感应图片
     * 删除本地图片，及缓存中的路径信息
     * @param Request $request
     */
    public function delGravityImage(Request $request)
    {
        $name = $request->input('name');
        $_key = $request->input('key');
        $blur = Cache::get($_key);

        if(($key = array_search('/gravity/'.$name, $blur->sequence_diagram)) !== false) {
            $arr = $blur->sequence_diagram;
            array_splice($arr,$key,1);
            $blur->sequence_diagram = $arr;
        }
        File::delete('blur/temp/' . $_key . '/gravity/' . $name);
        Cache::put($_key,$blur,$this->expiration_time);
    }

    /**
     * 添加人脸追踪 图片
     * 一、判断图片格式正确
     * 二、判断是否已存在该图片
     * 三、判断缓存中是否存在该路径信息
     * @param Request $request
     * @return mixed
     */
    public function faceImage(Request $request)
    {
        $file = $request->file('file');
        $key = $request->input('key');
        $rules = array('file' => 'image');
        $input = array('image' => $file);
        $blur = Cache::get($key);

        $validation = Validator::make($input,$rules);

        if($validation->fails()){
            return Response::make($validation->errors->first(), 400);
        }

        $directory = public_path().'/blur/temp/' . $key . '/face';
        $filename = $file->getClientOriginalName();

        if(file_exists($directory . '/'.$filename)){
            return Response::json(trans('app.fileExist'),400);
        }

        if($blur->face_tracking != null){
            return Reponse::json('error',400);
        }

        $blur->face_tracking = '/face/' . $filename;
        $file->move($directory,$filename);

        Cache::put($key,$blur,$this->expiration_time);
        return Response::json('success',200);
    }

    /**
     * 删除人脸识别图片
     * @param Request $request
     */
    public function delFaceImage(Request $request)
    {
        $name = $request->input('name');
        $_key = $request->input('key');
        $blur = Cache::get($_key);

        $blur->face_tracking = null;
        File::delete('blur/temp/' . $_key . '/face/' . $name);
        Cache::put($_key,$blur,$this->expiration_time);
    }

    /**
     * 添加动态图
     * 返回中包含图片暂存地址，以便预览
     * @param Request $request
     * @return mixed
     */
    public function dynamicImage(Request $request)
    {
        $file = $request->file('file');
        $key = $request->input('key');
        $rules = array('file' => 'image');
        $input = array('image' => $file);
        $blur = Cache::get($key);

        $validation = Validator::make($input,$rules);

        if($validation->fails()){
            return Response::make($validation->errors->first(), 400);
        }

        $directory = public_path().'/blur/temp/' . $key . '/dynamic';
        $filename = $file->getClientOriginalName();

        if(file_exists($directory . '/'.$filename)){
            return Response::json(trans('app.fileExist'),400);
        }

        if($blur->dynamic_image != null){
            return Reponse::json('error',400);
        }

        $height = Image::make($file)->height();
        $width = Image::make($file)->width();
        $scale1 = $width * 1000 / $height;
        $scale2 = $width * 1000 / $height;
        if($scale1 != 750 && $scale2){
            return Response::json(trans('app.imgScaleCorrect'),400);
        }

        $blur->dynamic_image = '/dynamic/' . $filename;
        $file->move($directory,$filename);

        Cache::put($key,$blur,$this->expiration_time);
        return Response::json(asset('blur/temp/' . $key . '/dynamic/'.$filename),200);
    }

    /**
     * 删除动态图
     * @param Request $request
     */
    public function delDynamicImage(Request $request)
    {
        $name = $request->input('name');
        $key = $request->input('key');
        $blur = Cache::get($key);

        $blur->dynamic_image = null;
        File::delete('blur/temp/' . $key . '/dynamic/' . $name);
        Cache::put($key,$blur,$this->expiration_time);
    }


    public function backgroundImage(Request $request)
    {
        $file = $request->file('file');
        $key = $request->input('key');
        $rules = array('photo' => 'mimes:jpg,png,gif');
        $input = array('image' => $file);
        $blur = Cache::get($key);

        $validation = Validator::make($input,$rules);

        if($validation->fails()){
            return Response::make($validation->errors->first(), 400);
        }

        $directory = public_path().'/blur/temp/' . $key . '/background';
        $filename = $file->getClientOriginalName();

        if(file_exists($directory . '/'.$filename)){
            return Response::json(trans('app.fileExist'),400);
        }

        if($blur->background_image != null){
            return Reponse::json('error',400);
        }

        $height = Image::make($file)->height();
        $width = Image::make($file)->width();
        $scale = $width * 1000 / $height;
        if($scale != 750){
            return Response::json(trans('app.imgScaleCorrect'),400);
        }

        $blur->background_image = '/background/' . $filename;
        $file->move($directory,$filename);

        Cache::put($key,$blur,$this->expiration_time);
        return Response::json(asset('blur/temp/' . $key . '/background/'.$filename),200);
    }

    public function delBackgroundImage(Request $request)
    {
        $name = $request->input('name');
        $key = $request->input('key');
        $blur = Cache::get($key);

        $blur->background_image = null;
        File::delete('blur/temp/' . $key . '/background/' . $name);
        Cache::put($key,$blur,$this->expiration_time);
    }

    public function backgroundCount(Request $request)
    {
        if(!$request->has('key')){
            return Response::json([
                'success' => false
            ]);
        }

        $key = $request->get('key');
        $blur = Cache::get($key);
        if(!isset($blur->background_image)){
            return Response::json([
                'success' => false
            ]);
        }

        return Response::json([
            'success' => true
        ]);
    }
    /**
     * 启动重力感应，音频，x偏移量,y偏移量,缩放比例,快门速度 信息提交保存
     * 再修改图片位置，及数据库地址信息。
     * 云存储暂未开通，所以先保存到本地路径中
     * -->之后会更改，目前函数过长，等开通云存储后一并优化
     *
     * 4-11 添加 后台背景图片
     * //TODO 暂存本地，等云存储开通后修改
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $_key = $request->input('key');
        $audio = $request->file('audio');
        $shutter_speed = $request->input('shutter_speed');
        $xAlign = $request->input('xAlign');
        $yAlign = $request->input('yAlign');
        $scale = $request->input('scale');
        $gravity = $request->input('gravity');

        $cache = Cache::get($_key);

        if(is_null($cache->background_image)){
            return Response::json([
                'success' => false
            ]);
        }

        $blur = new Blur();
        $blur->name_zh          = $cache->name_zh;
        $blur->name_en          = $cache->name_en;
        $blur->blur_class_id    = $cache->blur_class_id;
        $blur->parameter        = $cache->parameter;
        $blur->face_tracking    = $cache->face_tracking;
        if(!is_null($cache->sequence_diagram)){
            $blur->sequence_diagram = json_encode($cache->sequence_diagram);
        }
        $blur->dynamic_image    = $cache->dynamic_image;
        $blur->background = $cache->background_image;
        if(!is_null($gravity)){
            $blur->gravity_sensing = 1;
        }

        $blur->xAlign          = $xAlign;
        $blur->yAlign          = $yAlign;
        $blur->scaling_ratio   = $scale;

        if($shutter_speed == 'AUTO'){
            $blur->shutter_speed = 0;
        }else{
            $blur->shutter_speed = str_replace('1/','',$shutter_speed);
        }

        $blur->save();

        //保存后根据id 把临时文件保存到对应ID下的目录
        //TODO  云存储开通后 放到云存储上并另建函数
        //蛋疼的，PHP rename不建立文件夹会报错
        /*File::makeDirectory(public_path().'/blur/'.$blur->id);
        File::makeDirectory(public_path().'/blur/'.$blur->id.'/gravity');
        File::makeDirectory(public_path().'/blur/'.$blur->id.'/face');
        File::makeDirectory(public_path().'/blur/'.$blur->id.'/dynamic');
        File::makeDirectory(public_path().'/blur/'.$blur->id.'/background');*/

        $parameters = json_decode($blur->parameter,true);
        foreach($parameters as $key => $value){
            if(isset($value['imgUrl'])){
                $str = explode('/',$value['imgUrl']);
                $value_name = $str[sizeof($str) - 1];
                /*File::move(
                    public_path().'/blur/temp/'. $_key .$value['imgUrl'],
                    public_path().'/blur/'.$blur->id.'/'.$value_name
                );*/
                $this->upload(
                    'blur/'.$blur->id.'/'.$value_name,
                    public_path().'/blur/temp/'. $_key .$value['imgUrl']);
                $parameters[$key]['imgUrl'] = $value_name;
            }
        }
        $blur->parameter = json_encode($parameters);


        if(!is_null($blur->sequence_diagram)){
            $sequence_diagram = json_decode($blur->sequence_diagram,true);
            $i = 0;
            foreach($sequence_diagram as  $value){
                $str = explode('/',$value);
                $value_name = $str[sizeof($str) - 1];
                /*File::move(
                    public_path().'/blur/temp/'. $_key .$value,
                    public_path().'/blur/'.$blur->id.'/gravity/'.$value_name
                );*/
                $this->upload(
                    'blur/'.$blur->id.'/gravity/'.$value_name,
                    public_path().'/blur/temp/'. $_key .$value);
                $sequence_diagram[$i] = '/gravity/'.$value_name;
                $i++;
            }
            $blur->sequence_diagram = json_encode($sequence_diagram);
        }

        if(!is_null($blur->face_tracking)){
            $face_tracking = $blur->face_tracking;
            $str = explode('/',$face_tracking);
            $value_name = $str[sizeof($str) - 1];
            /*File::move(
                public_path().'/blur/temp/'. $_key .$face_tracking,
                public_path().'/blur/'.$blur->id.'/face/'.$value_name
            );*/
            $this->upload(
                'blur/'.$blur->id.'/face/'.$value_name,
                public_path().'/blur/temp/'. $_key .$face_tracking);
            $blur->face_tracking = '/face/'.$value_name;
        }

        if(!is_null($blur->dynamic_image)){
            $dynamic_image = $blur->dynamic_image;
            $str = explode('/',$dynamic_image);
            $value_name = $str[sizeof($str) - 1];
            /*File::move(
                public_path().'/blur/temp/'. $_key .$dynamic_image,
                public_path().'/blur/'.$blur->id.'/dynamic/'.$value_name
            );*/
            $this->upload(
                'blur/'.$blur->id.'/dynamic/'.$value_name,
                public_path().'/blur/temp/'. $_key .$dynamic_image);
            $blur->dynamic_image = '/dynamic/'.$value_name;
        }

        //4月11日添加的需求，后台页面要显示背景图
        $background_image = $blur->background;
        $str = explode('/',$background_image);
        $value_name = $str[sizeof($str) - 1];
        /*File::move(
            public_path().'/blur/temp/'. $_key .$background_image,
            public_path().'/blur/'.$blur->id.'/background/'.$value_name
        );*/
        $this->upload(
            'blur/'.$blur->id.'/background/'.$value_name,
            public_path().'/blur/temp/'. $_key .$background_image);
        $blur->background = '/background/'.$value_name;


        if(!is_null($audio)){
            $directory = public_path().'/blur/' . $blur->id . '/audio';
            $filename = $audio->getClientOriginalName();

            $blur->audio = '/audio/' . $filename;
            $audio->move($directory,$filename);
            $this->upload(
                'blur/'.$blur->id.'/audio/'.$filename,
                $directory . $filename);
        }

        $blur->save();

        //云存储结束

        //释放缓存和清除本地
        File::deleteDirectory('blur/temp/' . $_key);
        Cache::forget($_key);


        return Response::json([
            'success' => true
        ]);
    }
    //添加滤镜功能，结束
}