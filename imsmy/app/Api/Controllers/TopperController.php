<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ActivityDiscoverTransformer;
use App\Api\Transformer\ChannelTweetsTransformer;
use App\Api\Transformer\TemplateDiscoverTransformer;
use App\Models\Activity;
use App\Models\Make\MakeTemplateFile;
use App\Models\Topper;
use App\Models\Tweet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class TopperController extends Controller
{

    protected $channelTweetsTransformer;
    // 发现页面赛事
    protected $activityDiscoverTransformer;

    // 发现页面模板
    protected $templateDiscoverTransformer;

    public function __construct(
        ChannelTweetsTransformer $channelTweetsTransformer,
        ActivityDiscoverTransformer $activityDiscoverTransformer,
        TemplateDiscoverTransformer $templateDiscoverTransformer
    )
    {
        $this -> channelTweetsTransformer = $channelTweetsTransformer;
        $this -> activityDiscoverTransformer = $activityDiscoverTransformer;
        $this -> templateDiscoverTransformer = $templateDiscoverTransformer;
    }

    public function index(Request $request)
    {
        //省
        $province = $request->get('province','');

        //市
        $city = $request ->get('city','');

        if(!empty($city)) {
            //按市推送

            $data = Topper::where('city', '=', $city)
                ->orderBy('create_at', 'desc')
                ->where('closing_time', '>', time())
                ->first();

            if (!empty($data)) {
                return response()->json($data,200);
            }
        }

        if(!empty($province)) {
            //按省推荐
            $data = Topper::where('province', '=', $province)
                ->where('type','=',1)
                ->orderBy('create_at', 'desc')
                ->where('closing_time', '>', time())
                ->first();
            if (!empty($data)) {
                return response()->json($data,200);
            }
        }
        $data = [];
            //网页作品存在时
            $net_work = Topper::where('closing_time','>',time())
                ->where('type','=',1)
                ->orderBy('create_at','desc')
                ->get(['icon','addr','describe','create_at']);

            if ($net_work->count()){
                $arr['icon']  = $net_work->random(1)->toArray()[0]['icon'];
                $arr['addr']  = $net_work->random(1)->toArray()[0]['addr'];
                $arr['describe']  = $net_work->random(1)->toArray()[0]['describe'];
                $arr['create_at']  = $net_work->random(1)->toArray()[0]['create_at'];
                $arr['style']  = 4;
                $data[][] = $arr;
            }

            // 模板
                $template = Topper::where('type', '=', 2)
                    ->orderBy('create_at', 'desc')
                    ->where('closing_time', '>', time())
                    ->pluck('works_id');

                if ($template->all()) {
                    $templates = MakeTemplateFile::with(['belongsToUser' => function ($q) {
                        $q->select(['id', 'nickname', 'avatar', 'signature', 'verify', 'verify_info']);
                    }, 'belongsToFolder' => function ($q) {
                        $q->select(['id', 'name']);
                    }])
                        ->where('recommend', 1)
                        ->active()
                        ->where('status', 1)
                        ->whereIn('id', $template->all())
                        ->get(['id', 'user_id', 'folder_id', 'name', 'intro', 'cover', 'preview_address', 'count', 'time_add', 'duration', 'storyboard_count']);

                    if ($templates->count()) {
                        $templates = $templates->random(1);
                        $templates = $this->templateDiscoverTransformer->ptransform($templates->all());
                        $data[] = $templates;
                    }
                }

                $active = Topper::where('type', '=', 3)
                    ->orderBy('create_at', 'desc')
                    ->where('closing_time', '>', time())
                    ->pluck('works_id');

                if ($active->all()) {
                    $activity = Activity::with(['belongsToUser' => function ($q) {
                        $q->select('id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info');
                    }, 'hasManyTweets' => function ($q) {
                        $q->select(['tweet_id', 'screen_shot']);
                    }])
                        ->recommend()
                        ->whereIn('id', $active->all())
                        ->ofExpires()
                        ->get(['id', 'user_id', 'comment', 'location', 'icon', 'recommend_expires', 'time_add']);

                    if ($activity->count()) {
                        $activity = $activity->random(1);
                        $activity = $this->activityDiscoverTransformer->transformCollection($activity->all());
                        $data[] = $activity;
                    }
                }

                //动态
                $twe = Topper::where('type', '=', 4)
                    ->orderBy('create_at', 'desc')
                    ->where('closing_time', '>', time())
                    ->pluck('works_id');

                    if ($twe->all()) {
                        $tweets = Tweet::with(['belongsToManyChannel' => function ($q) {
                            $q->select(['name']);
                        }, 'hasOneContent' => function ($q) {
                            $q->select(['content', 'tweet_id']);
                        }, 'belongsToUser' => function ($q) {
                            $q->select(['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']);
                        }])
                            ->whereIn('id', $twe->all())
                            ->get(['id', 'user_id', 'type','video_m3u8','norm_video','high_video','join_video','transcoding_video','created_at', 'duration', 'screen_shot', 'location', 'browse_times', 'like_count', 'reply_count', 'video']);

                        // 过滤
                        if ($tweets->count()) {
                            $tweets = $tweets->random(1);
                            $tweets_data = $this->channelTweetsTransformer->transformCollection($tweets->all());
                            $data[] = $tweets_data;
                        }
                    }

                return  response()->json([
                    'data'=> $data ? [array_random($data)[0]]: [],
                ],200);

    }
}
