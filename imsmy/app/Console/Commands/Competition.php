<?php

namespace App\Console\Commands;

use app\Models\Notification;
use App\Models\TweetActivity;
use Illuminate\Console\Command;
use App\Models\{Activity,Subscription,ActivityParticipationOrder,CompanyAccountIncomeLog,ActivityBonusSet};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\GoldTransactionService;
use DB;

class Competition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competition:over';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Competition Bonus Allocation And Order';

    /**
     * 金币分配实例化
     * @var
     */
    protected $goldTransacton;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this -> goldTransacton = new GoldTransactionService();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('Start Channel Ranking');

        $this -> allocation();

        \Log::info('End Channel Ranking');
    }

    /**
     * 第一层：所有赛事 进行金币分配
     */
    public function allocation()
    {
        try{

            // 获取所有已结束未分配金币的赛事
            $activities = Activity::active()->ofOver()->whereStatus(0)->get();

            // 时间
            $time = getTime();

            // 判断是否有符合的赛事
            if(!$activities->count()) return;

            // 获取金币分配的各项参数
            $bonusSet = ActivityBonusSet::active()->get(['level','count_user','amount','prorata']);

            // 参与金币分配的用户总数量
            $users_count = $bonusSet -> sum('count_user');

            // 将多个赛事保存
            foreach($activities as $activity){

                // 每个赛事下面的有赏金的用户id
                $allocation = TweetActivity::with(['hasOneUser' => function($q){
                    $q -> select('id');
                }])
                    -> where('activity_id',$activity -> id)
                    -> orderBy('like_count','DESC')
                    -> take($users_count)
                    -> get();

                // 判断参与作品数量，如果小于5个，赛事将不成功，原金额将返回发布者账户
                $count = $allocation -> count();

                DB::beginTransaction();

                if($count >= 5){

                    // 赛事成功，将金币进行分配
                    $this -> bonus($activity, $count, $allocation, $time, $bonusSet);

                } else {

                    // 赛事失败
                    $activity -> status = 2;

                    // 赛事介绍
                    $intro = '赛事因参与作品少于5个而失败，现退回金币';

                    // 回退金额，100000为系统账号id，不参与分成
                    $this -> goldTransacton -> transaction($activity -> user_id, 100000, 9, $activity -> id, $activity -> bonus, $intro, 0, 0);

                    $activity -> save();

                    // 给参赛者发送提醒消息
                    foreach($allocation as $user){

                        // 提醒消息
                        Notification::create([
                            'user_id'           => 100000,
                            'notice_user_id'    => $user -> hasOneUser -> id,
                            'type'              => 8,   // 金币方面
                            'type_id'           => $activity -> id,   // 赛事id
                            'intro'             => '您参与的赛事'.$activity->comment.'因参与作品少于5个而失败',
                        ]);
                    }
                }

                DB::commit();

                \Log::error('赛事金币分配成功',['id' => $activity -> id]);
            }

        } catch (ModelNotFoundException $e) {
            DB::rollback();
            abort(404);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('赛事金币分配失败',['error' => $e->getMessage()]);
//            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * 第二层：所有赛事 -- 单个赛事 金币分配予前50名
     * @param $activity    赛事集合
     * @param $user_count  分配金币的用户数量
     * @param $allocation  分配金币的用户id
     * @param $time        时间戳
     * @param $bonusSet    金币后台配置参数
     * @return bool
     */
    private function bonus($activity, $user_count, $allocation, $time, $bonusSet)
    {
        try{
            // 不同级别的金币分配比例
            $format_amount = $bonusSet -> pluck('amount','level');

            // 不同级别的人员数量
            $format_user = $bonusSet -> pluck('count_user','level');

            // type 为4时的分配金额 前50名都有
            $bonus_four = floor($format_amount[4]*$activity->bonus/$user_count);

            // 获取金币介绍
            $intro = '参与赛事“'.$activity -> comment.'”所得金币';

            foreach($allocation as $key => $user){

                // 从1开始
                ++$key;

                # 根据用户级别进行分配金币
                // 第一级
                if($key <= $format_user[1]){

                    $level = 1;
                    $bonus_user = $bonus_four + floor($format_amount[1]*$activity->bonus);
                    // 第二级
                } elseif ($key <= $format_user[1]+$format_user[2]){

                    $level = 2;
                    $bonus_user = $bonus_four + floor($format_amount[2]*$activity->bonus);
                    // 第三级
                } elseif ($key <= $format_user[1]+$format_user[2]+$format_user[3]){

                    $level = 3;
                    $bonus_user = $bonus_four + floor($format_amount[3]*$activity->bonus);
                } elseif ($key <= $format_user[1]+$format_user[2]+$format_user[3]+$format_user[4]){

                    $level = 4;
                    $bonus_user = $bonus_four;
                }

                // 每位参加用户创建赛事 订单
                ActivityParticipationOrder::create([
                    'activity_id'   => $activity -> id,
                    'user_id'       => $user->hasOneUser->id,
                    'bonus'         => $bonus_user,
                    'level'         => $level,
                    'time_add'      => $time,
                    'time_update'   => $time,
                ]);

                # 先将全部金币存入获奖者账户，再将 20% 分予他的关注者
                // 金币金额的 100% 先存入金币获得者账户，100000为系统账号id
                $this -> goldTransacton -> transaction($user->hasOneUser->id, 100000, 3, $activity -> id, $bonus_user, $intro, 1, 0);
            }

            // 修改赛事状态为完成
            $activity -> update(['status' => 1]);
        } catch (ModelNotFoundException $e) {
            return false;
        } catch (\Exception $e) {
            \Log::error('赛事'.$activity -> id.'金币分成失败',['error' => $e->getMessage()]);
            return false;
        }
    }
}
