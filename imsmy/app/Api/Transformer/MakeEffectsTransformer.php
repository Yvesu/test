<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22 0022
 * Time: 下午 16:22
 */

namespace App\Api\Transformer;


use App\Facades\CloudStorage;
use App\Models\MixType;

class MakeEffectsTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
    }

    public  function transform($item)
    {
        return [
            'file_id'           =>  $item -> id,
            'name'              =>  $item   ->  name,
            'address'           =>  CloudStorage::downloadUrl($item   ->  address),
            'high_address'      =>  $item   ->  high_address ? CloudStorage::downloadUrl( $item   ->  high_address) : '',
            'super_address'     =>  $item   ->  super_address ? CloudStorage::downloadUrl($item   ->  super_address) : '',
            'preview_address'   =>  CloudStorage::downloadUrl( $item ->preview_address),
            'cover'             =>  CloudStorage::downloadUrl( $item->cover),
            'duration'          =>  $item->duration,
            'size'              =>  $item->size,
            'integral'          =>  $item->integral,
            'count'             =>  $item->count,
            'time_add'          =>  $item->time_add,
            'vipfree'           =>  $item->vipfree,
            'isalpha'           =>  $item->isalpha,
            'mix_texture_id'    =>  is_null($item->mix_texture_id) ? -1 : $item->mix_texture_id,
            'shade'             =>  $item->shade ? CloudStorage::downloadUrl($item->shade) : '' ,
            'mix_type_id'       =>  is_null($item->mix_type_id) ? -1 : MixType::find($item->mix_type_id)->code,
            'belongs_to_user'   =>  $this->usersTransformer->transform($item->belongsToUser),
        ];
    }
}