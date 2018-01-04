<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/2 0002
 * Time: 下午 16:01
 */
namespace App\Api\Transformer;

use App\Facades\CloudStorage;
class FragCollectTransformer extends Transformer
{
    private $storyboardTransform;

    private $fragmentTypeTransformer;

    private  $userTransformer;

    private $subtitleTransformer;

    public function __construct(
        StoryboardTransformer $storyboardTransformer,
        FragmentTypeTransformer $fragmentTypeTransformer,
        UsersTransformer $usersTransformer,
        SubtitleTransformer $subtitleTransformer
    )
    {
        $this->storyboardTransform = $storyboardTransformer;
        $this->fragmentTypeTransformer = $fragmentTypeTransformer;
        $this->userTransformer = $usersTransformer;
        $this ->subtitleTransformer = $subtitleTransformer;
    }

    public function transform($data)
    {
        $a = [];
        foreach ($data as $v){
            $a[] = [
                'fragment_id'   => $v['id'],
                'user_id'       => $v['user_id'],
                'name'          => $v['name'],
                'duration'      => changeTimeType($v['duration']),
                'cover'         => CloudStorage::publicImage($v['cover']),
                'url'           => CloudStorage::privateUrl($v['net_address']),
                'down_count'    => $v['count'],
                'watch_count'   => $v['watch_count'],
                'praise'        => $v['praise'],
                'country'       => $v['address_country'],
                'province'      => $v['address_province'],
                'city'          => $v['address_city'],
                'county'        => $v['address_county'],
                'street'        => $v['address_street'],
                'operator_id'   => $v['operator_id'],
                'storyboard_count' => $v['storyboard_count'],
                'create_time'   => $v['time_add'],
                'user'          => $this -> userTransformer -> fragtransform( $v['belongs_to_user'] ),
                'type'          => $this -> fragmentTypeTransformer->transformCollection($v['belongs_to_many_fragment_type']),
            ];
        }
         return $a;
    }


    public function ptransform($data)
    {
        $a = [];

        foreach ($data as $v){
            $a[] = [
                'fragment_id'   =>   $v['id'],
                'user_id'       =>   $v['user_id'],
                'name'          =>   $v['name'],
                'duration'      =>   changeTimeType($v['duration']),
                'cover'         =>   CloudStorage::publicImage($v['cover']),
                'url'           =>   CloudStorage::privateUrl($v['net_address']),
                'down_count'    =>   $v['count'],
                'watch_count'   =>   $v['watch_count'],
                'create_time'   =>   $v['time_add'],
                'user'          =>   $this -> userTransformer -> fragtransform($v['belongs_to_many_user'][0]),
                'type'          =>   $this->fragmentTypeTransformer->transformCollection($v['belongs_to_many_fragment_type']),
                'storyboard'    =>   $this->storyboardTransform->transformCollection( $v['has_many_storyboard'] ),
            ];
        }

        return $a;
    }

    /**
     * 下载的片段
     * @param $data
     * @return array
     */
    public function downtransform($data)
    {
        $a = [];
        foreach ($data as $v){
            $a[] = [
                'fragment_id'   => $v['id'],
                'user_id'       => $v['user_id'],
                'name'          => $v['name'],
                'duration'      => changeTimeType($v['duration']),
                'cover'         => CloudStorage::publicImage($v['cover']),
                'url'           => CloudStorage::privateUrl($v['net_address']),
                'down_count'    => $v['count'],
                'watch_count'   => $v['watch_count'],
                'create_time'   => $v['time_add'],
                'user'          => $this -> userTransformer -> fragtransform($v['belongs_to_user']),
                'type'          => $this -> fragmentTypeTransformer -> transformCollection($v['belongs_to_many_fragment_type']),
                'storyboard'    => $this -> storyboardTransform -> transformCollection( $v['has_many_storyboard']),
                'subtitle'      => $this -> subtitleTransformer -> transformCollection( $v['has_many_subtitle'])
            ];
        }

        return $a;
    }

    public function collecttransform($data)
    {
        $a = [];
        foreach ($data as $v){
            $a[] = [
                'fragment_id'   => $v['id'],
                'user_id'       => $v['user_id'],
                'name'          => $v['name'],
                'duration'      => changeTimeType($v['duration']),
                'cover'         => CloudStorage::publicImage($v['cover']),
                'url'           => CloudStorage::privateUrl($v['net_address']),
                'down_count'    => $v['count'],
                'watch_count'   => $v['watch_count'],
                'create_time'   => $v['time_add'],
                'user'          => $this -> userTransformer -> fragtransform($v['belongs_to_user']),
                'type'          => $this -> fragmentTypeTransformer->transformCollection($v['belongs_to_many_fragment_type']),
            ];
        }
        return $a;
    }

    /**
     * @param $data
     * @return array
     */
    public function searchtransform($data)
    {
        $a = [];
        foreach ($data as $v){
            $a[] = [
                'fragment_id'   => $v['id'],
                'user_id'       => $v['user_id'],
                'name'          => $v['name'],
                'duration'      => $v['duration'],
                'cover'         => CloudStorage::publicImage($v['cover']),
                'url'           => CloudStorage::privateUrl($v['net_address']),
                'down_count'    => $v['count'],
                'watch_count'   => $v['watch_count'],
                'create_time'   => $v['time_add'],
                'user'          => $this -> userTransformer -> fragtransform($v['belongs_to_user']),
                'type'          => $this -> fragmentTypeTransformer->transformCollection($v['belongs_to_many_fragment_type']),
            ];
        }
        return $a;
    }

}