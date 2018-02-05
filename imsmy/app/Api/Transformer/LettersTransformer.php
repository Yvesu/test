<?php

namespace App\Api\Transformer;

use App\Models\PrivateLetter;
use App\Models\Tweet;
use CloudStorage;
use Auth;

class LettersTransformer extends Transformer
{
    private $usersTransformer;
    private $channelTweetsTransformer;

    public function __construct(
        UsersTransformer $usersTransformer,
        ChannelTweetsTransformer $channelTweetsTransformer
    )
    {
        $this -> usersTransformer = $usersTransformer;
        $this -> channelTweetsTransformer = $channelTweetsTransformer;
    }

    public function transform($letter)
    {
        if ($letter->is_tweet === 0){

            $object = PrivateLetter::where('pid',$letter->id)->get();

            return [
                'id'            =>  $letter -> id,
                'content'       =>  $letter -> content,
                'from'          =>  $letter -> from,
                'to'            =>  $letter -> to,
                'is_tweet'      =>  $letter->is_tweet,
                'created_at'    =>  strtotime($letter -> created_at),
                'user'          =>  $this -> usersTransformer->transform($letter->belongsToUser),
                'son'           => $this->transformCollection($object->all())
            ];
        }else{

            $tweets_data = Tweet::where('type', 0)
                ->where('visible', 0)
                ->with(['belongsToManyChannel' => function ($q) {
                    $q->select(['name']);
                }, 'hasOneContent' => function ($q) {
                    $q->select(['content', 'tweet_id']);
                }, 'belongsToUser' => function ($q) {
                    $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                },'hasOnePhone' =>function($q){
                    $q->select(['id','phone_type','phone_os','camera_type']);
                }])
                ->find($letter->is_tweet);

            switch ($tweets_data->active){
                case 3 :
                    $str =  (object)'该条动态已经被主人删除了!';
                    break;
                case 5 :
                    $str = (object)'管理员已经禁止观看!';
                    break;
                case 6 :
                    $str =  (object)'该动态还在审核中,请您耐心等候...';
                    break;
            }

            return [
                'id'            =>  $letter -> id,
                'content'       =>  $letter -> content,
                'is_tweet'      => $letter->is_tweet,
                'tweet'         =>  in_array($tweets_data->active,[3,5,6],TRUE) ? $str :  $this->channelTweetsTransformer->transform( $tweets_data ),
                'from'          =>  $letter -> from,
                'to'            =>  $letter -> to,
                'created_at'    =>  strtotime($letter -> created_at),//scalar
                'user'          =>  $this -> usersTransformer->transform($letter->belongsToUser)
            ];
        }
    }

    protected function findSubTree($cates,$id=0,$lev=1){
        $subtree = [];//子孙数组
        foreach ($cates as $v) {
            if($v->pid==$id){
                $v->lev = $lev;
                $subtree[] = $v;
                $subtree = array_merge($subtree,$this->findSubTree($cates,$v->id,$lev+1));
            }
        }
        return $subtree;
    }
}