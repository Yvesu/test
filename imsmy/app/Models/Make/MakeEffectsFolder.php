<?php

namespace App\Models\Make;

use App\Models\Common;

/**
 * 视频制作 效果文件夹名
 * Class MakeEffectsFolder
 * @package App\Models
 */
class MakeEffectsFolder extends Common
{
    protected  $table = 'make_effects_folder';

    protected $fillable = [
        'name',
        'count',
        'sort',
        'active',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

    /**
     * 一对多关系 多个文件
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyFiles()
    {
        return $this -> hasMany('App\Models\Make\MakeEffectsFile','folder_id','id');
    }

    public function scopeOfSearch($query,$search,$condition)
    {
        // 条件
        switch($condition){
            case 1:
                return $query -> where('id','like',(int)$search);
                break;
            case 2:
                return $query -> where('name','like','%'.$search.'%');
                break;
            default:
                return $query;
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与创建人关系
     */
    public function belongsToAdministrator()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','create_id','id');
    }

    public function belongsToAdministratorOperator()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','operator_id','id');
    }

}