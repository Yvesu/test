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
        $province = $request->get('province');

        //市
        $city = $request ->get('city');

        if(!empty($city)) {
            //按市推送
            $data = Topper::where('city', '=', $city)
                ->where('closing_time', '>', time())
                ->orderBy('create_at', 'desc')
                ->first();

            if (!empty($data)) {
                return response()->json($data,200);
            }
        }

        if(!empty($province)) {
            //按省推荐
            $data = Topper::where('province', '=', $province)
                ->where('type','=',1)
                ->where('closing_time', '>', time())
                ->orderBy('create_at', 'desc')
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
                        ->first();

                    if (!empty($net_work)){
                        $arr['icon']  = $net_work->toArray()['icon'];
                        $arr['addr']  = $net_work->toArray()['addr'];
                        $arr['describe']  = $net_work->toArray()['describe'];
                        $arr['create_at']  = $net_work->toArray()['create_at'];
                        $arr['style']  = 4;
                        $data[][] = $arr;
                    }

                    // 模板
                    if ($tem = Topper::where('type','=',2)->where('closing_time','>',time())->orderBy('create_at','desc')->first()) {
                        $templates = MakeTemplateFile::with(['belongsToUser'=>function($q){
                            $q->select(['id','nickname','avatar','signature','verify','verify_info']);
                        },'belongsToFolder'=>function($q){
                            $q->select(['id','name']);
                        }])
                            ->where('id','=',$tem->works_id)
                            ->where('recommend', 1)
                            ->active()
                            ->where('status', 1)
                            ->get(['id', 'user_id','folder_id', 'name', 'intro', 'cover', 'preview_address', 'count', 'time_add','duration','storyboard_count']);

                        if($templates -> count()) {
                            $templates = $templates -> random(1);

                            $templates = $this -> templateDiscoverTransformer -> ptransform($templates->all());

                            $data[] = $templates;
                        }
                    }

                    // 竞赛
                    if ($act = Topper::where('type','=',3)->where('closing_time','>',time())->orderBy('create_at','desc')->first()) {
                        $activity = Activity::with(['belongsToUser', 'hasManyTweets'])
                            ->where('id','=',$act->works_id)
                            ->ofExpires()
                            ->recommend()
                            ->get(['id', 'user_id', 'comment', 'location', 'icon', 'recommend_expires', 'time_add']);

                        if($activity -> count()) {

                            $activity = $activity -> random(1);

                            $activity = $this -> activityDiscoverTransformer -> transformCollection($activity->all());

                            $data[]  = $activity;
                        }

                    }


                    //动态
                    if ($twe = Topper::where('type','=',4)->where('closing_time','>',time())->orderBy('create_at','desc')->pluck('works_id')) {

                        $tweets = Tweet::whereIn('id',$twe->all())
                            ->whereType(0)->with(['belongsToManyChannel' => function ($q) {
                            $q->select('name');
                        }])->selectListPageByWithAndWhereAndhas(
                            [['hasOneContent', ['content', 'tweet_id']], ['belongsToUser', ['id', 'nickname', 'avatar', 'cover', 'verify', 'signature', 'verify_info']]],
                            ['hasOneHot'],
                            [['active', 1]],
                            [],
                            []);

                        // 过滤
                        if($tweets->count()) {

                            $tweets = $tweets->random(1);

                            $tweets_data = $this->channelTweetsTransformer->ptransform($tweets->all());

                            $data[] = $tweets_data;
                        }

                    }

                    $count = count($data)-1;

                    if ($count){
                        $number = rand(0,$count);

                        $rand = $data[$number];

                        if ($rand){
                            return response()->json($rand[0],200);
                        }else{
                            return response()->json(['error' => 'Content is not found'],404);
                        }

                    }else{
                        return response()->json(['error' => 'Content is not found'],404);

                    }
    }
}
