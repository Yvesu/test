<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmfestsProductions extends Model
{
    //
    protected $table = 'filmfests_tweet_production';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filmfests_id','tweet_productions_id','time_add','time_update',
    ];

    public $timestamps = false;
}
