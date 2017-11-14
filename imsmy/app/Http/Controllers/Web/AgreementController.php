<?php

namespace App\Http\Controllers\Web;

use App\Api\Controllers\BaseController;
use App\Models\Agreement;

class AgreementController extends BaseController
{
    public function register()
    {
        return $this -> index(1);
    }

    public function report()
    {
        return $this -> index(2);
    }

    public function sponsor()
    {
        return $this -> index(3);
    }

    public function invest()
    {
        return $this -> index(4);
    }

    public function lease()
    {
        return $this -> index(5);
    }

    public function role()
    {
        return $this -> index(6);
    }

    public function rule()
    {
        return $this -> index(7);
    }

    public function index($type)
    {
        return view('/web/agreement/index',['data'=>Agreement::active()->where('type',$type)->first()]);
    }
}