<?php

namespace App\Api\Transformer;


class HotSearchTransformer extends Transformer
{

    public function transform($hotWords)
    {
        return [
            'id'            =>  $hotWords -> id,
            'hot_word'      =>  $hotWords -> hot_word,
        ];
    }
}