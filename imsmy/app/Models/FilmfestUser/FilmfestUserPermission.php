<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserPermission extends Model
{
    //
    protected $table = 'filmfest_user_permission';

    protected $primaryKey = 'id';

    protected $fillable = [
        'permission_name','permission_des','time_add','time_update',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与角色表关系  多对多
     */
    public function role()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserRole','filmfest_user_role_permission','permission_id','role_id');
    }
}
