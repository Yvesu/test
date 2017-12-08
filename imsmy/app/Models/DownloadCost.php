<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadCost extends Model
{
    //
    protected $table = 'downloadCost';

    protected $primaryKey = 'id';

    protected $fillable = [
        'details',
        'time_add',
        'time_update',
        'vipfree'
    ];

    public $timestamps = false;
}
