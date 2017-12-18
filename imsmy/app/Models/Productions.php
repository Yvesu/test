<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Productions extends Model
{
    //
    protected $table = 'productions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','active','playnum','cover','address',
        'time_add','time_update','time_up_over','is_priviate','user_id','size'
    ];

    public $timestamps = false;

    /**
     * 与电影节关联  多对多
     */
    public function filmfests()
    {
        $this->belongsToMany('App\Models\Filmfests');
    }

    public function scopeActive($query,$active)
    {
        if(is_null($active)){
            return $query;
        }elseif($active == '9'){
            return $query->where('active','>=',3);
        }else{
            return $query->where('active','=',$active);
        }

    }

    public function scopeStatus($query,$status)
    {
        if(is_null($status)){
            return $query;
        }elseif($status=6){
            return $query->where('is_priviate','=',1);
        }else{
            return $query->where('active','=',$status);
        }
    }
}
