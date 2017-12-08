<?php

namespace App\Http\Transformer;

use App\Models\TweetLike;
//use App\Api\Transformer\UsersTransformer;
use App\Models\Admin\Administrator;
use App\Models\TweetActivity;
use App\Models\User;
use CloudStorage;
use Auth;

class VideoIndexTransformer extends Transformer
{
    private $usersTransformer;

    public function __construct(
        UsersTransformer $usersTransformer
    )
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($tweet)
    {
//        $user = Auth::guard('api')->user();

        $official = '';
        $activity = '';
        $verity = '';

        // 判断视频类型
        if(!$official = Administrator::where('user_id', $tweet->user_id) -> first()) {

            if(!$activity = TweetActivity::where('tweet_id', $tweet->id) -> first()) {

                if(!$verity = User::where('id', $tweet->user_id) -> where('verify', '<>', 0) -> first());
            }
        }

        $behavior = [
            'dotype' => '推荐',
            'doshield' => '屏蔽',
        ];

        $a =$tweet->belongsToCheck->first()?$tweet -> belongsToCheck->first()->name:'';
        if(!is_null($tweet->hasOneTop()->first())){
            $behavior['hot']='热门';
            if(!is_null($tweet->hasOneTop()->first()->belongstoToper->first())){
                $b = $tweet->hasOneTop()->first()->belongstoToper->first()->name;
            }else{
                $b = '';
            }
        }else{
            $behavior['cancelhot']='取消热门';
            $b = '';
        }
        if(!is_null($tweet->hasOneTop()->first())){
            if(!is_null($tweet->hasOneTop()->first()->belongsToRecommender->first())){
                $c = $tweet->hasOneTop->first()->belongsToRecommender->first()->name;
            }else{
                $c = '';
            }
        }else{
            $c = '';
        }
        if($a){
            if($b){
                if($c){
                    $operator = $a.'、'.$b.'、'.$c;
                }else{
                    $operator = $a.'、'.$b;
                }
            }else{
                if($c){
                    $operator = $a.'、'.$c;
                }else{
                    $operator = $a;
                }
            }
        }else{
            if($b){
                if($c){
                    $operator = $b.'、'.$c;
                }else{
                    $operator = $b;
                }
            }else{
                if($c){
                    $operator = $c;
                }else{
                    $operator = '';
                }
            }
        }
        return [
            'id'            =>  $tweet->id,
            'browse_times'  =>  $tweet->browse_times,
            'content'       =>  $tweet->hasOneContent()->first()?$tweet->hasOneContent()->first()->content:'',
            'screen_shot'   =>  CloudStorage::downloadUrl($tweet->screen_shot),
            'type'          =>  $official ? '官方发布' : ($activity ? '参赛作品' : ($verity ? '认证用户' : '')),   // 视频类型，参赛、官方和认证,注意优先级
            'video'         =>  CloudStorage::downloadUrl($tweet->video),
            'duration'      =>  floor($tweet->duration),
            'user'          =>  $this->usersTransformer->transform($tweet->belongsToUser),
            'created_at'    =>  strtotime($tweet->created_at),
            'operator'      =>  $operator,
            'behavior'      =>  $behavior,
        ];
    }
}