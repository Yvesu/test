<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/6 0006
 * Time: 下午 16:36
 */
namespace App\Api\Transformer;

use App\Facades\CloudStorage;
use App\Api\Transformer\UsersTransformer;

class FragmentDetailTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    /**
     * @param $sub
     * @return array
     */
    public function subtransform($sub)
    {
        $a = [];
        foreach ($sub as $v){
            $a[] = [
                'content' =>$v['content'],
                'start_time' =>$v['start_time'],
                'end_time' =>$v['end_time'],
            ];
        }

        return $a;
    }

    public function transform($data)
    {
        return [
            'id'            => $data->id,
            'user_id'       => $data->user_id,
            'aspect_radio'  => $data->aspect_radio,
            'duration'      => $data->duration,
            'net_address'   => CloudStorage::downloadUrl($data->net_address),
            'cover'         => CloudStorage::downloadUrl($data->cover),
            'name'          => $data->name,
            'bgm'           => $data->bgm,
            'country'       => $data->address_country,
            'province'      => $data->address_province,
            'city'          => $data->address_city,
            'county'        => $data->address_county,
            'street'        => $data->address_street,
            'intergral'     => $data->intergral,
            'cost'          => $data->cost,
            'count'         => $data->count,
            'watch_count'   => $data->watch_count,
            'praise'        => $data->praise,
            'vip_isfree'    => (boolean)$data->vipfree,
            'user'          => $this->usersTransformer->transform( $data->belongsToManyUser[0]),
            'subtitle'      =>  $this->subtransform($data->hasManySubtitle->toArray()),
        ];
    }
}