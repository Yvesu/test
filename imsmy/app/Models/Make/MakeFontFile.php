<?php
namespace App\Models\Make;

use App\Models\Common;

class MakeFontFile extends Common
{
    protected $table = 'make_font_file';

    protected $fillable = [
        'name',
        'cover',
        'address',
        'sort',
        'size',
        'active',
        'time_add',
        'time_update',
        'test_result',
    ];

    public $timestamps = false;

    public function scopeOfSearch($query,$search,$type=2)
    {
        if(!$search) return $query;

        switch($type){
            // id
            case 1:
                return $query -> where('id',(int)$search);
                break;
            // name
            case 2:
                return $query -> where('name','like',"%$search%");
                break;
            default:
                return $query;
        }
    }
}