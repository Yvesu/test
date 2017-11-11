<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\Address\AddressCity;
use App\Models\Address\AddressCountry;
use App\Models\Address\AddressCounty;
use App\Models\Address\AddressProvince;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddAddressController extends Controller
{
    //
    /**
     * 添加国家
     */
    public function addresscountry()
    {
        try{
            $data = [];
            $country = AddressCountry::get();
            foreach ($country as $item=>$value)
            {
                array_push($data,['id' => $value->id,'Name' => $value->Name,'Code' => $value->Code]) ;
            }
            return response()->json(['data'=>$data]);
        }catch(ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }
    }

    /**
     * 添加省份
     */
    public function addressprovince(Request $request)
    {
        try{
            $id = $request->get('id');
//        $province = AddressProvince::select('Pid')->where('Pid','=',$id)->get();
            $province = AddressCountry::find($id)->state;
            $data = [];
            foreach ($province as $item => $value)
            {
                array_push($data,['id' => $value->id,'Name' => $value->Name,'Code' => $value->Code,'Pid' => $value->Pid]);
            }
            return response()->json(['data' => $data]);
        }catch(ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }


    }

    /**
     * 添加城市(上一级是省份)
     */
    public function addresscity(Request $request)
    {
        try{
            $id = $request->get('id');
            $city = AddressProvince::find($id)->city;
            $data=[];
            foreach($city as $item => $value)
            {
                array_push($data,['id' => $value->id,'Name' => $value->Name,'Code' => $value->Code,'Pid' => $value->Pid,'Tid' => $value->Tid]);
            }
            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }


    }

    /**
     * 添加城市(上一级是国家)
     */
    public function addressstate(Request $request)
    {
        try{
            $id = $request->get('id');
            $city = AddressCountry::find($id)->city;
            $data = [];
            foreach($city as $item => $value)
            {
                array_push($data,['id' => $value->id,'Name' => $value->Name,'Code' => $value->Code,'Tid' => $value->Tid]);
            }

            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'], 404);
        }

    }

    /**
     * 添加区县
     */
    public function addresscounty(Request $request)
    {
        try{
            $pid = $request->get('Code');
            $tid = $request->get('Pid');
            $cid = $request->get('Tid');
            $county = AddressCounty::where('Pid',$pid)->where('Tid',$tid)->where('Cid',$cid)->get();
            $data = [];
            foreach($county as $item => $value)
            {
                array_push($data,['id' => $value->id,'Name' => $value->Name,'Code' => $value->Code,'Pid' => $value-> Pid,'Tid' => $value->Tid,'Cid' => $value->Cid]);
            }
            return response()->json(['data'=>$data]);
        }catch (ModelNotFoundException $e){
            return response()->json(['error' => 'not_found'],404);
        }
    }

}
