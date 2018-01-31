<?php

namespace App\Api\Controllers;

use App\Models\Tweet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class YyController extends Controller
{
    public function index()
    {
        $tweet = Tweet::find([1577,1576]);
        dd($tweet);
    }
}
