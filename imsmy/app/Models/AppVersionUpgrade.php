<?php
namespace App\Models;

class AppVersionUpgrade extends Common
{
    protected  $table = 'app_version_upgrade';

    protected $fillable = [
        'app_id',
        'version_id',
        'version_mini',
        'version_code',
        'type',
        'apk_url',
        'upgrade_point',
        'status',
        'time_add',
        'time_update',
    ];

    public $timestamps = false;

}