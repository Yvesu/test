<?php
namespace App\Api\Transformer;

use CloudStorage;
use Auth;

class MakeFileTransformer extends Transformer
{
    protected $userTransformer;

    public function __construct
    (
        UsersTransformer $usersTransformer
    )
    {
        $this->userTransformer = $usersTransformer;
    }

//
//    public function transform($data)
//    {
//        $user = Auth::guard('api') -> user();
//
//        return [
//            'id'        => $data -> id,
//            'name'      => $data -> name,
//            'integral'  => $data -> integral,
//            'cover'     => CloudStorage::downloadUrl($data -> cover),
//            'count'     => $data -> count,
//            'has_download' => $user ? (isset($data -> hasManyDownload) ? 0 : 1) : 0,
//        ];
//    }



    public function transform($data)
    {
        $user = Auth::guard('api') -> user();

        return [
            'id'        => $data -> id,
            'name'      => $data -> name,
            'integral'  => $data -> integral,
            'cover'     => CloudStorage::downloadUrl($data -> cover),
            'video'     => CloudStorage::downloadUrl($data->preview_address),
            'count'     => $data -> count,
            'has_download' => $user ? (isset($data -> hasManyDownload) ? 0 : 1) : 0,
            'time_add'  => $data -> time_add,
            'user'      => $this->userTransformer->fragtransform( $data->belongsToUser),
            'type'      => $data->belongsToFolder->name,
        ];
    }

    /**
     * 首页模板
     * @param $datas
     * @return array
     */
    public function ptransform($datas)
    {
        $user = Auth::guard('api') -> user();

        $a = [];
        foreach ($datas as $data) {
            $a[] =  [
                'id' => $data->id,
                'name' => $data->name,
                'integral' => $data->integral,
                'cover' => CloudStorage::downloadUrl($data->cover),
                'video' => CloudStorage::downloadUrl($data->preview_address),
                'count' => $data->count,
                'has_download' => $user ? (isset($data->hasManyDownload) ? 0 : 1) : 0,
                'time_add' => $data->time_add,
                'user' => $this->userTransformer->fragtransform($data->belongsToUser),
                'type' => $data->belongsToFolder->name,
                'duration' => $data->duration ?: 0,
                'vipfree' => $data->vipfree,
                'storyboard_count'  =>  $data ->storyboard_count,
            ];
        }
        return $a;
    }

    /**
     * 模板搜索
     * @param $datas
     * @return array
     */
    public function searchtransform($datas)
    {
        $user = Auth::guard('api') -> user();

        $a = [];
        foreach ($datas as $data) {
            $a[] =  [
                'id' => $data->id,
                'name' => $data->name,
                'integral' => $data->integral,
                'cover' => CloudStorage::downloadUrl($data->cover),
                'video' => CloudStorage::downloadUrl($data->preview_address),
                'count' => $data->count,
                'has_download' => $user ? (isset($data->hasManyDownload) ? 0 : 1) : 0,
                'time_add' => $data->time_add,
                'user' => $this->userTransformer->fragtransform($data->belongsToUser),
                'type' => $data->belongsToFolder->name,
                'duration' => $data->duration ?: 0,
                'watch_count'   =>  $data->watch_count,
                'vipfree' => $data->vipfree,
                'storyboard_count'  =>  $data ->storyboard_count,
            ];
        }
        return $a;
    }
}