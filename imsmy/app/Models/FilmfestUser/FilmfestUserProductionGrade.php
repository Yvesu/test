<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserProductionGrade extends Model
{
    //
    protected $table = 'filmfest_user_production_grade';

    protected $primaryKey = 'id';

    protected $fillable = [
        'production_id','filmfest_id','judge_id','grade','time_add','time_update'
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与作品表关系
     */
    public function production()
    {
        return $this->belongsTo('App\Models\TweetProduction','production_id','id');
    }
}
