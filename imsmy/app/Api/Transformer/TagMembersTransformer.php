<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/13
 * Time: 17:22
 */

namespace App\Api\Transformer;


class TagMembersTransformer extends Transformer
{
    public function transform($tag_member)
    {
        return [
            'member_id'       =>  $tag_member->member_id
        ];
    }
}