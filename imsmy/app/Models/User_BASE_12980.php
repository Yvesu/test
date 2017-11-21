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
    ];

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


    public function belongsToManyFragment()
    {
        return $this->belongsToMany('App\Models\Fragment','fragment_user_collect','user_id','fragment_id');
    }

}
