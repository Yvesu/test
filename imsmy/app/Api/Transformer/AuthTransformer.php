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
use JWTAuth;

class AuthTransformer extends Transformer
{
    public function transform($auth)
    {
        $is_exit =UserToken::where('user_id',$auth->id)->get();

        if ($is_exit->count()>1){
            $old = UserToken::where('user_id',$auth->id)->orderBy('create_time','asc')->first();
            JWTAuth::invalidate($old->token);
            UserToken::where('token',$old->token)->delete();
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