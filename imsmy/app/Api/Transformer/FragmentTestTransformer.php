<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/4 0004
 * Time: 下午 18:21
 */

namespace App\Api\Transformer;

use CloudStorage;

class FragmentTestTransformer extends Transformer
{
    private $usersTransformer;

    private  $fragmentTypeTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer,
        FragmentTypeTransformer $fragmentTypeTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
        $this -> fragmentTypeTransformer = $fragmentTypeTransformer;
    }

    public  function transform($item)
    {
        return [
            'id'                =>  $item['id'],
            'user_id'           =>  $item['user_id'],
            'aspect_radio'      =>  $item['aspect_radio'],
            'duration'          =>  $item['duration'],
            'net_address'       =>  CloudStorage::downloadUrl($item['net_address']),
            'cover'             =>  CloudStorage::downloadUrl($item['cover']),
            'name'              =>  $item['name'],
            'country'           =>  $item['address_country'],
            'province'          =>  $item['address_province'],
            'city'              =>  $item['address_city'],
            'county'            =>  $item['address_county'],
            'street'            =>  $item['address_street'],
            'intergral'         =>  $item['intergral'],
            'cost'              =>  $item['cost'],
            'time_add'          =>  $item['time_add'],
            'size'              =>  $item['size'],
            'storyboard_count'  =>  $item['storyboard_count'],
            'user'              =>  $this ->usersTransformer->fragtransform(  $item['belongs_to_user']),
            'type'              =>  $this->fragmentTypeTransformer->transformCollection( $item['belongs_to_many_fragment_type']),
        ];
    }
}