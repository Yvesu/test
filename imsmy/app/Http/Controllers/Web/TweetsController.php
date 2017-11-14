<?php

namespace App\Http\Controllers\Web;

use App\Http\Transformer\Mobile\TweetsMobileTransformer;
use App\Http\Transformer\Mobile\TweetRepliesMobileTransformer;
use App\Http\Controllers\Controller;
use App\Models\TweetReply;
use App\Models\Topic;
use App\Models\TweetActivity;
use App\Models\Tweet;
use App\Models\Activity;
use App\Models\ChannelTweet;
use App\Models\TweetTopic;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

/**
 * 手机端网页 动态分享
 * Class TweetsController
 * @package App\Http\Controllers\Admin\Content
 */
class TweetsController extends Controller
{

    // 动态评论详情 details方法
    protected $tweetsMobileTransformer;
    protected $tweetRepliesMobileTransformer;

    public function __construct(

        TweetsMobileTransformer $tweetsMobileTransformer,
        TweetRepliesMobileTransformer $tweetRepliesMobileTransformer
    )
    {
        $this->tweetsMobileTransformer = $tweetsMobileTransformer;
        $this->tweetRepliesMobileTransformer = $tweetRepliesMobileTransformer;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {

            $limit = 3;

            // 具体视频详情
            $tweet_collection = Tweet::with('belongsToUser')->active()->visible()->findOrFail($id);

            // 过滤
            $tweet = $this -> tweetsMobileTransformer -> transform($tweet_collection);

            // 取3条评论普通评论，按时间排序
            $reply_collections = TweetReply::with('belongsToUser')
                -> where('tweet_id',$id)
                -> where('reply_id',NULL)
                -> orderBy('created_at','desc')
                -> status()
                -> open()
                -> take($limit)
                -> get();


            // 过滤评论
            $replys = $reply_collections ? $this -> tweetRepliesMobileTransformer -> transformCollection($reply_collections->all()) : [];

            // 将tweet表中相应动态的播放次数 +1
            $tweet_collection -> increment('browse_times');

            # 话题数据 播放次数+1 开始
            // 判断是否存在话题关系
            $topic_ids = TweetTopic::where('tweet_id',$id)->pluck('topic_id')->all();

            // 判断是否存在
            if(!empty($topic_ids)){

                // 获取集合
                $statistics = Topic::whereIn('id',$topic_ids)->get();

                // 遍历
                foreach($statistics as $value){

                    // 将相关话题的播放次数 ＋1
                    $value -> forwarding_time ++;

                    $value -> time_update = getTime();

                    // 保存
                    $value -> save();
                }
            }
            # 话题数据 结束

            # 活动数据 播放次数+1 开始
            // 判断是否存在活动关系
            $activity_ids = TweetActivity::where('tweet_id',$id)->pluck('activity_id')->all();

            // 判断是否存在
            if(!empty($activity_ids)){

                // 获取集合
                $activity_statistics = Activity::whereIn('id',$activity_ids)->get();

                // 遍历
                foreach($activity_statistics as $value){

                    // 将相关话题的播放次数 ＋1
                    $value -> forwarding_times ++;

                    $value -> time_update = getTime();

                    // 保存
                    $value -> save();
                }
            }
            # 活动数据 结束

            // 获取5条相关动态
            $relate = $this -> related($id,5);

            // 返回数据
            return view('/mobile/tweets/details',['tweet'=>$tweet,'relate'=>$relate,'replys'=>$replys]);

        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * 为更多相关提供数据
     * @param $tweet_id 动态id
     * @return array
     */
    protected function related($tweet_id,$limit)
    {
        // 通过下面arrayIntersect函数将数据添加到$data尾部
        $data = new Collection();

        // 用来接收id
        $arr = [$tweet_id];

        # 话题
        // 获取该动态话题id信息
        $topic_ids = TweetTopic::where('tweet_id',$tweet_id)->pluck('topic_id')->all();

        // 判断话题是否存在
        if(count($topic_ids)){

            $topic_tweets = Tweet::with('belongsToUser')->whereHas('hasManyTopicTweet',function($query) use($topic_ids) {

                $query -> whereIn('topic_id',$topic_ids);
            })->visible()->where('type',0)->whereNull('original')->take(200)->get();

            // 判断数量
            if($topic_tweets->count()>$limit) $topic_tweets = $topic_tweets->random($limit);

            // 获取交集
            $this->arrayIntersect($arr, $data, $topic_tweets);
        }

        # 活动
        // 判断该动态活动
        $activity_ids = TweetActivity::where('tweet_id',$tweet_id)->pluck('activity_id')->all();

        // 判断动态是否存在
        if(count($activity_ids)){

            $activity_tweets = Tweet::with('belongsToUser')->whereHas('hasManyActivityTweet',function($query) use($activity_ids) {

                $query -> whereIn('activity_id',$activity_ids);
            })->visible()->where('type',0)->whereNull('original')->take(200)->get();

            // 判断数量
            if($activity_tweets->count()>$limit) $activity_tweets = $activity_tweets->random($limit);

            // 获取交集
            $this->arrayIntersect($arr, $data, $activity_tweets);
        }

        // 判断 $data 数据数量
        if($data->count() >= $limit) return $this->tweetsMobileTransformer->transformCollection($data->random($limit)->values()->all());

        # 频道
        // 获取该动态频道id信息
        $channel_ids = ChannelTweet::where('tweet_id',$tweet_id)->pluck('channel_id')->all();

        // 判断频道是否存在
        if(count($channel_ids)) {

            $channel_tweets = Tweet::with('belongsToUser')->whereHas('hasManyChannelTweet', function ($query) use ($channel_ids) {

                $query->whereIn('channel_id', $channel_ids);
            })->visible()->where('type', 0)->whereNull('original')->take(200)->get();

            // 判断数量
            if($channel_tweets->count()>$limit) $channel_tweets = $channel_tweets->random($limit);

            // 获取交集
            $this->arrayIntersect($arr, $data, $channel_tweets);

            // 判断 $data 数据数量
            if($data->count() > $limit) return $this->tweetsMobileTransformer->transformCollection($data->random($limit)->values()->all());
        }

        // 填充普通动态
        $tweets = Tweet::with('belongsToUser')->visible()->where('type', 0)->whereNull('original')->take(200)->get();

        // 判断数量
        if($tweets->count()>$limit) $tweets = $tweets->random($limit);

        // 获取交集
        $this->arrayIntersect($arr, $data, $tweets);

        // 判断数量
        if($data->count()>$limit) return $this->tweetsMobileTransformer->transformCollection($data->random($limit)->values()->all());

        return $this->tweetsMobileTransformer->transformCollection($data->all());
    }

    /** 动态去重，并合并多个类型的动态
     * @param $array 存储id
     * @param $data  存储所有的值
     * @param $tweets
     */
    public function arrayIntersect(&$array, &$data, $tweets)
    {
        if (isset($tweets)){
            foreach ($tweets as $tweet) {
                $intersect = array_intersect($array,[$tweet->id]);
                if (! sizeof($intersect)) {

                    array_push($array,$tweet->id);

                    // 把数据添加到$data尾部
                    $data->push($tweet);
                }
            }
        }
    }

}
