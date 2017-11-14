<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/13
 * Time: 17:18
 */

namespace App\Api\Transformer;


class TagsTransformer extends Transformer
{
    protected $tagMembersTransformer;

    public function __construct(TagMembersTransformer $tagMembersTransformer)
    {
        $this->tagMembersTransformer = $tagMembersTransformer;
    }

    public function transform($tag)
    {
        return [
            'id'            =>  $tag->id,
            'name'          =>  $tag->name,
            'users'         =>  $this->tagMembersTransformer->transformCollection($tag->hasManyTagMembers->all()),
            'updated_at'    =>  strtotime($tag->updated_at)
        ];
    }
}