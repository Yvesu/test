<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2 0002
 * Time: 下午 16:01
 */
namespace App\Api\Transformer;

use App\Facades\CloudStorage;
class FragCollectTransformer extends Transformer
{
    public function transform($data)
    {
        $a = [];
        foreach ($data as $v){
            $a[] = [
                'fragment_id' =>$v['id'],
                'user_id'=>$v['user_id'],
                'name'=>$v['name'],
                'duration' =>$v['duration'],
                'cover' =>CloudStorage::downloadUrl($v['cover']),
                'label'=>$v['key_word'],
                'type'=>$v['belongs_to_many_fragment_type']
            ];
        }

       return $a;
    }


    public function ptransform($data)
    {
        $a = [];

        foreach ($data as $v){
            $a[] = [
                'fragment_id' =>$v['id'],
                'user_id'=>$v['user_id'],
                'name'=>$v['name'],
                'duration' =>$v['duration'],
                'cover' =>CloudStorage::downloadUrl($v['cover']),
                'down_count'=>$v['count'],
                'label'=>$v['key_word']
            ];
        }

        return $a;
    }
}