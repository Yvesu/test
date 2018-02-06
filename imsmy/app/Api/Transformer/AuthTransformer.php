<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/17
 * Time: 15:56
 */

namespace App\Api\Transformer;

use App\Models\UserToken;
use Config;
use CloudStorage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use JWTAuth;

class AuthTransformer extends Transformer
{
    public function transform($auth)
    {
        //缓存的方法
//        if ($old_token = Cache::get($auth->id.'token')){
//            JWTAuth::invalidate($old_token);
//            Cache::forget($auth->id.'token');
//            Cache::forever($auth->id.'token',$auth->token);
//        }else{
//            Cache::forever($auth->id.'token',$auth->token);
//        }

        //数据库的方法
//        $token = UserToken::where('user_id',1000437)->orderBy('create_time','asc')->get();
//
//        $count = $token->count();
//
//        if ( $count >= 2 ){
//            $old_token = UserToken::where('user_id',1000437)->orderBy('create_time','asc')->first();
//                JWTAuth::invalidate($old_token->token);
//            UserToken::where('create_time',$old_token->create_time)->delete();
//        }

        //redis方法
        $key ='STRING:TOKEN:'.$auth->id;

        if ($old_token = Redis::get($key)){
            JWTAuth::invalidate($old_token);
            Redis::del($key);
        }
        Redis::setex($key,60*1440 ,$auth->token);

        return [
            'id'           => (string)$auth->id,
            'nickname'     => (string)$auth->nickname,
            'token_expire' => Config::get('jwt.ttl') * 60,
            'avatar'       => $auth->avatar ? CloudStorage::downloadUrl($auth->avatar) : '',
//            'hash_avatar'  => $auth->hash_avatar,
            'verify'       => $auth->verify,
            'signature'    => $auth->signature,
            'token'        => $auth->token,
            'attention'    => $auth->attention,
        ];
    }
}