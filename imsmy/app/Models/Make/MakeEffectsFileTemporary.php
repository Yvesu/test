<?php

namespace App\Models\Make;

use Illuminate\Database\Eloquent\Model;

class MakeEffectsFileTemporary extends Model
{
    //
    protected $table = 'make_effects_file_temporary';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','name','intro','address','cover','folder_id','duration','size','integral','vipfree',
        'distinguishability_x','distinguishability_y','preview_address',
    ];

    public $timestamps = false;

    public function belongsToFolder()
    {
        return $this -> belongsTo('App\Models\Make\MakeEffectsFolder','folder_id','id');
    }

    public function keyWord()
    {
        return $this->belongsToMany('App\Models\Keywords','keyword_effects','effectsTemporary_id','keyword_id');
    }
}
