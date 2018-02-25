<?php

namespace App\Http\Middleware;

use App\Models\Filmfests;
use App\Models\User;
use Closure;
use function foo\func;

class Filmfest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$filmfest_id)
    {
        $filmfest_id = $request->get('id');
        $user = \Auth::guard('api')->user()->id;
        $time = time();
        $filmfest = Filmfests::select('id')->where('id','=',$filmfest_id)->whereHas('user',function ($q) use($user){
            $q->where('user.id','=',$user);
        })->first();
        if($filmfest && $time>($filmfest->close_filmfest_time)){
            $is_enter_end = User::where('id',$user)->whereHas('filmfestUserRoleGroup',function ($q) use($filmfest_id){
                $q->where('filmfest_user_role_group.filmfest_id',$filmfest_id)->where('filmfest_user_role_group.enter_end_status',1);
            })->first();
        }else{
            $is_enter_end = true;
        }
        $issue = User::where('id',$user)->whereHas('filmfest_role',function ($q)use($filmfest_id){
            $q->where('role_name','like','%发起%')->where('filmfest_id',$filmfest_id);
        })->first();
        if($filmfest && ($is_enter_end || $issue)){
            return $next($request);
        }else{
            return response()->json(['message'=>'您不具备此权限'],200);
        }
    }
}
