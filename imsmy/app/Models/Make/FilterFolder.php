<?php

namespace App\Models\Make;

use Illuminate\Database\Eloquent\Model;

class FilterFolder extends Model
{
    //
    protected $table = 'filter_folder';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filter_id',
        'folder_id',
        'time_create',
        'time_update',
    ];

    public $timestamps = false;
}
