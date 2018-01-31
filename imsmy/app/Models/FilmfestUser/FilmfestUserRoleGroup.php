<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserRoleGroup extends Model
{
    //
    protected $table = 'filmfest_user_role_group';

    protected $primaryKey = 'id';

    protected $fillabel = [
        'name','time_add',',time_update',
    ];

    public $timestamps = false;

    public function role()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserRole','filmfest_user_role_role_group','group_id','role_id');
    }

    public function filmfest()
    {
        return $this->belongsToMany('App\Models\Filmfests','filmfest_user_role_group_filmfest','role_group_id','filmfest_id');
    }

    public function user()
    {
        return $this->belongsToMany('App\Models\User','filmfest_user_user_role_group','role_group_id','user_id');
    }

    public function userGroup()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserUserGroup','filmfest_user_user_group_role_group','role_group_id','user_group_id');
    }
}
