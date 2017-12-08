<?php

namespace App\Models\Tigase;

use Illuminate\Database\Eloquent\Model;


class TigNodes extends Model
{

    protected $table = 'tig_nodes';

//    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'nid',
        'parent_nid',
        'uid',
        'node'
    ];

}
