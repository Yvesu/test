<?php

namespace App\Http\Controllers\NewWeb;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TestIndexController extends Controller
{
    private $protocol = 'http://';

    private $paginate = 20;
    //
    public function index(Request $request)
    {

        try{
            $video = $this->protocol.'v.cdn.hivideo.com/web_bg.mp4';
            return response()->json(['data'=>$video],200);
        }catch (ModelNotFoundException $q){
            return response()->json(['error'=>'not_found'],404);
        }
    }
}
