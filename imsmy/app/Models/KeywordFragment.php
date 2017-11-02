<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordFragment extends Model
{
    //
    protected $table = 'keyword_fragment';

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword_id',
        'fragment_id',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;
}
