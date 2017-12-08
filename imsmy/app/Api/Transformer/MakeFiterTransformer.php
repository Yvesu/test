<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/1 0001
 * Time: 下午 15:14
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class MakeFiterTransformer extends Transformer
{
    private $userTransformer;

    private $floderTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer,
        FloderTransformer $floderTransformer
    )
    {
        $this ->userTransformer = $usersTransformer;

        $this -> floderTransformer = $floderTransformer;
    }


    public  function transform($item)
    {
        return [
            'id'            =>  $item['id'],
            'user_id'       => $item['user_id'],
            'name'          => $item['name'],
            'cover'         => CloudStorage::publicImage( $item['cover'] ),
            'content'       => CloudStorage::privateUrl_zip( $item['content'] ),
            'count'         => $item['count'],
            'integral'      => $item['integral'],
            'texture'       => CloudStorage::downloadUrl( $item['texture']),
            'texture_mix_type_id'   => $item['texture_mix_type_id'],
            'time_add'      => $item['time_add'],
            'user'          => $this ->userTransformer->filetransformer( $item['belongs_to_user']),
            'folder'        => $this->floderTransformer->transformCollection($item['belongs_to_many_folder']),
        ];
    }
}