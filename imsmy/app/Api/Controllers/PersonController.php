<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\PersonTweetsTransformer;
use App\Models\Friend;
use App\Models\Tweet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class PersonController extends BaseController
{
    protected $paginate = 20;

    private $personTweetsTransformer;

    public function __construct
    (
       PersonTweetsTransformer $personTweetsTransformer
    )
    {
       $this->personTweetsTransformer=$personTweetsTransformer;
    }

    public function tweet(Request $request)
    {
        try{
//            过滤数据
            if( !is_numeric($request->get('page',1))   || !is_numeric($request->get('id')) )  return response()->json(['message'=>'bad_request'],403);

            //页码
            $page = $request -> get('page');

            //被查看用户的id
            $be_watch_userid = $request->get('id');

            //判断用户是否登录
            $user = Auth::guard('api')->user();

            if($user){                              //如果用户登录
                //判断登录用户与被查看用户的关系
                $id = $user -> id;

                //不存在任何关系   查看公开动态
                $is_friend_1 = Friend::OfIsFriend($id,$be_watch_userid)->first();

                if($is_friend_1){
                    $is_friend_2 = Friend::OfIsFriend($be_watch_userid,$id)->first();

                    if ($is_friend_2){
                        $is_friend = 2;             //好友
                    }else{
                        $is_friend = 1;             //关注了对方
                    }

                }else{
                    $is_friend = 0;                 //没有什么关系
                }

                if ($id == $be_watch_userid){
                    $is_friend = 3;                 //自己看自己
                }

               switch ($is_friend){
                   case 0 :

                   case 1:
                        return $this->open($page,$be_watch_userid);

                   case 2:
                       return $this->friendOpen($page,$be_watch_userid);    //为好友  + 好友可见

                   case 3:
                       return $this->self($page,$be_watch_userid);      //登录用户 === 被查看用户 可以查看全部
               }

            }else{                             //用户未登录
                //查看该用户的公开动态
                return $this->open($page,$be_watch_userid);
            }

        }catch(\Exception $e){
            return response()->json(['messahe'=>'bad_request'],500);
        }
    }

    /**
     * @param $page
     * @param $be_watch_userid
     * @return \Illuminate\Http\JsonResponse
     */
    private function open($page,$be_watch_userid)
    {
        try{
            $tweets = Tweet::WhereHas('belongsToUser',function ($q) use ($be_watch_userid){
                $q->where('user_id',$be_watch_userid);
            })
                ->with(['hasOneContent'=>function($q){
                    $q->select(['tweet_id','content']);
                },'belongsToManyChannel'=>function($q){
                    $q->select(['name']);
                }
//                ,'belongsToUser'=>function($q){
//                    $q->select(['id','nickname','avatar','signature','verify','verify_info']);
//                }
                ])
                ->where('active',1)
                ->where('visible',0)
                -> forPage($page, $this -> paginate)
                ->orderBy('created_at','desc')
                ->get(['id','user_id','type','location','like_count','reply_count','tweet_grade_total','tweet_grade_times','duration','screen_shot','browse_times','created_at']);

            return response()->json([
                'data' => $this->personTweetsTransformer->transformCollection($tweets->all()),
            ]);
        }catch(\Exception $e){
            return response()->json(['messahe'=>'bad_request'],500);
        }
    }

    /**
     * @param $page
     * @param $be_watch_userid
     * @return \Illuminate\Http\JsonResponse
     */
    private function friendOpen($page,$be_watch_userid)
    {
        try{
            $tweets = Tweet::WhereHas('belongsToUser',function ($q) use ($be_watch_userid){
                $q->where('user_id',$be_watch_userid);
            })
            ->with(['hasOneContent'=>function($q){
                $q->select(['tweet_id','content']);
            },'belongsToManyChannel'=>function($q){
                $q->select(['name']);
            }
//            ,'belongsToUser'=>function($q){
//                $q->select(['id','nickname','avatar','signature','verify','verify_info']);
//            }
            ])
                ->whereIn('active',[0,1])
                ->whereIn('visible',[0,1])
                -> forPage($page, $this -> paginate)
                ->orderBy('created_at','desc')
                ->get(['id','user_id','type','location','like_count','reply_count','tweet_grade_total','tweet_grade_times','duration','screen_shot','browse_times','created_at']);

            return response()->json([
                'data' => $this->personTweetsTransformer->transformCollection($tweets->all()),
            ]);
        }catch(\Exception $e){
            return response()->json(['messahe'=>'bad_request'],500);
        }
    }

    private function self($page,$be_watch_userid)
    {
        try{
            $tweets = Tweet::WhereHas('belongsToUser',function ($q) use ($be_watch_userid){
                $q->where('user_id',$be_watch_userid);
            })
                ->with(['hasOneContent'=>function($q){
                    $q->select(['tweet_id','content']);
                },'belongsToManyChannel'=>function($q){
                    $q->select(['name']);
                }
//            ,'belongsToUser'=>function($q){
//                $q->select(['id','nickname','avatar','signature','verify','verify_info']);
//            }
                ])
                ->whereIn('active',[0,1])
                -> forPage($page, $this -> paginate)
                ->orderBy('created_at','desc')
                ->get(['id','user_id','type','location','like_count','reply_count','tweet_grade_times','tweet_grade_total','duration','screen_shot','browse_times','created_at']);

            return response()->json([
                'data' => $this->personTweetsTransformer->transformCollection($tweets->all()),
            ]);
        }catch(\Exception $e){
            return response()->json(['messahe'=>'bad_request'],500);
        }
    }

}
