<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class TweetProductionApplication extends Model
{
    //
    protected $table = 'tweet_production_application';

    protected $primaryKey = 'id';

    protected $fillable = [
        'tweet_production_id','application_id','time_add','time_update'
    ];

    public $timestamps = false;
}
