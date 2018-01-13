<?php

namespace App\Models\Filmfest;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    //
    protected $table = 'application_form';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name','english_name','duration','is_moretype','is_orther_web',
        'copyright','is_collective','create_people_num','create_collective_name',
        'create_time','production_des','creater_name','director_name','photography_name',
        'scriptwriter_name','cutting_name','hero_name','heroine_name','major','university_id',
        'university_name','adviser_name','adviser_phone','communication_address','creater_des',
        'other_creater_des','apply_time','time_add','time_update','festfilms_id'
    ];

    public $timestamps =false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     * 与电影单元关系  多对多
     */
    public function filmType()
    {
        return $this->belongsToMany('App\Models\FilmfestFilmType','film_type_application','application_id','type_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 和报名表联系方式关系  1对多
     */
    public function contactWay()
    {
        return $this->hasMany('App\Models\Filmfest\ApplicationContactWay','application_id','id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与电影节或竞赛表关系  反向一对多
     */
    public function festfilm()
    {
        return $this->belongsTo('App\Models\Filmfests','filmfests_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与学校  反向1对多
     */
    public function school()
    {
        return $this->belongsTo('App\Models\Filmfest\JoinUniversity','university_id','id');
    }
}
