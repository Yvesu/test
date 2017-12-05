<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/4/21
 * Time: 14:27
 */

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Common
{
    protected  $table = 'tweet';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'retweet',
        'original',
        'photo',
        'video',
        'duration',
        'size',
        'location',
        'type',
        'screen_shot',
        'shot_width_height',
        'user_top',
        'visible',
        'visible_range',
        'is_download',
        'active',
        'location_id',
        'phone_id',
        'label_id',
        'label_expires',
        'retweet_count',
        'reply_count',
        'like_count',
        'top_expires',
        'recommend_expires',
        'browse_times',
        'tweet_grade_total',
        'tweet_grade_times',
        'category',
        'category_id',
        'fragment_id',
        'filter_id',
        'template_id',
    ];

    /**
     * 内容 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneContent()
    {
        // 第二个参数是第一个参数的字段tweet_id，第三个参数是本类的id
        return $this->hasOne('App\Models\TweetContent','tweet_id','id');
    }

    /**
     * 动态与动态回复 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTweetReply()
    {
        return $this->hasMany('App\Models\TweetReply','tweet_id','id');
    }

    public function hasManyTweetLike()
    {
        return $this->hasMany('App\Models\TweetLike','tweet_id','id');
    }

    /**
     * 动态与 tweet_topic 表 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTopicTweet()
    {
        return $this->hasMany('App\Models\TweetTopic','tweet_id','id');
    }

    /**
     * 动态与 tweet_activity 表 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyActivityTweet()
    {
        return $this->hasMany('App\Models\TweetActivity','tweet_id','id');
    }

    /**
     * 赛事 动态关联表
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToActivityTweet()
    {
        return $this -> belongsTo('App\Models\TweetActivity','id','tweet_id');
    }

    /**
     * 动态与 channel_tweet 表 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyChannelTweet()
    {
        return $this->hasMany('App\Models\ChannelTweet','tweet_id','id');
    }

    /**
     * 动态与用户 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToUser()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    /**
     * 动态与发布系统 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToPhone()
    {
        return $this->belongsTo('App\Models\TweetPhone','phone_id','id');
    }

    /**
    * 动态与频道 多对多关系
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function belongsToManyChannel()
    {
        // 第二个参数是关联关系连接表的表名,第三个参数是本类的id，第四个参数是第一个参数那个类的id
        return $this->belongsToMany('App\Models\Channel','channel_tweet','tweet_id','channel_id');
    }

    /**
     * 动态屏蔽原因
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToReason()
    {
        // 第二个参数是关联关系连接表的表名,第三个参数是本类的id，第四个参数是第一个参数那个类的id
        return $this->belongsToMany('App\Models\TweetBlockingReason','tweet_blocking','tweet_id','reason_id') -> withPivot('time_add');
    }

    /**
     * 动态屏蔽管理人员
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToReasonAdmin()
    {
        // 第二个参数是关联关系连接表的表名,第三个参数是本类的id，第四个参数是第一个参数那个类的id
        return $this->belongsToMany('App\Models\Admin\Administrator','tweet_blocking','tweet_id','admin_id') -> withPivot('time_add');
    }

    /**
     * 检查审批日志
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToCheck()
    {
        return $this->belongsToMany('App\Models\Admin\Administrator','tweet_check_log', 'tweet_id', 'admin_id') -> withPivot('time_add');
    }

    /**
     * 转发动态与被转发动态 关系(追寻到根)
     * @return mixed
     */
    public function hasOneTweet()
    {
        return $this->hasOne('App\Models\Tweet','id','retweet')->with('hasOneTweet');
    }

    /**
     * 转发动态与原创动态 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneOriginal()
    {
        // 第二个参数是本类的id，第三个参数是第一个参数的字段original（即tweet表）
        return $this->hasOne('App\Models\Tweet','id','original');
    }

    /**
     * 热门 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneHot()
    {
        // 第二个参数是第一个参数的字段tweet_id（即 zx_tweet_hot 表），第三个参数是本类的id
        return $this->hasOne('App\Models\TweetHot','tweet_id','id');
    }

    /**
     * 置顶 推荐 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneTop()
    {
        // 第二个参数是第一个参数的字段tweet_id（即 zx_tweet_top 表），第三个参数是本类的id
        return $this->hasOne('App\Models\TweetTop','tweet_id','id');
    }




    /**
     * 推送 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOnePush()
    {
        // 第二个参数是第一个参数的字段tweet_id（即 tweets_push 表），第三个参数是本类的id
        return $this->hasOne('App\Models\TweetsPush','tweet_id','id');
    }

    public function hasManyAt()
    {
        return $this->hasMany('App\Models\TweetAt', 'tweet_id', 'id');
    }

    /**
     * 动态与频道 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasManyChannel()
    {
        return $this->belongsToMany('App\Models\Channel', 'channel_tweet', 'tweet_id', 'channel_id');
    }

    /**
     * 动态与标签 多对一关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToLabel()
    {
        // 第一个belongsToLabel方法是与App\Models\Label模型关联的,第二个他们关联的ID是本类的label_id,第三个参数表示第一个参数的ID值
        return $this->belongsTo('App\Models\Label','label_id','id');
    }

    /**
     * 好友
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToFriend()
    {
        return $this -> belongsTo('App\Models\Friend', 'user_id', 'from');
    }

    /**
     * 动态与话题 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToManyTopic()
    {
        return  $this->belongstoMany('App\Models\Topic', 'tweet_topic', 'tweet_id', 'topic_id');
    }

    /**
     * 某一话题下的置顶动态
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsToTopicTop()
    {
        return $this -> belongsTo('App\Models\TopicTopTweet', 'id', 'tweet_id');
    }

    /**
     * 动态与活动 多对多关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongsToManyActivity()
    {
        return  $this->belongstoMany('App\Models\Activity', 'tweet_activity', 'tweet_id', 'activity_id');
    }

    /**
     * 查找已订阅的人的动态，按时间倒叙查找
     *
     * @param $query
     * @param $subscriptions
     * @param $friends
     * @param $id
     * @param $date
     * @return mixed
     */
    public function scopeOfSubscriptions($query, $subscriptions, $friends, $id, $date)
    {
        return $query->where(function ($q) use ($subscriptions, $friends, $id) {

            //查询已订阅的人发布的类型为0(公开)的动态
            $q->where(function ($q) use ($subscriptions){
                $q->whereIn('user_id',$subscriptions)
                    ->where('visible',0);
                })

                //查询好友动态
                ->orWhere(function ($q) use ($friends, $id) {
                    $q->whereIn('user_id',$friends)
                        ->where(function ($q) use ($id) {
                            $q->where(function ($q) use ($id){           //查询好友发布的类型为0(公开),1(好友圈公开),2(好友圈私密)的动态
                                $q->whereIn('visible',[0,1,2]);
                            })
                                ->orWhere(function ($q) use ($id) {
                                    $q->where('visible',4)                      //查询好友发布的类型为4(好友圈部分可见)的动态
                                    ->where('visible_range','like','%' . $id . '%');
                                })
                                ->orWhere(function ($q) use ($id) {
                                    $q->where('visible',5)                      //查询好友发布的类型为5(好友圈部分不可见)的动态
                                    ->where('visible_range','not like','%' . $id . '%');
                                });
                        });
                })

                // 查询自己的动态
                ->orWhere('user_id',$id);
        })->where('created_at','<',$date);
    }

    /**
     * 查找已订阅的人的动态，按id倒叙查找 scopeOfSubscriptions() 的升级版，上一个还在使用，后期再彻底更换
     *
     * @param $query
     * @param $subscriptions 关注者的ids
     * @param $friends  好友ids
     * @param $id   用户本人的id
     * @return mixed
     */
    public function scopeOfAttention($query, $subscriptions, $friends, $id)
    {
        return $query->where(function ($q) use ($subscriptions, $friends, $id) {

            //查询已订阅的人发布的类型为0(公开)的动态
            $q->where(function ($q) use ($subscriptions){
                $q->whereIn('user_id',$subscriptions)
                    ->where('visible',0);
            })

                //查询好友动态
                ->orWhere(function ($q) use ($friends, $id) {
                    $q->whereIn('user_id',$friends)
                        ->where(function ($q) use ($id) {
                            $q->where(function ($q) use ($id){           //查询好友发布的类型为0(公开),1(好友圈公开),2(好友圈私密)的动态
                                $q->whereIn('visible',[0,1,2]);
                            })
                                ->orWhere(function ($q) use ($id) {
                                    $q->where('visible',4)                      //查询好友发布的类型为4(好友圈部分可见)的动态
                                    ->where('visible_range','like','%' . $id . '%');
                                })
                                ->orWhere(function ($q) use ($id) {
                                    $q->where('visible',5)                      //查询好友发布的类型为5(好友圈部分不可见)的动态
                                    ->where('visible_range','not like','%' . $id . '%');
                                });
                        });
                })

                // 查询自己的动态
                ->orWhere('user_id',$id);
        });
    }

    /**
     * 查询可以查看的好友的动态
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeOfFriendTweets($query, $id)
    {
        return $query->where(function ($q) use ($id){
                $q
                -> where(function ($q) use ($id){           //查询好友发布的类型为0(公开),1(好友圈公开),2(好友圈私密)的动态
                    $q->whereIn('visible',[0,1,2]);
                })
                -> orWhere(function ($q) use ($id) {
                    $q->where('visible',4)                      //查询好友发布的类型为4(好友圈部分可见)的动态
                    ->where('visible_range','like','%' . $id . '%');
                })
                -> orWhere(function ($q) use ($id) {
                    $q->where('visible',5)                      //查询好友发布的类型为5(好友圈部分不可见)的动态
                    ->where('visible_range','not like','%' . $id . '%');
                });
        });
    }

    /**
     * 查询该用户是否有权查看该动态   TODO 需要先判断是否为好友关系
     * @param $query
     * @param $user_id
     * @return mixed
     */
    public function scopeOfVisibleTweets($query, $user_id)
    {
        return $query->orWhere(function ($q) use ($user_id){
                    $q->where(function ($q) use ($user_id){           //查询好友发布的类型为0(公开),1(好友圈公开),2(好友圈私密)的动态
                        $q->whereIn('visible',[0,1,2]);
                    })
                        ->orWhere(function ($q) use ($user_id) {
                            $q->where('visible',4)                      //查询好友发布的类型为4(好友圈部分可见)的动态
                            ->where('visible_range','like','%' . $user_id . '%');
                        })
                        ->orWhere(function ($q) use ($user_id) {
                            $q->where('visible',5)                      //查询好友发布的类型为5(好友圈部分不可见)的动态
                            ->where('visible_range','not like','%' . $user_id . '%');
                        });
        });
    }

    /**
     * 查看审批通过动态
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * 查看待审批动态
     * @param $query
     * @return mixed
     */
    public function scopeWait($query)
    {
        return $query->where('active', 0);
    }

    /**
     * 查询的动态类型，置顶、推荐、普通动态、带黑名单的动态
     *
     * @param $query
     * @param string $type 查询类型 置顶、推荐、普通动态、带黑名单的动态
     * @param array $blacklist  屏蔽黑名单的动态
     * @param int $page 页码
     * @param int $paginate 每一页数量
     * @return mixed
     */
    public function scopeOfFilter($query, $type, $blacklist=[], $page=1, $paginate=20)
    {
        $now = Carbon::now()->toDateTimeString();

        switch($type) {
            case 'top':
                return $query->where('top_expires', '>', $now);
                break;
            case 'recommend':
                return $query->where('recommend_expires', '>', $now);
                break;
            case 'ordinary':
                return $query->forPage($page, $paginate);
                break;
            case 'blacklist':
                return $query->whereNotIn('user_id', $blacklist)->forPage($page, $paginate);
                break;

        }
    }

    /**
     * 查看非屏蔽动态 TweetLikeController和TweetReplyController已改用Common able()
     * @param $query
     * @return mixed
     */
    public function scopeAllow($query)
    {
        return $query->where('active', 1)->orWhere('active', 0);
    }

    /**
     * 查看可看动态
     * @param $query
     * @return mixed
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', 0);
    }

    /**
     * 查询指定
     * @param $query
     * @return mixed
     */
    public function scopeTop($query)
    {
        $now = Carbon::now()->toDateTimeString();
        return $query->where('active', 1)
                     ->where('top_expires', '>', $now);
    }

    /**
     * 查询用户中心的置顶动态
     * @param $query
     * @return mixed
     */
    public function scopeOfUserTop($query)
    {
        return $query->where('user_top', 1);
    }

    /**
     * 查询推荐
     * @param $query
     * @return mixed
     */
    public function scopeRecommend($query)
    {
        $now = Carbon::now()->toDateTimeString();
        return $query->where('active', 1)
            ->where('recommend_expires', '>', $now);
    }

    /**
     * 按日期查看 刷新与加载 暂时停用
     * @param $query
     * @param $style 1为刷新 2为加载
     * @param $date
     * @return mixed
     */
    public function scopeOfFlushDate($query, $style, $date)
    {
        // 刷新，大于某一时间
        if($style == 1) return $query->where('created_at', '>', $date);

        // 加载，小于某一时间
        return $query->where('created_at', '<', $date);
    }

    /**
     * 按日期查看 加载，小于某一时间
     * @param $query
     * @param $date
     * @return mixed
     */
    public function scopeOfDate($query, $date)
    {
        return $query->where('active', 1)->where('created_at', '<', $date);
    }

    /** 旧版
     * 按频道查询
     * @param $query
     * @param $date
     * @param $id
     * @return mixed
     */
    public function scopeOfChannel($query, $date, $id)
    {
        return $query->where('active', 1)->whereHas('belongsToManyChannel',function ($q) use ($id){
            $q->where('channel_id', $id)->active();
        })->where('created_at', '<', $date);
    }

    /** 新版
     * 按频道查询
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeOfFlushChannel($query, $id)
    {
        return $query->whereHas('belongsToManyChannel',function ($q) use ($id){
            $q->where('channel_id', $id);
        });
    }

    /**
     * 按类型查询
     * @param $query
     * @param $type
     * @return mixed
     */
    public function scopeOfType($query, $type)
    {
        switch ($type) {
            case 'video':
                return $query->whereNull('original')->where('type',0);
                break;
            case 'photo':
                return $query->whereNull('original')->where('type',1);
                break;
            case 'retweet':
                return $query->whereNotNull('original');
                break;
//            case 'topic':
//                return $query->has('belongsToManyTopic');
//                break;
//            case 'activity':
//                return $query->has('belongsToManyActivity');
//                break;
            default:
                return $query;
                break;
        }
    }
//    public function scopeOfType($query, $type)
//    {
//        switch ($type) {
//            case 'original':
//                return $query->whereNull('original');
//                break;
//            case 'retweet':
//                return $query->whereNotNull('original');
//                break;
//            case 'topic':
//                return $query->has('belongsToManyTopic');
//                break;
//            case 'activity':
//                return $query->has('belongsToManyActivity');
//                break;
//            default:
//                return $query->whereNull('original');
//                break;
//        }
//    }

    /**
     * 按名称查询
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfName($query, $name)
    {
        // binary 二进制格式
        return $query->where('content', 'LIKE BINARY', $name);
    }

    /**
     * 模糊搜索 非精确
     * @param $query
     * @param $name
     * @return mixed
     */
//    public function scopeOfSearch($query, $name)
//    {
//        return $query->where('content', 'LIKE BINARY', '%' . $name . '%')
//            ->where('content', '!=', $name);
//    }

    /**
     * 模糊搜索 新 因内容已单独拆分成表
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfSearch($query, $name)
    {
        if(empty($name)) return $query;

        return $query->whereHas('hasOneContent',function($q)use($name){

            $q->where('content', 'LIKE BINARY', '%' . $name . '%');
        });
    }

    // 官方的
    public function belongsToOfficial()
    {
        return $this->belongsTo('App\Models\Admin\Administrator', 'user_id', 'user_id');
    }

    /**
     * 搜索，新
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfNewSearch($query, $search_keywords, $search_type, $search_time, $search_duration, $search_browse,$operator)
    {
        // 搜索关键词
        if($search_keywords) {

            $query->whereHas('hasOneContent',function($q)use($search_keywords){

                $q->where('content', 'LIKE BINARY', '%' . $search_keywords . '%');
            });
        }

        // 类型
        if($search_type) {

//            switch($search_type){
//                case 1:
//                    $query->has('hasManyActivityTweet');
//                    break;
//                case 2:
//                    $query -> has('belongsToOfficial');
//                    break;
//                case 3:
//                    $query -> whereHas('belongsToUser', function($q){
//                        $q -> where('verify','<>',0);
//                    });
//                    break;
//            }
            $query->whereHas('belongsToManyChannel',function ($q) use($search_type){
                $q->where('channel_id', $search_type);
            });
        }

        // 多久之内的
        if($search_time) {
            switch ($search_time){
                case 0:
                    $search_time = 0;
                    break;
                case 1:
                    $search_time = strtotime(date('Y-m-d',time()));
                    break;
                case 2:
                    $search_time = mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                    break;
                case 3:
                    $search_time = mktime(0,0,0,date('m'),1,date('Y'));
                    break;
                default:
                    $search_time = 0;
            }

            $query->where('created_at', '>=', date_format('Y-m-d H:i:s', $search_time));
        }

        // 搜索时长
        if($search_duration) {
            switch ($search_duration){
                case 0:
                    $search_duration = 0;
                    break;
                case 1:
                    $search_duration = 10*60;
                    break;
                case 2:
                    $search_duration = 30*60;
                    break;
                case 3:
                    $search_duration = 60*60;
                    break;
                default:
                    $search_duration = 0;
            }


            $query->where('duration', '>=', $search_duration);
        }

        // 播放次数
        if($search_browse) {

            $query->where('browse_times', '>=', $search_browse);
        }

        //  操作员
        if($operator){
            $query->where('operator','=',$operator)->orWhereHas('hasOneTop',function ($q) use($operator){
               $q->where('toper_id','=',$operator)->orWhere('recommender_id','=',$operator);
            });
        }

        return $query;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 与管理员表多对一关系   审核通过人id
     */
    public function belongsToPass()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','pass_id','id');
    }

    /**
     * 按id 刷新与加载
     * @param $query
     * @param $style 1为刷新 2为加载
     * @param $last_id
     * @return mixed
     */
    public function scopeOfNearbyDate($query, $style, $last_id)
    {
        if($last_id){

            // 刷新，大于某一id
            if($style == 1) return $query->where('id', '>', $last_id);

            // 加载，小于某一id
            return $query->where('id', '<', $last_id);
        }else{

            return $query;
        }
    }

    /**
     * @param $query
     * @param $user
     * @param $id
     */
    public function scopeOfVs($query,$user,$id =null)
    {
        if(!$id){
            return $query;
        }

     $ids = [];
     foreach ($id->toArray() as $k=>$v){
            $ids[] = $v['belongs_to_user']['id'];
     }





    }







    /**
     * 动态与片段一对一
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneFragment()
    {
        return $this -> hasOne('App\Models\Fragment','id','fragment_id');
    }

    public function belongsToManyKeywords()
    {
        return $this->belongsToMany('App\Models\keywords','keywords_tweet','tweet_id','keyword_id');
    }


}