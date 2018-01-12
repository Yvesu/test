<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11 0011
 * Time: 下午 19:47
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class FilmRecommendTransformer extends Transformer
{
    public  function transform($item)
    {
        return [
            'id'            =>   $item->id,
            'name'          =>   $item->name,
            'cover'         =>   CloudStorage::downloadUrl($item->cover),
            'url'           =>   $item->url,
            'users_count'   =>   $item->count,
            'expires'       =>   $item->time_end,
            'bonus'         =>   $item->cost,
        ];
    }

}