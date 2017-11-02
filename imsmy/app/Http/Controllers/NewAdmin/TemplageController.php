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
            $name = $request->get('name');
            $icon = $request->file('type_icon');
            if(is_null($name) || is_null($icon)){
                return;
            }

            // 判断分类是否存在，如果存在，返回并将信息存至session中
            if(FragmentType::where('name',$name)->first()){
                return response()->json(['error'=>'已存在'],200);
            }

            DB::beginTransaction();
            $fragmenttype = FragmentType::create([
                'name'      => $name,
                'sort'      => ++Channel::orderBy('sort','DESC')->first()->sort,
                'icon'      => 'temp_key',
                'hash_icon' => 'temp_hash',
                'active'    => 0
            ]);

            // 获取随机数
            $rand = mt_rand(1000000,9999999);

            $result = CloudStorage::putFile(
                'channel/' . $fragmenttype->id . '/' . getTime() . $rand . '.' . $icon->getClientOriginalExtension(),
                $icon);

            if($result[1] !== null){
                DB::rollBack();
            } else {
                $fragmenttype->hash_icon = $result[0]['hash'];
                $fragmenttype->icon = $result[0]['key'];
                $fragmenttype->save();
                DB::commit();
            }

            return response()->json(['data'=>'添加成功'],200);
        }catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
