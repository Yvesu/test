<?php

namespace App\Http\Transformer;

use CloudStorage;

class TrophyLogTransformer extends Transformer
{
    public function transform($data)
    {
        return [
            'id'           =>  $data -> belongsToUser ->id,
            'nickname'     =>  $data -> belongsToUser ->nickname,
            'verify'       =>  $data -> belongsToUser ->verify,
            'anonymity' => $data -> anonymity,   // 0为公开，1为匿名颁奖
        ];
    }
}