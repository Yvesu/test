<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\NewTopicDetailsTransformer;
use App\Api\Transformer\TopicTweetTransformer;
use App\Models\Blacklist;
use App\Models\Channel;
use App\Models\Friend;
use App\Models\Topic;
use App\Models\Tweet;
use App\Models\TweetHot;
use App\Models\TweetTopic;
use App\Models\UsersLikes;
use App\Models\UsersUnlike;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RelatedController extends Controller
{
    protected $paginate = 20;

    protected $channelTweetsTransformer;

    protected $newTopicDetailsTransformer;

    protected $topicTweetTransformer;

    public function __construct
    (
        ChannelTweetsTransformer $channelTweetsTransformer,
        NewTopicDetailsTransformer $newTopicDetailsTransformer,
        TopicTweetTransformer $topicTweetTransformer
    )
    {
        $this -> channelTweetsTransformer = $channelTweetsTransformer;
        $this -> newTopicDetailsTransformer = $newTopicDetailsTransformer;
        $this -> topicTweetTransformer = $topicTweetTransformer;
    }

    public function index(Request $request,$id = '0')
    {
        //获取类型
        if ( is_null($type = $request->get('type')) ) return response()->json(['message'=>'bad_request'],403);

        //获取页码
        $page = $request ->get('page',1);

        //是否热门
        $is_hot = $request->get('is_hot','0');

        //接收匹配的信息
        if ( is_null( $info = $request->get('info')) ) return response()->json(['message'=>'bad_request'],403);

        //非热门动态
        $tweets_nohot = Tweet::where('type',0)
            ->where('active',1)
            ->where('visible',0)
            ->orderBy('created_at','DESC')
            ->with(['belongsToManyChannel' =>function($q){
                $q -> select(['name']);
            },'hasOneContent' =>function($q){
                $q->select(['content','tweet_id']);
            },'belongsToUser' =>function($q){
                $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
            },'hasOnePhone' =>function($q){
                $q->select(['id','phone_type','phone_os','camera_type']);
            }])->  forPage($page,$this->paginate);

        //热门的动态
        $except  = TweetHot::with(['hasOneTweet'])->where('top_expires','>=',time())->orWhere('recommend_expires','>=',time())->pluck('tweet_id');
        $tweets_hot = Tweet::where('type',0)
            ->where('active',1)
            ->where('visible',0)
            ->orderBy('created_at','DESC')
            ->with(['belongsToManyChannel' =>function($q){
                $q -> select(['name']);
            },'hasOneContent' =>function($q){
                $q->select(['content','tweet_id']);
            },'belongsToUser' =>function($q){
                $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
            },'hasOnePhone' =>function($q){
                $q->select(['id','phone_type','phone_os','camera_type']);
            }])->  forPage($page,$this->paginate)->whereIn('id',$except->all());

        //初始化用户信息
        $user_info = [];
        if ( $id !== '0'){
            //获取用户的ID
            $user_id = (int)$id;

            //查看用户喜好
            $user_channels = UsersLikes::where('user_id',$user_id)->pluck('channel_id');
            $user_channels = $user_channels -> all();

            if ( !$user_channels ){
                $user_channels = Channel::where('active',1)->pluck('id')->all();
            }
            $user_info['channels'] = $user_channels;

            //将用户不感兴趣的排除
            $ids_obj             = UsersUnlike::where('user_id',$user_id)->where('type','0')->pluck('work_id');
            $ids_unlike_arr      = $ids_obj->all();
            $user_info['unlike'] = $ids_unlike_arr;

            //获取用户的黑名单
            $users_id_black     = Blacklist::where('from',$user_id)->pluck('to');
            $users_id_black     = $users_id_black->all();
            $user_info['black'] = $users_id_black;
        }

        switch ( $type ){
            case '1' :          // 根据手机类型进行搜索
                return $this -> phone( $tweets_nohot,$tweets_hot,(int)$info,$is_hot,$id,$user_info );
            case '2' :          //根据经纬度匹配
                return $this -> location( $tweets_nohot,$tweets_hot,$info,$is_hot,$id,$user_info );
            case '3' :          //根据话题
                return $this -> topic( $tweets_nohot,$tweets_hot,(int)$info,$is_hot,$id,$user_info,$page);
        }

    }

    /**
     * @param $tweets_nohot
     * @param $tweets_hot
     * @param $info
     * @param $is_hot
     * @param $id
     * @param $user_info
     * @return \Illuminate\Http\JsonResponse
     */
    private function phone( $tweets_nohot,$tweets_hot,$info,$is_hot,$id,$user_info )
    {
        $user_likes = explode(',',$user_info['channels'][0]);
        if ($id === '0'){
            if ( $is_hot === '0' ){
                $tweets = $tweets_nohot->where('phone_id',$info)->get();
                $count = $tweets_nohot->where('phone_id',$info)->count();
            }else{
                $tweets = $tweets_hot->where('phone_id',$info)->get();
                $count = $tweets_hot->where('phone_id',$info)->count();
            }
        }else{
            if ( $is_hot === '0' ){
                $tweets = $tweets_nohot->where('phone_id',$info)->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                $count = $tweets_nohot->where('phone_id',$info)->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->count();
            }else{
                $tweets = $tweets_hot->where('phone_id',$info)->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                $count = $tweets_hot->where('phone_id',$info)->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->count();
            }
        }
        return  response()->json([
            'data'  => $this->channelTweetsTransformer->transformCollection($tweets->all()),
            'count' =>  $count,
        ]);
    }

    /**
     * @param $tweets_nohot
     * @param $tweets_hot
     * @param $info
     * @param $is_hot
     * @param $id
     * @param $user_info
     * @return \Illuminate\Http\JsonResponse
     */
    private function location( $tweets_nohot,$tweets_hot,$info,$is_hot,$id,$user_info )
    {
        $lgt_lat_obj = json_decode( $info );
        $big_lgt = $lgt_lat_obj->lgt + 0.1;
        $small_lgt = $lgt_lat_obj->lgt - 0.1;
        $big_lat = $lgt_lat_obj->lat + 0.1;
        $small_lat = $lgt_lat_obj->lat - 0.1;
        $user_likes = explode(',',$user_info['channels'][0]);
        if ($id === '0'){
            if ( $is_hot === '0' ){
                $tweets = $tweets_nohot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->get();
                $count  = $tweets_nohot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->count();
            }else{
                $tweets = $tweets_hot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                $count  = $tweets_hot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->count();
            }
        }else{
            if ( $is_hot === '0' ){
                $tweets = $tweets_nohot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                $count  = $tweets_nohot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->count();
            }else{
                $tweets = $tweets_hot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                $count  = $tweets_hot-> whereBetween('lgt',[$small_lgt,$big_lgt])-> whereBetween('lat',[$small_lat,$big_lat])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->count();
            }
        }
        return  response()->json([
            'data'  => $this->channelTweetsTransformer->transformCollection($tweets->all()),
            'count' =>  $count,
        ]);
    }

    private function topic( $tweets_nohot,$tweets_hot,$info,$is_hot,$id,$user_info,$page)
    {
        $topic = Topic::find($info);

        $top = [];
        $handpicks = [];
        $hot_topic_tweet = [];
        if ($id === '0'){
            if ( $topic->compere_id === 0){
                //话题动态
                $topic_arr = TweetTopic::where('topic_id',$info)->pluck('tweet_id');

                //热门动态
                $tweets_1 = $tweets_hot->with(['hasOneTweetTopic'])->whereIn('id',$topic_arr->all())->get();

                //随机 5 个
                if ($tweets_1->count() >= 5 ){
                    $tweets_1 = $tweets_1->random(5);
                }

                $tweets_1 = $this->topicTweetTransformer->transformCollection($tweets_1->all());

                //非热门
                $tweets_2 = $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('id',$topic_arr->all())->get();

                $tweets_2 = $this->topicTweetTransformer->transformCollection($tweets_2->all());

                $tweets = mult_unique( array_merge($tweets_1,$tweets_2) );

                return response()->json([
                    'data'  =>  $tweets,
                ]);

            }else{

                //非热门
                $nohot_topic_tweet_ids = TweetTopic::where('topic_id',$info)->where('status_compere','1')->pluck('tweet_id');

                $nohot_topic_tweet   =  $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('id',$nohot_topic_tweet_ids->all())->get();

                if ($page === 1 ){
                    //置顶  1
                    $top_id = TweetTopic::where('topic_id',$info)->where('is_top','1')->where('status_compere','1')->pluck('tweet_id');

                    if ( $top_id->all() ){
                        $top = $tweets_nohot->with(['hasOneTweetTopic'])->where('id',$top_id->all()[0])->first();
                        $top =  $this->topicTweetTransformer->transform( $top );
                    }

                    //精华  5
                    $handpicks_ids = TweetTopic::where('topic_id',$info)->where('is_handpick','1')->where('status_compere','1')->pluck('tweet_id');

                    if ( $handpicks_ids->all() ){
                        $handpicks = $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('id',$handpicks_ids->all() )->get();
                        if ($handpicks->count() >=5 ){
                            $handpicks = $handpicks->random(5);
                        }
                        $handpicks =  $this->topicTweetTransformer->transformCollection( $handpicks->all() );
                    }

                    //热门  5
                    $hot_topic_tweet_ids = TweetTopic::where('topic_id',$info)->where('status_compere','1')->pluck('tweet_id');

                    if ($hot_topic_tweet_ids ->all() ){
                        $hot_topic_tweet   =  $tweets_hot->with(['hasOneTweetTopic'])->whereIn('id',$hot_topic_tweet_ids->all())->get();
                        if ($hot_topic_tweet->count() >=5 ){
                            $hot_topic_tweet = $hot_topic_tweet->random(5);
                        }
                        $hot_topic_tweet =  $this->topicTweetTransformer->transformCollection( $hot_topic_tweet->all() );
                    }
                    $nohot_topic_tweet = $this->topicTweetTransformer->transformCollection( $nohot_topic_tweet->all() );

                    $data =  mult_unique( array_merge($handpicks,$hot_topic_tweet,$nohot_topic_tweet) );

                    return response()->json([
                        'top'   => $top,
                        'data'  => $data,
                    ]);
                }

                return response()->json([
                    'data'  =>  $this->topicTweetTransformer->transformCollection( $nohot_topic_tweet->all() ),
                ]);
            }
        }else{
            $user_likes = explode(',',$user_info['channels'][0]);

            if ( $topic->compere_id === 0){
                //话题动态
                $topic_arr = TweetTopic::where('topic_id',$info)->pluck('tweet_id');

                //热门动态
                $tweets_1 = $tweets_hot->with(['hasOneTweetTopic'])->whereIn('id',$topic_arr->all())->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();

                //随机 5 个
                if ($tweets_1->count() >= 5 ){
                    $tweets_1 = $tweets_1->random(5);
                }

                $tweets_1 = $this->topicTweetTransformer->transformCollection($tweets_1->all());

                //非热门
                $tweets_2 = $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('id',$topic_arr->all())->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();

                $tweets_2 = $this->topicTweetTransformer->transformCollection($tweets_2->all());

//                $tweet_3_ids = $this ->friendsTweets((int)$id ,$page);

//                $tweet_3 = $tweets_nohot->whereIn('id',$tweet_3_ids)->whereIn('channel_id', $user_info['channels'])->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();

//                $tweets_3 = $this->channelTweetsTransformer->transformCollection($tweet_3->all());

                $tweets = mult_unique( array_merge($tweets_1,$tweets_2) );

                return response()->json([
                    'data'  =>  $tweets,
                ]);

            }else{

                //非热门
                $nohot_topic_tweet_ids = TweetTopic::where('topic_id',$info)->pluck('tweet_id');

                $nohot_topic_tweet   =  $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('id',$nohot_topic_tweet_ids->all())->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();

//                $tweet_3_ids = $this ->friendsTweets((int)$id ,$page);

//                $tweet_3 = $tweets_nohot->whereIn('id',$tweet_3_ids)->whereIn('channel_id', $user_info['channels'])->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();

//                $tweets_3 = $this->channelTweetsTransformer->transformCollection($tweet_3->all());

                if ($page === 1 ){
                    //置顶  1
                    $top_id = TweetTopic::where('topic_id',$info)->where('is_top','1')->pluck('tweet_id');

                    if ( $top_id->all() ){
                        $top = $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->where('id',$top_id->all()[0])->first();
                        $top =  $this->topicTweetTransformer->transform( $top );
                    }

                    //精华  5
                    $handpicks_ids = TweetTopic::where('topic_id',$info)->where('is_handpick','1')->pluck('tweet_id');

                    if ( $handpicks_ids->all() ){
                        $handpicks = $tweets_nohot->with(['hasOneTweetTopic'])->whereIn('id',$handpicks_ids->all() )->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                        if ($handpicks->count() >=5 ){
                            $handpicks = $handpicks->random(5);
                        }
                        $handpicks =  $this->topicTweetTransformer->transformCollection( $handpicks->all() );
                    }

                    //热门  5
                    $hot_topic_tweet_ids = TweetTopic::where('topic_id',$info)->pluck('tweet_id');

                    if ($hot_topic_tweet_ids ->all() ){
                        $hot_topic_tweet   =  $tweets_hot->with(['hasOneTweetTopic'])->whereIn('id',$hot_topic_tweet_ids->all())->whereIn('channel_id', $user_likes)->whereNotIn('id',$user_info['unlike'])->whereNotIn('user_id',$user_info['black'])->get();
                        if ($hot_topic_tweet->count() >=5 ){
                            $hot_topic_tweet = $hot_topic_tweet->random(5);
                        }
                        $hot_topic_tweet =  $this->topicTweetTransformer->transformCollection( $hot_topic_tweet->all() );
                    }

                    $nohot_topic_tweet = $this->topicTweetTransformer->transformCollection( $nohot_topic_tweet->all() );

                    $data =  mult_unique( array_merge($handpicks,$hot_topic_tweet,$nohot_topic_tweet) );

                    return response()->json([
                        'top'   => $top,
                        'data'  => $data,
                    ],200);
                }

                return response()->json([
                    'data'  =>  $this->topicTweetTransformer->transformCollection( $nohot_topic_tweet->all() ),
                ],200);
            }
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function topicDetails($id)
    {
        //获取该话题的信息
        $topic = Topic::with(['hasOneCompere'=>function($q){
            $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
        }])
            ->find($id);

        return response()->json([
            'data'  => $this ->newTopicDetailsTransformer->transform( $topic ),
        ]);
    }

    /**
     * @param $id
     * @param $page
     * @return array
     */
    private function friendsTweets($id,$page)
    {
        $user_id = (int)$id;

        //获取好友
       $friends_1 = Friend::where('from',$user_id)->pluck('to');
        $friends = [];
       foreach ($friends_1->all() as $v){
           $friend_2 = Friend::where('from',$v)->where('to',$user_id)->first();
           if ($friend_2){
               $friends[] = $v ;
           }
       }

        $friend_teweets = Tweet::where('type',0)
            ->where('active',1)
            ->where('visible',1)
            ->orderBy('created_at','DESC')
            ->whereIn('user_id',$friends)
            ->forPage($page,$this->paginate)
            ->pluck('id');

        $self_tweets = Tweet::where('type',0)
            ->where('active',1)
            ->where('user_id',$user_id)
            ->where('visible',3)
            ->orderBy('created_at','DESC')
            ->forPage($page,$this->paginate)
            ->pluck('id');

        $friens_self =  mult_unique(  array_merge($self_tweets->toArray(),$friend_teweets->toArray()) );

        return $friens_self;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toHandpick(Request $request)
    {
        //接收用户信息
        $user = \Auth::guard('api')->user();

        //过滤
        if ( is_null($id = $request -> get('id'))
            || is_null( $topic_id = $request ->get('topic'))
            || is_null( $type = $request ->get('type'))
            || is_null( $yet = $request ->get('yet')) ) return response()->json(['message'=>'bad request'],400);

        //匹配话题
        $topic = Topic::find( $topic_id );

        //判断登录用户是否为主持人
        if ( $user->id !== $topic->compere_id) return response()->json(['message'=>'the user isn`t compere'],403);

        $tweet = TweetTopic::where('topic_id', $topic_id)
            ->where('tweet_id',$id)
            ->first();

        if ( $type === '1' ){     //选精华

            if (  $yet === '1' && $tweet->is_handpick === $yet ){
                return response()->json(['message'=>'the tweet already handpick'],205);
            }elseif(  $yet === '0' && $tweet->is_handpick === $yet ){
                return response()->json(['message'=>'the tweet already isn`t handpick'],205);
            }
            $result = $tweet->update([ 'is_handpick'=>$yet ]);

            if ( $result ){
                return response()->json(['message'=>'success'],201);
            }else{
                return response()->json(['message'=>'failed'],500);
            }

        }elseif($type === '2'){
            if (  $yet === '1' && $tweet->is_top === $yet ){
                return response()->json(['message'=>'the tweet already top'],205);
            }elseif(  $yet === '0' && $tweet->is_top === $yet ){
                return response()->json(['message'=>'the tweet already isn`t top'],205);
            }

            if( $yet==='1' ){
                $result_1 = TweetTopic::where('topic_id',$topic_id)->where('is_top','1')->update(['is_top'=>'0']);
                $result_2 = $tweet->update([ 'is_top'=>'1' ]);

                if ( $result_1 && $result_2 ){
                    return response()->json(['message'=>'success'],201);
                }else{
                    return response()->json(['message'=>'failed'],500);
                }

            }else{
                $result_2 = $tweet->update([ 'is_top'=>'0' ]);
                if ( $result_2 ){
                    return response()->json(['message'=>'success'],201);
                }else{
                    return response()->json(['message'=>'failed'],500);
                }
            }


        }else{   //不符合主题   隐藏
            $result = $tweet->update(['status_compere'=>'0']);

            if ( $result ){
                return response()->json(['message'=>'success'],201);
            }else{
                return response()->json(['message'=>'failed'],500);
            }
        }

    }

    public function handpickIndex($topic_id,Request $request)
    {
        $tweet_ids = TweetTopic::where('topic_id',$topic_id)->where('is_handpick','1')->pluck('tweet_id');

        $page = $request->get('page',1);
        //获取数据
        $tweets_nohot = Tweet::where('type',0)
            ->where('active',1)
            ->where('visible',0)
            ->orderBy('created_at','DESC')
            ->with(['belongsToManyChannel' =>function($q){
                $q -> select(['name']);
            },'hasOneContent' =>function($q){
                $q->select(['content','tweet_id']);
            },'belongsToUser' =>function($q){
                $q->select(['id','nickname','avatar','cover','verify','signature','verify_info']);
            },'hasOnePhone' =>function($q){
                $q->select(['id','phone_type','phone_os','camera_type']);
            },'hasOneTweetTopic'])
                ->whereIn('id',$tweet_ids->all())
                ->forPage($page,$this->paginate)
                ->get();

        return response()->json([
            'data'  =>  $this->topicTweetTransformer->transformCollection( $tweets_nohot->all() ),
        ],200);
    }

}
