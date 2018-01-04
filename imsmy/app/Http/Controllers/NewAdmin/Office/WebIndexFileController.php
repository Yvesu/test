<?php

namespace App\Http\Controllers\NewAdmin\Office;

use App\Models\Office\WebIndexFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use CloudStorage;

class WebIndexFileController extends Controller
{
    //
    public function upUserIndex(Request $request)
    {
        try{
            $js_name = $request->get('js_name',null);
            $css_name = $request->get('css_name',null);
            if(is_null($name)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $keys1 = [];
            $keys2 = [];
            array_push($keys2,$js_name);
            array_push($keys1,$css_name);
            $keyPairs1 = array();
            $keyPairs2 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            foreach ($keys2 as $key)
            {
                $keyPairs2[$key] = $key;
            }

            $srcbucket1 = 'hivideo-file-ects';
            $srcbucket2 = 'hivideo-file-ects';
            $destbucket1 = 'hivideo-file';
            $destbucket2 = 'hivideo-file';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
            if($message1[0]['code']==200 && $message2[0]['code']==200)
            {
                \DB::beginTransaction();
                WebIndexFile::where('type','=',0)->delete();
                $newJSData = new WebIndexFile;
                $newJSData -> name = 'file.cdn.hivideo.com/'.$js_name;
                $newJSData -> type =0;
                $newJSData -> folder = 0;
                $newJSData -> time_add = time();
                $newJSData -> time_update = time();
                $newJSData -> save();

                $newCSSData = new WebIndexFile;
                $newCSSData -> name = 'file.cdn.hivideo.com/'.$css_name;
                $newJSData -> type =0;
                $newCSSData -> folder = 1;
                $newCSSData -> time_add = time();
                $newCSSData -> time_update = time();
                $newCSSData -> save();

                \DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                return response()->json(['message'=>'fail']);
            }

        }catch (ModelNotFoundException $q){
            \DB::rollBack();
            return response()->json(['error'=>'not_found'],200);
        }
    }

    public function upAdminIndex(Request $request)
    {
        try{
            $js_name = $request->get('js_name',null);
            $css_name = $request->get('css_name',null);
            if(is_null($name)){
                return response()->json(['message'=>'数据不合法'],200);
            }
            $keys1 = [];
            $keys2 = [];
            array_push($keys2,$js_name);
            array_push($keys1,$css_name);
            $keyPairs1 = array();
            $keyPairs2 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            foreach ($keys2 as $key)
            {
                $keyPairs2[$key] = $key;
            }

            $srcbucket1 = 'hivideo-file-ects';
            $srcbucket2 = 'hivideo-file-ects';
            $destbucket1 = 'hivideo-file';
            $destbucket2 = 'hivideo-file';
            $message1 = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            $message2 = CloudStorage::copyfile($keyPairs2,$srcbucket2,$destbucket2);
            if($message1[0]['code']==200 && $message2[0]['code']==200)
            {
                \DB::beginTransaction();
                WebIndexFile::where('type','=',1)->delete();
                $newJSData = new WebIndexFile;
                $newJSData -> name = 'file.cdn.hivideo.com/'.$js_name;
                $newJSData -> folder = 0;
                $newJSData -> type =1;
                $newJSData -> time_add = time();
                $newJSData -> time_update = time();
                $newJSData -> save();

                $newCSSData = new WebIndexFile;
                $newCSSData -> name = 'file.cdn.hivideo.com/'.$css_name;
                $newCSSData -> folder = 1;
                $newJSData -> type =1;
                $newCSSData -> time_add = time();
                $newCSSData -> time_update = time();
                $newCSSData -> save();

                \DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                return response()->json(['message'=>'fail']);
            }

        }catch (ModelNotFoundException $q){
            \DB::rollBack();
            return response()->json(['error'=>'not_found'],200);
        }
    }
}
