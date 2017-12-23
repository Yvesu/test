<?php

namespace App\Api\Controllers;

use App\Api\Transformer\ParticipationActivityTransformer;
use App\Api\Transformer\SearchTopicsTransformer;
use App\Api\Transformer\UsersWithFansTransformer;
use App\Api\Transformer\TopicSimplyTransformer;
use App\Api\Transformer\CompetitionTransformer;
use App\Api\Transformer\Discover\HotActivityTransformer;
use App\Models\TweetActivity;
use App\Models\Activity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Validator;

/**
 * 赛事相关接口
 *
 * @Resource("Topics")
 */
class CompetitionController extends BaseController
{
    // 页码
    protected $paginate = 20;

    private $searchTopicsTransformer;
    private $usersWithFansTransformer;
    private $topicSimplyTransformer;
    private $competitionTransformer;

    // 热门赛事 活动
    protected $hotActivityTransformer;

    // 参与赛事
    protected $participationActivityTransformer;

    public function __construct(
        SearchTopicsTransformer $searchTopicsTransformer,
        UsersWithFansTransformer $usersWithFansTransformer,
        TopicSimplyTransformer $topicSimplyTransformer,
        CompetitionTransformer $competitionTransformer,
        HotActivityTransformer $hotActivityTransformer,
        ParticipationActivityTransformer $participationActivityTransformer
    )
    {
        $this->searchTopicsTransformer = $searchTopicsTransformer;
        $this->usersWithFansTransformer = $usersWithFansTransformer;
        $this->topicSimplyTransformer = $topicSimplyTransformer;
        $this->competitionTransformer = $competitionTransformer;
        $this->hotActivityTransformer = $hotActivityTransformer;
        $this->participationActivityTransformer = $participationActivityTransformer;
    }

    /**
     * 保存发布的赛事
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert($id,Request $request)
    {
        try{
            // 验证接收数据格式是否正确
            $validator = Validator::make($request->all(), [
                'comment'  => 'required|max:255',
                'bonus'    => 'required',
                'expires'  => 'required|numeric',

            ]);

            // 获取所有信息
            $input = $request -> only(['comment','expires','icon','location']);

            $bonus = (int)$request->get('bonus');

            if ($validator->fails() || !is_int($bonus) || $bonus < 20) {
                return response()->json(['error'=>'bad_request'],403);
            }

            $time = getTime();

            // 判断该赛事是否已经存在
            $arr = [
                'icon'         => $input['icon']?:'',
                'user_id'      => $id,
                'comment'      => removeXSS($request -> get('comment')),
                'bonus'        => $bonus,
                'location'     => $input['location']===null ?:'',
                'expires'      => $input['expires'],
                'time_add'     => $time,
                'time_update'  => $time,
            ];

            \DB::beginTransaction();

            $competition = Activity::create($arr);

            if ($competition){
                \DB::commit();
                return response()->json(['message'=>'success'],201);
            }else{
                \DB::rollBack();
                return response()->json(['message'=>'failed'],403);
            }

        }catch(ModelNotFoundException $e){
            \DB::rollBack();
            return response()->json(['error'=>'bad_request'],403);
        }catch(\Exception $e){
            \DB::rollBack();
            return response()->json(['error'=>'bad_request'],403);
        }
    }

    /**
     * 赛事详情
     * @param $id 赛事id
     * @return array
     */
    public function details($id)
    {
        try{

            // 获取赛事的详情
            $data = Activity::with('belongsToUser')->findOrFail($id);

            // 返回数据
            return response()->json($this->competitionTransformer->transform($data));

        } catch (ModelNotFoundException $e) {

            return response()->json(['error' => 'bad_request'], 403);
        } catch (\Exception $e) {

            return response()->json(['error' => 'bad_request'], 403);
        }
    }

    /**
     * 判断用户是否已经参与过该赛事 或 赛事是否已经结束
     * @param $id   用户id
     * @param $competition_id
     * @return int
     */
    public function check($id,$competition_id)
    {
        // 该赛事已经结束
        if(getTime() > Activity::findOrFail($competition_id) -> expires)
            return 2;

        // 该用户是否参与过该赛事
        return TweetActivity::where('user_id',$id)->where('activity_id',$competition_id)->first() ? 1 : 0;
    }

    /**
     * 用户发布的赛事
     * @param $id   用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function release($id,Request $request)
    {
        try{
            // 获取赛事（原活动）数据
            $activities = Activity::with([
                'belongsToUser',
                'hasManyTweets' => function($q){
                    $q -> allow();
                }])
                -> where('user_id',$id)
                -> forPage((int)$request->get('page',1),$this->paginate)
                -> orderBy('id','DESC')
                -> get();

            return response()->json([
                'data'  => $activities->count() ? $this->hotActivityTransformer->transformCollection($activities->all()) : [],
                'count' => $this -> paginate
            ],200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 404], 'not_found');
        } catch (\Exception $e) {
            return response()->json(['error' => 404], 'not_found');
        }
    }

    /**
     * 用户参与的赛事
     * @param $id 用户id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function participation($id,Request $request){
        try{
            // 获取赛事（原活动）数据
            $activities = Activity::with([
                'belongsToUser',
                'hasManyTweets' => function($q){
                    $q -> allow();
                }])
                -> whereHas('hasManyTweetActivity',function($query)use($id){
                    $query -> where('user_id',$id);
                })
                -> forPage((int)$request->get('page',1),$this->paginate)
                -> orderBy('id','DESC')
                -> get();

            return response()->json([
                'data'  => $activities->count() ? $this->participationActivityTransformer->transformCollection($activities->all()) : [],
                'count' => $this -> paginate
            ],200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 404], 'not_found');
        } catch (\Exception $e) {
            return response()->json(['error' => 404], 'not_found');
        }
    }

}