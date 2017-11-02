<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryboardTemporary extends Model
{
    //
    protected $table = 'storyboard_temporary';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'speed',
        'time_add',
        'time_update',
        'address',
        'fragment_id',
        'effects_id'
    ];

    /**
     * 发现一对多关系  与特效表相连
     */
    public function belongsToMakeEffectsFile()
    {
        return $this->belongsTo('App\Models\Make\MakeEffectsFile','effects_id','id');
    }

    public $timestamps = false;
}
