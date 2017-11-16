<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubtitleTemporary extends Model
{
    //
    protected $table = 'subtitle_temporary';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'content',
        'start_time',
        'end_time',
        'time_add',
        'time_update',
        'font_id',
        'fragment_id',
        'englishcontent',
        'slowInAndOut',
        'font_size',
    ];

    public $timestamps = false;

    public function belongsToFont()
    {
        return $this->belongsTo('App\Models\Make\MakeFontFile','font_id','id');
    }

}
