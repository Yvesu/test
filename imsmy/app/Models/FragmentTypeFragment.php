<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FragmentTypeFragment extends Model
{
    //
    protected $table = 'fragmentType_fragment';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id' ,
        'fragmentType_id',
        'fragment_id',
        'time_add',
        'time_update',
        'fragment_temporary_id'
    ];

    public $timestamps = false;
}
