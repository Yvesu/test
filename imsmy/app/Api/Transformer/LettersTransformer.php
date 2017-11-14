<?php

namespace App\Api\Transformer;

use CloudStorage;
use Auth;

class LettersTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
    }

    public function transform($letter)
    {
        return [
            'id'            =>  $letter -> id,
            'content'       =>  $letter -> content,
            'from'          =>  $letter -> from,
            'to'            =>  $letter -> to,
            'created_at'    =>  strtotime($letter -> created_at),
            'user'          =>  $this -> usersTransformer->transform($letter->belongsToUser)
        ];
    }
}