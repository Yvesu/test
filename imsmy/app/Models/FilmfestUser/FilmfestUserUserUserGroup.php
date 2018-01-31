<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserUserUserGroup extends Model
{
    //
    protected $table = 'filmfest_user_user_user_group';

    protected $priamaryKey = 'id';

    protected $fillable = [
        'user_id','group_id','time_add','time_update',
    ];

    public $timestamps = false;

}
