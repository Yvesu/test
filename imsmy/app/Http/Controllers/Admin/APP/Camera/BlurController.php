<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/28
 * Time: 18:00
 */

namespace app\Http\Controllers\Admin\APP\Camera;


use App\Http\Controllers\Controller;
use App\Models\Blur;
use App\Models\BlurClass;
use App\Models\GPUImage;
use App\Models\GPUImageValue;
use CloudStorage;
use Illuminate\Http\Request;
use File;
class BlurController extends Controller
{
    /**
     * 显示滤镜主页面
     * @param Request $request
     * @return $this
     */
    public function index(Request $request)
    {
        $active = $request->get('active');
        $id     = $request->get('id');
        $blurs  = Blur::with('belongsToBlurClass')->where('active',1)->get();
        if(!is_null($active)){
            $blurs = Blur::with('belongsToBlurClass')
                ->where('active',$active)
                ->whereHas('belongsToBlurClass',function($q){
                    $q->where('active','!=',0);
                })
                ->get();
        }

        if(!is_null($id)){
            $blurs = Blur::with('belongsToBlurClass')
                ->where('blur_class_id',$id)
                ->where('active',1)
                ->get();
        }

        $classes = BlurClass::where('active',1)->get();


        return view('admin.app.camera.blur.blur')
                ->with([
                    'classes'   =>  $classes,
                    'blurs'     =>  $blurs
                ]);
    }

    public function show($id)
    {
        $blur = Blur::where('id',$id)->with('belongsToBlurClass')->first();

        if(!isset($blur) || 0 == $blur->belongsToBlurClass->active){
            abort(404);
        }

        $parameters = json_decode($blur->parameter,true);
        foreach($parameters as &$parameter){
            $parameter['name_zh'] = GPUImage::where('name_en',$parameter['name'])->first()->name_zh;
            foreach($parameter['data'] as &$item){
                $item['name_zh']  = GPUImageValue::where('name_en',$item['name'])->first()->name_zh;
            }
            unset($item);
        }
        unset($parameter);

        $sequence_diagrams = json_decode($blur->sequence_diagram,true);

        return view('admin.app.camera.blur.viewBlur')
                ->with([
                    'blur'              => $blur,
                    'parameters'        => $parameters,
                    'sequence_diagrams' => $sequence_diagrams
                ]);
    }

    public function enable($id)
    {
        $blur = Blur::find($id);
        $blur->active = 1;
        $blur->save();
        return redirect('/admin/app/camera/blur');
    }

    public function disable($id)
    {
        $blur = Blur::find($id);
        $blur->active = 0;
        $blur->save();
        return redirect('/admin/app/camera/blur?active=0');
    }

    public function delete($id)
    {
        try {
            $blur = Blur::find($id);
            if(2 != $blur->active){
                return false;
            }

            CloudStorage::deleteDirectory('blur/' . $id);
            $blur->delete();
            return redirect('/admin/app/camera/blur?active=2');
        } catch (\Exception $e) {
            //TODO 错误处理
            return redirect('/admin/app/camera/blur?active=2');
        }

    }
}