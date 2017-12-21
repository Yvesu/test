<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkTweet extends Model
{
    protected $table = 'mark_tweet';

    protected $fillable = [
        'tweet_id',
        'url',
        'create_time',
        'active',
    ];

    public $timestamps = false;

    public function belongToTweet()
    {
        return $this -> belongsTo('App\Models\Tweet','id','tweet_id');
    }
}


