<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitHistory extends Model
{
    protected $table = 'visit_history';

    protected $fillable = [
        'from',
        'to',
        'status',
        'created_at',
        'updated_at',
        'class_time',
    ];

    public $timestamps = false;

    public function belongToUser()
    {
        return $this -> belongsTo('App\Models\User','from','id');
    }
}
