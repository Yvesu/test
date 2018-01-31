<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserReviewChildLog extends Model
{
    //
    protected $table = 'filmfest_user_review_child_log';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','filmfest_id','production_id',
        'doing','cause','time_add','time_update'
    ];

    public $timestamps = false;
}
