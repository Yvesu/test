<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Common implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected  $table = 'user';

    /**
     * @var array
     */
    protected $fillable = [
        'nickname',
        'avatar',
        'hash_avatar',
        'video_avatar',
        'sex',
        'cover',
        'verify',
        'verify_info',
        'level',
        'honor',
        'signature',
        'background',
        'location',
        'location_id',
        'nearby_id',
        'birthday',
        'phone_model',
        'phone_serial',
        'phone_sdk_int',
        'umeng_device_token',
        'xmpp',
        'advertisement',
        'status',
        'stranger_comment',
        'stranger_at',
        'stranger_private_letter',
        'location_recommend',
        'search_phone',
        'new_message_comment',
        'new_message_fans',
        'new_message_like',
        'fans_count',
        'new_fans_count',
        'follow_count',
        'work_count',
        'retweet_count',
        'trophy_count',
        'collection_count',
        'like_count',
        'topics_count',
        'browse_times',
        'last_ip',
        'last_token',
        'is_vip',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与登录日志表一对多关系，目前登录日志表仅有登录时间
     */
    public function loginTime()
    {
        return $this->hasMany('App\Models\User\UserLoginLog','user_id','id');
    }

    /**
     * 返回LocalAuth密码
     * @return mixed
     */
    public function getAuthPassword()
    {
        $auth = LocalAuth::where('user_id',$this->id)->firstOrFail();

        return $auth->password;
    }

    /**
     * 用户与本地认证 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneLocalAuth()
    {
        return $this->hasOne('App\Models\LocalAuth','user_id','id');
    }

    /**
     * 用户统计表 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneStatistics()
    {
        return $this->hasOne('App\Models\StatisticsUsers','user_id','id');
    }

    /**
     * 用户与金币账户 一对一关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOneGoldAccount()
    {
        return $this->hasOne('App\Models\GoldAccount','user_id','id');
    }

    /**
     * 用户与OAuth 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyOAuth()
    {
        return $this->hasMany('App\Models\OAuth','user_id','id');
    }

    /**
     * 用户与被订阅 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManySubscriptions()
    {
        return $this->hasMany('App\Models\Subscription','to','id');
    }

    /**
     * 用户与订阅 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManySubscriptionsFrom()
    {
        return $this->hasMany('App\Models\Subscription','from','id');
    }

    /**
     * 用户与动态 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTweet()
    {
        return $this->hasMany('App\Models\Tweet','user_id','id');
    }

    /**
     * 用户与动态点赞 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTweetLike()
    {
        return $this->hasMany('App\Models\TweetLike','user_id','id');
    }

    /**
     * 用户与动态 一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyTopic()
    {
        return $this->hasMany('App\Models\Topic','user_id','id');
    }

    /**
     * 按名称查询
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfName($query, $name)
    {
        // binary 二进制格式
        return $query->where('nickname', 'LIKE BINARY', $name);
    }

    /**
     * 认证用户
     * @param $query
     * @return mixed
     */
    public function scopeOfVerify($query)
    {
        return $query->whereIn('verify', [1,2]);
    }

    /**
     * 模糊搜索
     * @param $query
     * @param $name
     * @return mixed
     */
    public function scopeOfSearch($query, $name)
    {
        return $query->where('nickname', 'LIKE BINARY', '%' . $name . '%')
            ->where('nickname', '!=', $name);
    }

    /**
     * 查询非拉黑用户
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query->where('status', 0);
    }

    /**
     * 时间
     * @param $query
     * @param $date
     * @return mixed
     */
    public function scopeOfDate($query,$date)
    {
        return $query->where('created_at', '<', $date);
    }

    /**
     * 查询时排除自己
     * @param $query
     * @param $user
     * @return mixed
     */
    public function scopeOfRemoveSelf($query, $user)
    {
        // 判断是否为登录状态
        if($user) return $query -> whereNotIn('id',[$user->id]);

        return $query;
    }

    /**
     * 查询时排除黑名单
     * @param $query
     * @param $black
     * @return mixed
     */
    public function scopeOfRemoveBlack($query, $black)
    {
        // 判断是否为登录状态
        if(!empty($black)) return $query -> whereNotIn('id',$black);

        return $query;
    }

    /**
     *  用户与片段
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function hasManyFragment()
    {
        return $this->hasMany('App\Models\Fragment','user_id','fragment_id');
    }

    /**
     * 用户与积分
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyIntegral()
    {
        return $this->hasOne('App\Models\User\UserIntegral','user_id','id');
    }

    /**
     * 用户与积分支出
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hasManyIntegralExtend()
    {
        return $this->hasMany('App\Models\User\UserIntegralExpend','user_id','id');
    }

    /**
     * 用户与关键词 多对多
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function belongtoManyKeywords()
    {
        return $this->belongsToMany('App\Models\UserKeywords','user_keywords','user_id','keyword_id');
    }

    /**
     * [hasManyFriends description]
     * @return boolean [description]
     */
    public function hasManyFriends()
    {
        return $this->hasMany('App\Models\Friend', 'to', 'id');
    }


    /**
     * @param $query
     * @param $userType
     * @return mixed
     * 查询用户种类
     */
    public function scopeUserType($query,$userType)
    {
        if($userType == null)
        {
            return $query;
        }elseif ($userType == 2){
            return $query->where('is_thirdparty','=',1)->where('is_phonenumber','=',0);
        }elseif($userType == 3){
            return $query->where('is_phonenumber','=',1)->where('verify','=',2);
        }elseif($userType == 0){
            return $query->where('is_vip','=',0)->where('is_phonenumber','=',1);
        }else{
            return $query->where('is_vip','>',0)->where('verify','<>',2);
        }
    }


    /**
     * @param $query
     * @param $vipLevel
     * @return mixed
     * 搜索vip等级
     */
    public function scopeVipLevel($query,$vipLevel)
    {
        if($vipLevel == null)
        {
            return $query;
        }elseif($vipLevel == 101){
            return $query->where('is_vip','>',100);
        }else{
            return $query->where('is_vip','=',$vipLevel);
        }
    }


    /**
     * @param $query
     * @param $fans
     * @return mixed
     * 查询粉丝调节
     */
    public function scopeFans($query,$fans)
    {
        return $query->where('fans_count','>=',$fans);
    }

    /**
     * @param $query
     * @param $playNum
     * @return mixed
     * 查询播放量
     */
    public function scopePlayCount($query,$playCount)
    {
        return $query->where('browse_times','>=',$playCount);
    }


    /**
     * @param $query
     * @param $productionNum
     * @return mixed
     * 查询作品数
     */
    public function scopeProductionNum($query,$productionNum)
    {
        return $query->where('work_count','>=',$productionNum);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 认证用户与管理员用户审核人对应   反向1对多
     */
    public function verifyCheck()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','verify_checker','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 与特权用户表关系  一对多
     */
    public function privilegeUser()
    {
        return $this->hasMany('App\Models\PrivilegeUser','user_id','id');
    }

    /**
     * @param $query
     * @param $checker
     * @return mixed
     * 审核人搜索
     */
    public function scopeChecker($query,$checker)
    {
        if(is_null($checker))
        {
            return $query;
        }else{
            return $query->where('verify_checker','=',$checker);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 精选用户与管理员用户审核人对应   反向1对多
     */
    public function choiceness()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','choiceness_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 冻结用户与管理员用户审核人对应   反向1对多
     */
    public function stop()
    {
        return $this->belongsTo('App\Models\Admin\Administrator','stop_id','id');
    }

    /**
     * @param $query
     * @param $checker
     * @return mixed
     * 推选人搜索
     */
    public function scopeChoiceness($query,$checker)
    {
        if(is_null($checker))
        {
            return $query;
        }else{
            return $query->where('choiceness_id','=',$checker);
        }
    }

    /**
     * @param $query
     * @param $checker
     * @return mixed
     * 冻结人搜索
     */
    public function scopeStop($query,$checker)
    {
        if(is_null($checker))
        {
            return $query;
        }else{
            return $query->where('stop_id','=',$checker);
        }
    }

}
