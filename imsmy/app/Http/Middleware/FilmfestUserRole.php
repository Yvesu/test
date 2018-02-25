<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class FilmfestUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $filmfest_id = $request->get('id');
        $user = \Auth::guard('api')->user()->id;
        $role_id = $request->get('role_id',null);
        if(is_null($role_id)){
            return response()->json(['message'=>'提交数据不合法'],200);
        }
        $is_ok = User::where('id',$user)
            ->whereHas('filmfest_role',function ($q) use($filmfest_id,$role_id){
                $q->where([['filmfest_user_role.id',$role_id],['filmfest_user_role.filmfest_id',$filmfest_id]])
                    ->orWhere([['filmfest_user_role.role_name','like','%发起%'],['filmfest_user_role.filmfest_id',$filmfest_id]]);
            })->first();
        if($is_ok){
            return $next($request);
        }else{
            return response()->json(['message'=>'Hey! Brother,Are you kidding me?'],200);
        }


    }
}
