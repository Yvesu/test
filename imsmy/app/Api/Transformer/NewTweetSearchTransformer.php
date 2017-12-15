<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/15 0015
 * Time: 下午 16:37
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;

class NewTweetSearchTransformer extends Transformer
{
    private $newTweetChannelTransformer;

    public function __construct
    (
        NewTweetChannelTransformer $newTweetChannelTransformer
    )
    {
        $this->newTweetChannelTransformer = $newTweetChannelTransformer;
    }


    public  function transform($item)
    {
        return [
            'id'            =>      $item['id'],
            'duration'      =>      $item['duration'],
            'screen_shot'   =>      CloudStorage::downloadUrl($item['screen_shot']),
            'browse_times'  =>      $item['browse_times'],
            'content'       =>      $item['has_one_content']['content'],
            'channel'       =>      $this->newTweetChannelTransformer->transformCollection($item['belongs_to_many_channel']),
        ];
    }
}