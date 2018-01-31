<?php

namespace App\Http\Middleware;

use App\Models\Filmfests;
use App\Models\User;
use Closure;

class FilmfestUserIssue
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$id)
    {
        $filmfest_id = $request->get('id');
        $user = \Auth::guard('api')->user()->id;
        $issue = User::where('id',$user)->whereHas('filmfest_role',function ($q)use($filmfest_id){
            $q->where('role_name','发起者')->where('filmfest_id',$filmfest_id);
        })->first();
        if($issue){
            return $next($request);
        }else{
            return response(['message'=>'您不具备此权限'],200);
        }
    }
}
