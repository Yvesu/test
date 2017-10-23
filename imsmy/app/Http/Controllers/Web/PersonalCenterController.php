<?php
namespace App\Http\Controllers\Web;

use CloudStorage;
use App\Http\Controllers\Controller;
use App\Models\GoldTransaction;
use App\Models\Tweet;
use App\Http\Transformer\PersonalTweetTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class PersonalCenterController extends Controller
{
    protected $personalTweetTransformer;

    protected $paginate = 20;

    public function __construct(
        PersonalTweetTransformer $personalTweetTransformer
    )
    {
        $this->personalTweetTransformer = $personalTweetTransformer;
    }

    /**
     * 用户中心首页信息
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index()
    {
        try{

            // 用户信息
//            $user = session('users');
            $user = session('users',User::find(1000240));   // TODO 临时测试，待删除

            $info = User::with(['hasOneGoldAccount' => function($q){
                $q -> select('id','user_id','gold_total');
            }])
                -> findOrFail($user -> id, ['id', 'verify', 'last_ip', 'last_token', 'like_count', 'browse_times', 'avatar', 'nickname', 'created_at', 'signature', 'sex', 'fans_count', 'new_fans_count', 'follow_count', 'work_count']);

            $info -> avatar = CloudStorage::downloadUrl($info -> avatar);

            // 测试接口，待删除
            return response() -> json($info, 200);

        } catch (ModelNotFoundException $e) {
            return response() -> json(['error' => 'not_found', 404]);
        } catch (\Exception $e) {
            return response() -> json(['error' => 'not_found', 404]);
        }
    }

    // 用户中心的tweet
    public function tweet(Request $request)
    {
        try{

            // 用户信息
//            $user = session('users');
            $user = session('users',User::find(1000240));   // TODO 临时测试，待删除

            // 类型，1'我的热门', 2'好友动态', 3'为您推荐'     测试
            $type = $request -> get('type');

            if(!in_array($type, [1,2,3]))
                return response() -> json(['error' => 'bad_request', 403]);

            $title = [1=>'我的热门', 2=>'好友动态', 3=>'为您推荐'];

            switch($type){
                case 1:
                    $data = $this -> hot_tweet($user -> id);
                    break;
                case 2:
                    $data = $this -> friends_tweet($user -> id);
                    break;
                case 3:
                    $data = $this -> recommend_tweet();
                    break;
            }

            // 测试接口，待删除
            return [
                'title' => $title[$type],
                'data'  => count($data) ? array_slice($data,0,3) : [],
            ];

        } catch (ModelNotFoundException $e) {
            return response() -> json(['error' => 'not_found', 404]);
        } catch (\Exception $e) {
            return response() -> json(['error' => 'not_found', 404]);
        }
    }

    /**
     * 用户中心首页信息  粉丝贡献
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function contribution(Request $request)
    {
        try{

            // 用户信息
//            $user = session('users');
            $user = session('users',User::find(1000240));   // TODO 临时测试，待删除

            // 类型，1'粉丝贡献榜', 2'月排行榜'
            $type = $request -> get('type');

            if(!in_array($type, [1,2]))
                return response() -> json(['error' => 'bad_request', 403]);

            $title = [1 => '粉丝贡献榜', 2 => ltrim(date('m',time()),0).'月'];

            switch($type){
                case 1:
                    // 从缓存中获取用户的贡献榜,保存时间120分钟
                    $data = $this -> fans_contribution('all', $user -> id);
                    $data = $data -> count() ? $data -> take(3) -> values() -> all() : (object)null;
                    break;
                case 2:
                    // 从缓存中获取用户的本月排行榜
                    $data = $this -> fans_contribution('month', $user -> id);

                    $data = $data -> count() ? $data -> take(10) -> values() -> all() : (object)null;
                    break;
            }

            // 测试接口，待删除
            return [
                'title' => $title[$type],
                'data'  => $data,
            ];

        } catch (ModelNotFoundException $e) {
            return response() -> json(['error' => 'not_found', 404]);
        } catch (\Exception $e) {
            return response() -> json(['error' => 'not_found', 404]);
        }
    }

    /**
     * 用户的热门动态
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hot(Request $request){

        // 获取用户id
//        $user = session('users');
        $user = session('users',User::find(1000240));   // TODO 临时测试，待删除

        // 调用接口，获取数据
        $tweet = $this -> hot_tweet($user->id, (int)$request -> get('page', 1));

        return response() -> json(['data' => $tweet], 200);
        
        return view('web/users/hot_tweet', ['tweet' => $tweet]);
    }

    /**
     * 用户的好友动态
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function friends(Request $request){

        // 获取用户id
//        $user = session('users');
        $user = session('users',User::find(1000240));   // TODO 临时测试，待删除

        // 调用接口，获取数据
        $tweet = $this -> friends_tweet($user->id, (int)$request -> get('page', 1));

        return response() -> json(['data' => $tweet], 200);

        return view('web/users/friends_tweet', ['tweet' => $tweet]);
    }

    /**
     * 用户的推荐动态
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function recommend(Request $request){

        // 调用接口，获取数据
        $tweet = $this -> recommend_tweet((int)$request -> get('page', 1));

        return response() -> json(['data' => $tweet], 200);

        return view('web/users/recommend_tweet', ['tweet' => $tweet]);
    }

    /**
     * 为上面调用缓存数据使用
     *
     * @param $type string 缓存类型
     * @param $user_id int 用户id
     * @return mixed
     */
    protected function fans_contribution($type, $user_id){

        // 根据类型判断缓存数据类型
        $type_cache = 'month' == $type ? 'contribution_list_month_' : 'contribution_list_all_';

        // 获取数据
        $contribution = Cache::remember($type_cache.$user_id, 120, function() use($user_id, $type) {

            // 获取给该用户贡献金币的所有用户信息
            if('month' == $type) {
                // 月度
                $gold_users = GoldTransaction::where('user_to', $user_id)
                    -> where('time_add', '>=', strtotime(date('Y-m', getTime())))
                    -> get(['user_from', 'num'])
                    -> groupBy('user_from');
            } else {
                // 总体
                $gold_users = GoldTransaction::where('user_to', $user_id)
                    -> get(['user_from', 'num'])
                    -> groupBy('user_from');
            }

            $sort = [];

            foreach($gold_users as $key => $value){

                $sort[$key] = $value -> sum -> num;
            }

            $contribution_users = User::status() -> whereIn('id', array_keys($sort)) -> get(['id', 'avatar', 'verify', 'nickname']);

            foreach($contribution_users as $key => $value){
                $value -> avatar = CloudStorage::downloadUrl($value -> avatar);
                $value -> gold = $sort[$value -> id];
            }

            return $contribution_users -> sortByDesc('gold');
        });

        return $contribution;
    }

    /**
     * 自己的热门动态，供其他接口调用数据使用
     *
     * @param int $user_id 用户id
     * @param int $page 页码
     * @return mixed
     */
    protected function hot_tweet($user_id, $page=1)
    {

        $tweet = Cache::remember('tweet_hot_'.$user_id.'_'.$page, 120, function() use($user_id, $page) {

            $tweet = Tweet::with(['hasOneContent' => function($q){
                $q -> select('tweet_id', 'content');
            }]) -> where('original', 0)
                -> ofFriendTweets($user_id)
                -> where('user_id', $user_id)
                -> orderByDesc('browse_times')
                -> get(['id', 'screen_shot', 'browse_times', 'video', 'tweet_grade_total', 'tweet_grade_times', 'created_at'])
                -> forPage($page, $this->paginate);

            return $this->personalTweetTransformer->transformCollection($tweet->all());
        });

        return $tweet;
    }

    /**
     * 自己的好友动态，供其他接口调用数据使用
     *
     * @param int $user_id 用户id
     * @param int $page 页码
     * @return mixed
     */
    protected function friends_tweet($user_id, $page=1)
    {

        $tweet = Cache::remember('tweet_friends_'.$user_id.'_'.$page, 120, function() use($user_id, $page) {

            $tweet = Tweet::whereHas('belongsToFriend', function($q) use ($user_id) {
                $q -> where('to', $user_id);
            })
                -> with(['belongsToUser' => function($q){
                    $q -> select('id', 'nickname');
                }, 'hasOneContent' => function($q){
                    $q -> select('tweet_id', 'content');
                }])
                -> where('original', 0)
                -> ofFriendTweets($user_id)
                -> orderByDesc('id')
                -> get(['id', 'user_id', 'browse_times', 'tweet_grade_total', 'tweet_grade_times', 'created_at'])
                -> forPage($page, $this->paginate);

            // 计算分数平均值
            return $this->personalTweetTransformer->transformCollection($tweet->all());
        });

        return $tweet;
    }

    /**
     * 推荐动态，供其他接口调用数据使用
     *
     * @param int $page 页码
     * @return mixed
     */
    protected function recommend_tweet($page=1)
    {
        $tweet = Cache::remember('tweet_recommend_'.$page, 1, function() use($page) {
            $tweet = Tweet::with(['belongsToUser' => function($q) {
                $q -> select('id', 'nickname');
            }, 'hasOneContent' => function($q){
                $q -> select('tweet_id', 'content');
            }])
                -> where('original', 0)
                -> whereVisible(0)
                -> orderByDesc('recommend_expires')
                -> orderByDesc('id')
                -> get(['user_id', 'id', 'browse_times', 'tweet_grade_total', 'tweet_grade_times', 'created_at'])
                -> forPage($page, $this->paginate);

            // 计算分数平均值
            return $this->personalTweetTransformer->transformCollection($tweet->all());
        });

        return $tweet;
    }

}