<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class JoinUniversity extends Model
{
    //
    protected $table = 'join_university';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update'
    ];

    public $timestamps = false;


    public function tweetProduction()
    {
        return $this->hasMany('App\Models\TweetProduction','join_university_id','id');
    }
}
