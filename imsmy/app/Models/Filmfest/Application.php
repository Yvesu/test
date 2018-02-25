<?php

namespace App\Models\Filmfest;

use App\Models\FilmfestFilmfestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'other_creater_des','apply_time','time_add','time_update','festfilms_id','production_id'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与电影节报名表关系 反向1对多
     */
    public function filmTypeApplication()
    {
        return $this->belongsTo('App\Models\Filmfest\FilmTypeApplication','id','application_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与作品的关系
     */
    public function production()
    {
        return $this->belongsTo('App\Models\TweetProduction','production_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与作品和电影节关系表 关系  反向一对多
     */
    public function productionTweet()
    {
        return $this->belongsTo('App\Models\FilmfestsProductions','production_id','tweet_productions_id');
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


    /**
     * @param $query
     * @param $type
     * @return mixed
     * 查询单元类别
     */
    public function scopeType($query,$type,$filmfest_id)
    {
        if((int)$type===0){
            return $query->whereHas('production',function ($q){
                $q->whereHas('tweet',function ($a){
                    $a->whereHas('yellowCheck',function ($b){
                        $b->where('tweet_qiniu_check.image_qpulp',0)
                            ->where('tweet_qiniu_check.qpolitician',0)
                            ->where('tweet_qiniu_check.tupu_video',0);
                    });
                });
            });
        }elseif($type==999){
            return $query;
        }elseif ($type==1000){
            return $query->whereHas('production',function ($q){
                $q->whereHas('tweet',function ($a){
                    $a->whereHas('yellowCheck',function ($b){
                        $b->where('tweet_qiniu_check.image_qpulp',1)
                            ->orWhere('tweet_qiniu_check.qpolitician',1)
                            ->orWhere('tweet_qiniu_check.tupu_video',1);
                    });
                });
            });
        }else{
            $filmfestFilmType = FilmfestFilmfestType::where('type_id',$type)
                ->where('filmfest_id',$filmfest_id)->first();
            $lt_time = $filmfestFilmType->lt_time;
            $gt_time = $filmfestFilmType->gt_time;
            return $query->whereHas('production',function ($q) use($lt_time,$gt_time){
                $q->where('tweet.duration','>=',$lt_time)->where('tweet.duration','<=',$gt_time)
                    ->whereHas('tweet',function ($a){
                        $a->whereHas('yellowCheck',function ($b){
                            $b->where('tweet_qiniu_check.image_qpulp',0)
                                ->where('tweet_qiniu_check.qpolitician',0)
                                ->where('tweet_qiniu_check.tupu_video',0);
                        });
                    });
            })->whereHas('filmType',function ($q)use($type){
                $q->where('filmfest_film_type.id',$type);
            });
        }
    }


    /**
     * @param $query
     * @param $searchKey
     * @return mixed
     * 查询关键字
     */
    public function scopeKey($query,$searchKey)
    {
        if(is_null($searchKey)){
            return $query;
        }else{
            return $query->where('name','like','%'.$searchKey.'%')->orWhere('number','like','%'.$searchKey.'%');
        }
    }

    /**
     * @param $query
     * @param $searchCountry
     * @return mixed
     * 查询国家
     */
    public function scopeCountry($query,$searchCountry)
    {
        if(is_null($searchCountry)){
            return $query;
        }else{
            return $query->where('communication_address_country',$searchCountry);
        }
    }

    /**
     * @param $query
     * @param $searchDuration
     * @return mixed
     * 查询时长
     */
    public function scopeDuration($query,$searchDuration)
    {
        if((int)$searchDuration === 0 ){
            return $query;
        }elseif ((int)$searchDuration === 1 ){
            return $query->where('duration','<',600);
        }elseif ($searchDuration == 2){
            return $query->where('duration','<',1800);
        }elseif ($searchDuration == 3){
            return $query->where('duration','<',3600);
        }else{
            return $query->where('duration','>=',3600);
        }
    }


    /**
     * @param $query
     * @param $searchStatus
     * @param $filmfest_id
     * @return mixed
     * 初审页的状态条件方法
     */
    public function scopeSelectStatus($query,$searchStatus,$filmfest_id)
    {
        if((int)$searchStatus === 0){
            return $query;
        }elseif ((int)$searchStatus === 1){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.again_select_status','>',0);
            });
        }elseif ($searchStatus == 2){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.status','=',3);
            });
        }else{
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.status','=',0);
            });
        }
    }

    /**
     * @param $query
     * @param $searchStatus
     * @param $filmfest_id
     * @return mixed
     * 复审状态查询
     */
    public function scopeAgainSelectStatus($query,$searchStatus,$filmfest_id)
    {
        if((int)$searchStatus === 0){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.again_select_status','!=',0);
            });
        }elseif ((int)$searchStatus === 1){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.again_select_status','=',2);
            });
        }elseif($searchStatus == 2){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.again_select_status','=',1);
            });
        }else{
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.again_select_status','=',2)
                    ->where('filmfests_tweet_production.join_select_status','!=',0);
            });
        }
    }


    /**
     * @param $query
     * @param $searchStatus
     * @param $filmfest_id
     * @return mixed
     * 入围评审
     */
    public function scopeJoinSelectStatus($query,$searchStatus,$filmfest_id)
    {
        if((int)$searchStatus === 0){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.join_select_status','!=',0);
            });
        }elseif((int)$searchStatus === 1){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.join_select_status','=',2);
            });
        }else{
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.join_select_status','=',1);
            });
        }

    }


    /**
     * @param $query
     * @param $searchStatus
     * @param $filmfest_id
     * @return mixed
     * 专业查询
     */
    public function scopePerofessuibalSelectStatus($query,$searchStatus,$filmfest_id)
    {
        if((int)$searchStatus === 0){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.perofessuibal_select_status','!=',0);
            });
        }elseif((int)$searchStatus === 1){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.perofessuibal_select_status','=',2);
            });
        }else{
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.perofessuibal_select_status','=',1);
            });
        }
    }

    /**
     * @param $query
     * @param $searchStatus
     * @param $filmfest_id
     * @return mixed
     * 获奖查询
     */
    public function scopeWinSelectStatus($query,$searchStatus,$filmfest_id)
    {
        if((int)$searchStatus === 0){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.status',4)
                    ->where('filmfests_tweet_production.again_select_status',2)
                    ->where('filmfests_tweet_production.join_select_status',2)
                    ->where('filmfests_tweet_production.perofessuibal_select_status',2);
            });
        }elseif((int)$searchStatus === 1){
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.status',4)
                    ->where('filmfests_tweet_production.again_select_status',2)
                    ->where('filmfests_tweet_production.join_select_status',2)
                    ->where('filmfests_tweet_production.perofessuibal_select_status',2)
                    ->where('filmfests_tweet_production.is_win','=',1);
            });
        }else{
            return $query->whereHas('productionTweet',function ($q)use($filmfest_id){
                $q->where('filmfests_tweet_production.filmfests_id',$filmfest_id)
                    ->where('filmfests_tweet_production.status',4)
                    ->where('filmfests_tweet_production.again_select_status',2)
                    ->where('filmfests_tweet_production.join_select_status',2)
                    ->where('filmfests_tweet_production.perofessuibal_select_status',2)
                    ->where('filmfests_tweet_production.is_win','=',0);
            });
        }
    }


    /**
     * @param $query
     * @param $searchSchool
     * @return mixed
     * 查询学校
     */
    public function scopeSchool($query,$searchSchool)
    {
        if(is_null($searchSchool)){
            return $query;
        }else{
            return $query->where('university_id',$searchSchool);
        }
    }

    /**
     * @param $query
     * @param $order
     * @param $by
     * @return mixed
     * 排序
     */
    public function scopeOrder($query,$order,$by)
    {
        if($order == 2){
//            return $query->with('filmTypeApplication',function ($q){
//                $q->select(['film_type_application.application_id','film_type_application.count(type_id)'])
//                    ->where('film_type_application.count(type_id)','>',1)
//                    ->groupBy('film_type_application.application_id')
//                    ->orderBy('film_type_application.count(type_id)','desc');
//            });
            $data = DB::select('SELECT * FROM (select application_id,count(*) as type FROM film_type_application  GROUP BY application_id order BY type desc) a WHERE a.type>?',[1]);
            $aa = [];
            foreach ($data as $k => $v)
            {
                array_push($aa,$v->application_id);
            }
            return $query->whereIn('id',$aa);
        }elseif (is_null($order)){
            return $query;
        }else{
            return $query->orderBy($order,$by);
        }
    }

    public function scopeRangeTime($query,$startTime,$endTime)
    {
        if(is_null($endTime)){
            return $query;
        }else{
            return $query->where('time_add','>',$startTime)->where('time_add','<',$endTime);
        }
    }
}
