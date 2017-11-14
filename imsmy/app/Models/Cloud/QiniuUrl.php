<?php

namespace App\Models\Cloud;

use Illuminate\Database\Eloquent\Model;

class QiniuUrl extends Model
{
    //
    protected $table = 'qiniu_url';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type',
        'url',
        'zone_name',
        'location',
        'create_at',
        'update_at',
    ];

    public $timestamps =false;
}
