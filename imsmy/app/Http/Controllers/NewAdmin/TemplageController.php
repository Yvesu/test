<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\FragmentType;
use App\Services\CloudStorage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class TemplageController extends Controller
{

    /**
     * 模板-添加分类
     */
    public function addType(Request $request)
    {
        try{
            $admin = Auth::guard('api')->user();
            $operator = $admin->id;
            $creater = $admin->id;
            $name = $request->get('name');
            $icon = $request->file('type_icon');
            $active = $request->get('active',0);
            if(is_null($name) || is_null($icon)){
                return;
            }

            // 判断分类是否存在，如果存在，返回并将信息存至session中
            if(FragmentType::where('name',$name)->first()){
                return response()->json(['error'=>'已存在'],200);
            }
            DB::beginTransaction();
            $data = new FragmentType;
            if($active == 1)
            {
                $sort =1 + FragmentType::max('sort');
                $data ->sort = $sort;
            }
            $data->name = $name;
            $data->icon = 'temp_key';
            $data->active = $active;
            $data->operator_id = $operator;
            $data->create_id =$creater;
            $data->time_add = time();
            $data->time_update = time();
            $data->save();
            return response()->json(['data'=>'添加成功'],200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
