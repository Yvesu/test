<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordEffects extends Model
{
    //
    protected $table = 'keyword_effects';

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword_id','effectsTemporary_id','effects_id','time_add','time_update'
    ];

    public $timestamps = false;
}
