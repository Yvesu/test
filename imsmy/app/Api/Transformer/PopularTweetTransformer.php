<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28 0028
 * Time: 上午 9:46
 */

namespace App\Api\Transformer;


class PopularTweetTransformer extends Transformer
{
    private $usersSearchTransformer;

    public function __construct
    (
       UsersSearchTransformer $usersSearchTransformer
    )
    {
        $this->usersSearchTransformer = $usersSearchTransformer;
    }

    public  function transform ($item)
    {

    }
}