<?php

namespace App\Http\Controllers\NewAdmin;

use App\Models\FragmentType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Auth;
use CloudStorage;
class TemplageController extends Controller
{

    private $protocol='http://';
    /**
     * 模板-添加分类
     */
    public function addType(Request $request)
    {
        try{
            $keys1 = [];
            $admin = Auth::guard('api')->user();
            $operator = $admin->id;
            $creater = $admin->id;
            $name = $request->get('name');
            $icon = $request->get('type_icon');
            $active = $request->get('active',0);
            if(is_null($name) || is_null($icon)){
                return response()->json(['error'=>'不能添加空值'],200);
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
            $data->icon = $this->protocol.'img.cdn.hivideo.com/'.$icon;
            $data->active = $active;
            $data->operator_id = $operator;
            $data->create_id =$creater;
            $data->time_add = time();
            $data->time_update = time();
            $data->save();
            array_push($keys1,$icon);
            $keyPairs1 = array();
            foreach($keys1 as $key)
            {
                $keyPairs1[$key] = $key;
            }
            $srcbucket1 = 'hivideo-img-ects';
            $destbucket1 = 'hivideo-img';
            $message = CloudStorage::copyfile($keyPairs1,$srcbucket1,$destbucket1);
            DB::commit();
            return response()->json(['data'=>'添加成功',$message],200);
        }catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'not_found'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
