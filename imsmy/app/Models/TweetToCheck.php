<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetToCheck extends Model
{
   protected $table='tweet_to_qiniu';

   protected $fillable=[
       'id',
       'tweet_id',
       'create_time',
       'active',
   ];

   public $timestamps = false;

    public function hasOneTweet()
    {
        return $this->hasOne('App\Models\Tweet','id','tweet_id');
    }
}
