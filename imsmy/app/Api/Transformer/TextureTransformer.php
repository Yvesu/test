<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/26 0026
 * Time: 下午 17:08
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class TextureTransformer  extends Transformer
{
    private $userTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer
    )
    {
        $this -> userTransformer = $usersTransformer;
    }

    public  function transform($item)
    {
        return [
            'id'                =>  $item->id,
            'name'              =>  $item->name,
            'content'           =>  CloudStorage::downloadUrl($item->content),
            'download_count'    =>  $item->download_count,
            'folder'            =>  $item->belongsToFolder,
            'user'              =>  $this->userTransformer->transform($item->belongsToUser),
        ];
    }
}