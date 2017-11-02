<?php

namespace App\Api\Controllers;

use App\Models\Activity;
use App\Models\ActivityHotWord;
use App\Models\Keywords;
use App\Models\Tweet;
use App\Models\TweetActivity;
use App\Models\User;
use App\Models\Word_filter;
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
//        dd( in_array('啊实打实多',Redis::lrange('HOTSEARCH:LIST:NO_EXIST',0,-1))  );

        //接收用户搜索的内容
        $activity =  $request->get('activity');

        //判断用户输入的内容是否为空
        if (!$activity){
            return response()->json([
                'code'=>'500',
                'message'=>'输入的内容为空'
            ]);
        }

        //检查缓存中是否存在相关数据
        $key = 'HOTSEARCH:LIST:EXIST';
        //设置哈希
        $hashkey = 'HOTSEARCH:HASH:'.$activity;
        //查询缓存中的所有字段
        $arr_activity = Redis::lrange($key,0,-1);
        $lists = [];
        foreach($arr_activity as $v){
            $lists[] = strstr($v,$activity);
        }
        if(!$lists){
            $lists[0] = null;
        }

            //如果缓存中没有
        if(!$lists[0]){
            // 查询数据库
            $info = Activity::where('theme', 'like', "%" . $request->get('activity') . "%")
                ->get();
            $ids = [];
            foreach ($info as $v){
                $ids[] = $v->id;
            }
           foreach ($ids as $k=>$v){
                $countt[$k] = TweetActivity::where('activity_id','=',$v)->count();
           }
//           dd($count);
            $a = [];
            foreach ($info as $k => $v) {
                $a[$k]['id'] = $v->id;$a[$k]['user_id'] = $v->user_id;$a[$k]['active'] = $v->active;$a[$k]['official'] = $v->official;$a[$k]['bonus'] = $v->bonus; $a[$k]['theme'] = $v->theme;$a[$k]['comment'] = $v->comment;$a[$k]['forwarding_time'] = $v->forwarding_time;$a[$k]['comment_time'] = $v->comment_time;$a[$k]['work_count'] = $v->work_count;$a[$k]['users_count'] = $v->users_count;$a[$k]['like_count'] = $v->like_count;$a[$k]['location'] = $v->location;$a[$k]['icon'] = $v->icon;$a[$k]['expires'] = $v->expires;$a[$k]['status'] = $v->status;$a[$k]['recommend_start'] = $v->recommend_start;$a[$k]['recommend_expires'] = $v->recommend_expires;$a[$k]['time_add'] = $v->time_add;$a[$k]['time_update'] = $v->time_update;$a[$k]['son_count'] = array_shift($countt);
            }
                $count = count($a);
            //将信息写入缓存
            if (!$a) {
                //当数据库里不存在热词时
                if( !in_array($activity, Redis::lrange('HOTSEARCH:LIST:NO_EXIST',0,-1))  ){
                    Redis::rpush('HOTSEARCH:LIST:NO_EXIST', $activity);
                }
//=============== PV+1 ===================
                //不存在的搜索次数+1(PV)
                $hot_word_key = 'KEYWORD:NOEXIST_PV:'.$activity;
                Redis::incr($hot_word_key);

//==============  IP+1 ====================

                //获取客户端的IP
                $ip = getIP();
                //生成ip队列的key
                $ip_key= 'VALID_HOT_WORD:NO_EXIST'.$activity;

                //查询本词的记录IP
                if(!in_array($ip,Redis::lrange($ip_key,0,-1))){
                    //将搜索的用户的IP放入队列
                    Redis::rpush($ip_key,$ip);
                }
//================= 响应 =================
                return response()->json([
                    'code' => '404',
                    'message' => '没有相关赛事内容'
                ]);
            } else {
                //当数据库存在热词时
                for ($i = 0; $i <= $count - 1; $i++) {
                    //队列加入热词
                    Redis::rpush('HOTSEARCH:LIST:EXIST', $activity . $i);
                }
//================ PV+1 ===================
                //搜索次数+1
                $hot_word_key = 'KEYWORD:EXIST_PV:'.$activity;
                Redis::incr($hot_word_key);
//================= IP+1 ====================
                //获取客户端的IP
                $ip = getIP();
                //生成ip队列的key
                $ip_key= 'VALID_HOT_WORD:EXIST'.$activity;

                //查询本词的记录IP
                if(!in_array($ip,Redis::lrange($ip_key,0,-1))){
                    //将搜索的用户的IP放入队列
                    Redis::rpush($ip_key,$ip);
                }

                //写入缓存
                foreach ($a as $k => $v) {
                    Redis::hMset($hashkey . $k, $v);
                }
            }

            //将数据库查询到的数据响应app
            return response()->json([
                'data' => $a,
                'code' => '200'
            ]);
        }else{
            //搜索次数+1
            $hot_word_key = 'KEYWORD:EXIST_PV:'.$activity;
            Redis::incr($hot_word_key);

            $count = count($lists);
            for ($i=0;$i<=$count-1;$i++){
                $hashinfo[$i] = Redis::hGetall('HOTSEARCH:HASH:'.$activity.$i);
            }
            //将数据库查询到的数据响应app
            return response()->json([
                'data' => $hashinfo,
                'code' => '200'
            ]);
        }
    }

}




