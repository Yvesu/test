<?php

namespace App\Api\Transformer;

use App\Models\FragmentType;
use App\Models\TweetReply;
use CloudStorage;
use Auth;
class FragmentTransformer extends Transformer
{
  
    public function transform($fragment)
    {
        return [
            'id'            =>  $fragment->id,
            'name'          =>  $fragment->name,
            'icon'		    =>  $fragment->icon
        ];
    }
}