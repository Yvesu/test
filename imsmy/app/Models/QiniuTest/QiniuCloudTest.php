<?php

namespace App\Models\QiniuTest;

use Illuminate\Database\Eloquent\Model;

class QiniuCloudTest extends Model
{
    //
    protected $table = 'qiniu_cloud_test';

    protected $primaryKye = 'id';

    protected $fillable = [
        'content','type','time_add','time_update'
    ];

    public $timestamps = false;
}
