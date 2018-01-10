<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/6 0006
 * Time: 上午 9:59
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class ShadeTransformer extends Transformer
{
    protected $userTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer
    )
    {
        $this->userTransformer = $usersTransformer;
    }

    public  function transform($item)
    {
        return [
            'id'            =>  $item -> id ,
            'name'          =>  $item -> name,
            'video'         =>  CloudStorage::downloadUrl($item -> video),
            'image'         =>  CloudStorage::downloadUrl($item -> image),
            'user'          =>  $this->userTransformer -> transform($item -> belongToUser),
            'folder'        =>  $item -> belongToFolder  -> name,
            'integral'      =>  $item -> integral,
            'official'      =>  $item -> official,
            'down_count'    =>  $item -> down_count,
            'watch_count'   =>  $item -> watch_count,
            'size'          =>  $item -> size,
            'duration'      =>  $item -> duration,
            'vipfree'       =>  $item -> vipfree,
            'create_time'   =>  $item -> create_time,
        ];
    }
}