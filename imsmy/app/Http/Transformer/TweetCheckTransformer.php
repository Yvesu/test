<?php

namespace App\Http\Transformer;

use App\Models\TweetLike;
//use App\Api\Transformer\UsersTransformer;
use App\Models\Admin\Administrator;
use App\Models\TweetActivity;
use App\Models\User;
use CloudStorage;
use Auth;

class TweetCheckTransformer extends Transformer
{

    public function transform($tweet)
    {

        return [
            'id'            =>  $tweet->id,
            'active'        =>  $tweet->active,
            'admin_user'    =>  $tweet->belongsToCheckAdmin->name,
            'time_add'      =>  $tweet->time_add,
        ];
    }
}