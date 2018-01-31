<?php

namespace App\Models\FilmfestUser;

use Illuminate\Database\Eloquent\Model;

class FilmfestUserReviewLog extends Model
{
    //
    protected $table = 'filmfest_user_review_log';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','filmfest_id','production_id','is_complete_watch','watch_num',
        'complete_watch_num','time_add','time_update',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与用户表关系  反向一对多
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与作品表关系  反向1对多
     */
    public function production()
    {
        return $this->belongsTo('App\Models\TweetProduction','production_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与竞赛表关系 反向1对多
     */
    public function filmfest()
    {
        return $this->belongsTo('App\Models\Filmfests','filmfest_id','id');
    }
}
