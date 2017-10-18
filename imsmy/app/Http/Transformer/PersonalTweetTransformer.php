<?php

namespace App\Http\Transformer;

use CloudStorage;

class PersonalTweetTransformer extends Transformer
{
    public function transform($data)
    {
        return [
            'id'           =>  $data->id,
            'browse_times' =>  '播放：'.$data->browse_times.'次',
            'screen_shot'  =>  CloudStorage::downloadUrl($data->screen_shot),
            'video'        =>  CloudStorage::downloadUrl($data->video),
            // 计算分数平均值
            'grade'        =>  number_format($data -> tweet_grade_total/($data -> tweet_grade_times ?: 1), 1),
            'content'      =>  $data->hasOneContent -> content,
            'nickname'     =>  isset($data->belongsToUser) ? $data->belongsToUser -> nickname : '',
            'created_at'   =>  date_format($data->created_at,'Y/m/d H:i'),
        ];
    }
}


