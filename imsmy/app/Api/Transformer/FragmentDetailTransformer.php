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

    private  $fragmentTypeTransformer;

    private  $storyboardTransform;

    private $subtitleTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        FragmentTypeTransformer $fragmentTypeTransformer,
        StoryboardTransformer $storyboardTransformer,
        SubtitleTransformer $subtitleTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
        $this->fragmentTypeTransformer = $fragmentTypeTransformer;
        $this ->storyboardTransform = $storyboardTransformer;
        $this ->subtitleTransformer = $subtitleTransformer;
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
            'duration'      => changeTimeType($data->duration),
            'net_address'   => $data->net_address,
            'cover'         => $data->cover,
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
            'type'          =>  $this->fragmentTypeTransformer->transformCollection($data->belongsToManyFragmentType->toArray()),
            //'subtitle'      =>  $this->subtransform($data->hasManySubtitle->toArray()),
        ];
    }

    /**
     * 开拍或下载过使用
     * @param $data
     * @return array
     */
    public function usetransform($data)
    {
        return [
            'id'            => $data->id,
            'user_id'       => $data->user_id,
            'aspect_radio'  => $data->aspect_radio,
            'duration'      => $data->duration,
            'net_address'   => $data->net_address,
            'cover'         => $data->cover,
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
            'user'          => $this->usersTransformer->transform( $data->belongsToUser),
            'type'          => $this->fragmentTypeTransformer->transformCollection($data->belongsToManyFragmentType->toArray()),
            'storyboard'    => $this -> storyboardTransform ->transformCollection($data->hasManyStoryboard->toArray()),
            'subtitle'      => $this -> subtitleTransformer ->transformCollection($data->hasManySubtitle->toArray()),
        ];
    }

    /**
     * 预览使用
     * @param $data
     * @return array
     */
    public function fragtransform($data)
    {
        return [
            'id'            => $data->id,
            'user_id'       => $data->user_id,
            'aspect_radio'  => $data->aspect_radio,
            'duration'      => $data->duration,
            'net_address'   => $data->net_address,
            'cover'         => $data->cover,
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
            'user'          => $this->usersTransformer->transform( $data->belongsToUser),
            'type'          =>  $this->fragmentTypeTransformer->transformCollection($data->belongsToManyFragmentType->toArray()),
            //'subtitle'      =>  $this->subtransform($data->hasManySubtitle->toArray()),
        ];
    }
}