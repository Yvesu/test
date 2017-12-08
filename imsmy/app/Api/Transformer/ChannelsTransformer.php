<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/27
 * Time: 9:45
 */

namespace App\Api\Transformer;

use CloudStorage;
class ChannelsTransformer extends Transformer
{
    public function transform($channel)
    {
        return [
            'id'         => $channel->id,
            'name'       => $channel->name,
            'ename'      => $channel->ename,
            'icon'       => CloudStorage::downloadUrl($channel->icon),
        ];
    }
}