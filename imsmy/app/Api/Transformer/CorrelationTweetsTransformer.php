<?php
namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class CorrelationTweetsTransformer extends Transformer
{
    private $usersTransformer;
    public function __construct
    (
        UsersTransformer $usersTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
    }


    public function transform($item)
    {
        return [
            'id'                    =>      $item['id'],
            'type'                  =>      $item['type'],
            'browse_times'          =>      $item['browse_times'] ,
            'video'                 =>      CloudStorage::downloadUrl(  $item['video'] ),
            'photo'                 =>      $item['photo'] === null ? [] : CloudStorage::downloadUrl(json_decode($item['photo'],true)),
            'content'               =>      $item['has_one_content']['content'],
            'screen_shot'           =>      CloudStorage::downloadUrl($item['screen_shot']),
            'created_at'            =>      strtotime( $item['created_at'] ),
            'user'                  =>      $this -> usersTransformer->tweettransformer($item['belongs_to_user']),
        ];
    }
}



