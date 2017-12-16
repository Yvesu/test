<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/16 0016
 * Time: 上午 9:57
 */

namespace App\Api\Transformer;



use App\Facades\CloudStorage;

class NewFragmentSearchTransformer extends Transformer
{
    private $fragmentTypeTransformer;

    public function __construct
    (
        FragmentTypeTransformer $fragmentTypeTransformer
    )
    {
        $this->fragmentTypeTransformer = $fragmentTypeTransformer;
    }

    public  function transform($item)
    {
        return [
            'id'            =>  $item['id'],
            'name'          =>  $item['name'],
            'cover'         =>  CloudStorage::downloadUrl($item['cover']),
            'net_address'   =>  CloudStorage::downloadUrl($item['net_address']),
            'duration'      =>  $item['duration'],
            'watch_count'   =>  $item['watch_count'],
            'type'          =>  $this->fragmentTypeTransformer->transformCollection($item['belongs_to_many_fragment_type']),
        ];
    }


}