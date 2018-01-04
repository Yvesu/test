<?php

namespace App\Models\Office;

use Illuminate\Database\Eloquent\Model;

class WebIndexFile extends Model
{
    //
    protected $table = 'web_index_file';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type','name','time_add','time_update'
    ];
}
