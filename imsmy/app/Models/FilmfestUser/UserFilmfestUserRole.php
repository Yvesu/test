<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class UserFilmfestUserRole extends Model
{
    //
    protected $table = 'user_filmfest_user_role';

    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id','user_id','time_add','time_update',
    ];

    public $timestamps = false;

    public function role()
    {
        return $this->belongsTo('App\Models\FilmfestUser\FilmfestUserRole','role_id','id');
    }
}
