<?php

namespace App\Models\Make;

use Illuminate\Database\Eloquent\Model;

class FilterKeyword extends Model
{
    //
    protected $table = 'filter_keyword';

    protected $primaryKey = 'id';

    protected $fillable = [
        'filter_id','keyword_id','time_add','time_update'
    ];

    public $timestamps = false;
}
