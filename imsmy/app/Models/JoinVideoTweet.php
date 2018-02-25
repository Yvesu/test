<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoinVideoTweet extends Model
{
    //
    protected $table = 'join_video_tweet';

    protected $primaryKey = 'id';

    protected $fillable = [
        'join_video_id','tweet_id','transcoding_video','high_video',
        'norm_video','video_m3u8','time_add','time_update','video'
    ];

    public $timestamps = false;



}
