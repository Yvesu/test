<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserUserGroup extends Model
{
    //
    protected $table = 'filmfest_user_user_group';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','time_add','time_update',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsToMany('App\Models\User','filmfest_user_user_user_group','group_id','user_id');
    }


    public function filmfest()
    {
        return $this->belongsTo('App\Models\Filmfests','filmfest_id','id');
    }

    public function roleGroup()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserRoleGroup','filmfest_user_user_group_role_group','user_group_id','role_group_id');
    }

}
