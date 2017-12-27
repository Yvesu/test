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
use JWTAuth;

class AuthTransformer extends Transformer
{
    public function transform($auth)
    {
        if ($old_token = Cache::get($auth->id.'token')){
            JWTAuth::invalidate($old_token);
            Cache::forget($auth->id.'token');
            Cache::forever($auth->id.'token',$auth->token);
        }else{
            Cache::forever($auth->id.'token',$auth->token);
        }

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