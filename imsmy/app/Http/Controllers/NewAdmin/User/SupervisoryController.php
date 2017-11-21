<?php

namespace App\Http\Controllers\NewAdmin\User;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class SupervisoryController extends Controller
{
    //
    public function index()
    {
        try{
            $todayNewUser = User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->get()->count();
            $todayNewUserWomen = (round((User::where('created_at','>',date('Y-m-d H:i:s',strtotime(date('Y-m-d',time()))))->where('sex','=',0)->get()->count())/$todayNewUser,2)*100).'%';
            dd($todayNewUserWomen);
        }catch (ModelNotFoundException $e){}
    }
}
