<?php

namespace App\Http\Controllers\NewAdmin\MixResource;

use App\Models\DownloadCost;
use App\Models\Make\MakeEffectsFolder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    //
    public function type()
    {
        try{
            DB::beginTransaction();
            $type = MakeEffectsFolder::where('active','=',1)->get();
            $data = [];
            foreach($type as $k => $v)
            {
                $temp = [
                    'id'=>$v->id,
                    'type'=>$v->name,
                ];
                array_push($data,$temp);
            }
            DB::commit();
            return response()->json(['data'=>$data],200);
        }catch (ModelNotFoundException $q){
            DB::rollBack();
            return response()->json(['error'=>'not_found'],404);
        }
    }

    public function downloadCost()
    {
        try{
            $data = DownloadCost::get();
            $intergal = [];
            foreach($data as $k => $v)
            {
                array_push($intergal,['intergal'=>$v->details]);
            }
            return response()->json(['data'=>$intergal],200);
        }catch (ModelNotFoundException $e){
            return response()->json(['error'=>'not_found'],404);
        }
    }
}
