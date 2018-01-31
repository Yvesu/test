<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserRole extends Model
{
    //
    protected $table = 'filmfest_user_role';

    protected $primaryKey = 'id';

    protected $fillable = [
        'role_name','role_des','time_add','time_update',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与用户关系  多对多
     */
    public function user()
    {
        return $this->belongsToMany('App\Models\User','user_filmfest_user_role','role_id','user_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与权限表关系  多对多
     */
    public function permission()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserPermission','filmfest_user_role_permission','role_id','permission_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与竞赛关系
     */
    public function filmfests()
    {
        return $this->belongsToMany('App\Models\Filmfests','filmfest_user_filmfest_role','role_id','filmfest_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与角色组关系  多对多
     */
    public function group()
    {
        return $this->belongsToMany('App\Models\FilmfestUser\FilmfestUserRoleGroup','filmfest_user_role_role_group','role_id','group_id');
    }

    public function pass()
    {
        return $this->belongsTo('App\Models\FilmfestUser\FilmfestUserPass','pass_id','id');
    }
}
