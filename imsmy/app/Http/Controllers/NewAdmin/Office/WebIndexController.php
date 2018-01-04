<?php

namespace App\Http\Controllers\NewAdmin\Office;

use App\Models\Office\WebIndexFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebIndexController extends Controller
{
    //
    public function userIndex()
    {
        $js_name = WebIndexFile::where('type','=',0)->where('folder','=',0)->first();
        $css_name = WebIndexFile::where('type','=',0)->where('folder','=',1)->first();
        return view('web1',['js_name'=>$js_name,'css_name'=>$css_name]);
    }
}
