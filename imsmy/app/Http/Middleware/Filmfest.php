<?php

namespace App\Http\Middleware;

use App\Models\Filmfests;
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
        $filmfest = Filmfests::select('id')->where('id','=',$filmfest_id)->whereHas('user',function ($q) use($user){
            $q->where('user.id','=',$user);
        })->first();
        if($filmfest){
            return $next($request);
        }else{
            return response()->json(['message'=>'您不具备此权限'],200);
        }
    }
}
