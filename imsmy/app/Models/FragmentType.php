<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FragmentType extends Model
{
    //
    protected $table = 'fragmenttype';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'ename',
        'icon',
        'active',
        'sort',
        'time_add',
        'time_update'
    ];

    public $timestamps = false;

    public function belongsToManyFragment()
    {
        return $this->belongsToMany('App\Models\Fragment','fragmenttype_fragment','fragmentType_id','fragment_id');
    }

}
