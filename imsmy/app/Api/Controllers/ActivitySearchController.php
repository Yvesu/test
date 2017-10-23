<?php

namespace App\Api\Controllers;

use App\Models\Activity;
use App\Models\ActivityHotWord;
use App\Models\Tweet;
use App\Models\TweetActivity;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ActivitySearchController extends BaseController
{
    protected $paginate = 20;
    protected $activitySearchTransformer;

    //赛事搜索(有封页 创建用户)
    public function search(Request $request)
    {

            //查询赛事以及创建者信息
            $activity = \DB::table('activity')
                     ->leftJoin('user','activity.user_id','=','user.id')
                      ->where('comment','like',"%".$request->get('activity')."%")
                        ->take($this->paginate)
                       ->first();
            if (!$activity){
                    $new = new ActivityHotWord();
                    $new->hot_word = $request->get('activity');
                    $new->last_time = time();
                    $new->save();


                return response()->json([
                    'data' => '没有相关赛事信息',
                    'code'=>404
                ]);
            }

          //热搜统计
          $hot_word = $activity->theme;
          $activityId = \DB::table('activity') ->where('comment','like',"%".$request->get('activity')."%")->first()->id;

          //将赛事的ID保存
          session(['activity_id'=>null]);
          session(['activity_id'=>$activityId]);

              $hot_count = \DB::table('activity_hot_word')->where('hot_word','=',$hot_word)->first()->hot_count;
              $hot_count += 1;

              $res = ActivityHotWord::where('hot_word','=',$hot_word)->update(['hot_count'=>$hot_count]);

          return response()->json([
              'data' => count($activity) ? $activity : null,
              'code'=>200
          ]);


    }

    //搜索后作品列表
    public function  searchlist()
    {
       try {
            //获取赛事ID
            session(['activity' => 26]);
            $activity_id = session('activity');
            //获取赛事下的作品的ID
            $activityIdList = TweetActivity::where('activity_id', $activity_id)
                ->orderBy('activity_id', 'asc')
                ->get();

            foreach ($activityIdList as $v) {
                $tweet_id[] = $v->tweet_id;
            }

            //通过作品ID查找作品
            foreach ($tweet_id as $v) {
                $activityList[] = Tweet::where('id', $v)
                    ->orderBy('like_count', 'asc')
                    ->get();
            }
            //通过作品id查找用户ID
            $count = count($activityList);
            for ($i = 0; $i <= $count - 1; $i++) {
                foreach ($activityList[$i] as $v) {
                    $user_id[] = $v->user_id;

                }
            }

            //通过用户ID 搜索用户详情
            foreach ($user_id as $v) {
                $users[] = User::where('id', $v)->get();

            }

           return response()->json([
               'data' => $activityList,
                 'user'=>$users,
                'status'=>200
            ]);




        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

    }

    //赛事搜索后的全部作品列表
    public function alllist()
    {
        try {
            $list = \DB::table('tweet')
                ->leftJoin('user', 'tweet.user_id', '=', 'user.id')
                ->get();
            return response()->json([
                'data' => count($list) ? $list : null,
                'code'=>200
            ]);

        }catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


}




