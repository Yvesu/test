<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelsTransformer;
use App\Models\Channel;
use App\Models\UsersLikes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class UserLikes extends Controller
{
    private $channelsTransformer;

    public function __construct
    (
        ChannelsTransformer $channelsTransformer
    )
    {
        $this -> channelsTransformer = $channelsTransformer;
    }

    /**
     * 查看用户喜好
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{
            //获取用户信息
            $user = Auth::guard('api')->user();

            if (is_null($user))   return response()->json(['message'=>'bad request'],400);

            $channels = UsersLikes::where('user_id',$user->id)->first();

            $channels_id = explode(',',$channels ->channel_id);

            $data =  Channel::where('active',1)
               ->whereIn('id',$channels_id)
               ->get(['id','name','ename','icon']);

            return response()->json([
                'data'=>$this->channelsTransformer->transformCollection($data->all()),
            ],200);

        }catch (\Exception $e){
            return response()->json(['message'=>'bad request'],500);
        }
    }

    public function edit(Request $request)
    {
        try{
            //接收用户的信息
            $user = Auth::guard('api')->user();

            //接收用户新的喜好
            $new_channels = $request->get('newlikes');

            if ( is_null($new_channels) )  return response()->json(['message'=>'bad request'],400);

            \DB::beginTransaction();

            $res = UsersLikes::where('user_id',$user->id)->update(['channel_id'=>$new_channels]);

            if ($res){
                \DB::commit();
                return response()->json(['message'=>'success'],200);
            }else{
                \DB::rollBack();
                return response()->json(['message'=>'failed'],403);
            }
        }catch (\Exception $e){
            return response()->json(['message'=>'bad request'],500);
        }
    }
}
