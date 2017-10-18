<?php
namespace App\Services;

use App\Models\GoldAccount;
use App\Models\GoldTransaction;
use App\Models\ActivityBonusSet;
use App\Models\Config;
use App\Models\User;
use App\Models\Subscription;
use App\Models\CompanyAccountIncomeLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

class GoldTransactionService
{

    /**
     * 按排序分配一定奖金的数组
     * @param $count 分奖金的用户量
     * @param $bonus 总奖金金额
     * @return array
     */
    public function bonusAllocation($count,$bonus)
    {

        // 获取奖金分配的各项参数
        $bonusSet = ActivityBonusSet::active()->get(['level','count_user','amount','prorata']);

        // 不同级别的奖金分配比例
        $format_amount = $bonusSet -> pluck('amount','level');

        // type 为4时的分配金额 前50名都有 最小单位为1，舍去法取整
        $bonus_four = floor($format_amount[4]*$bonus/$count);

        $bonus_user = [];

        for($i=1; $i <= $count; $i++){

            $bonus_user[] = $bonus_four + ($i<=3 ? $format_amount[$i]*$bonus : 0);
        }

        return $bonus_user;
    }

    /**
     * 用户金币流动
     * @param int $user_to 接收金币的用户id
     * @param int $user_from 支出金币用户id
     * @param int $style 金币流动的类型
     * @param int $style_id 金币流动的类型所在的id
     * @param int $num 金币数量
     * @param string $intro 金币流动的介详细绍
     * @param int $share 是否开启分成 0关闭1开启
     * @param int $type 添加金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
    public function transaction($user_to, $user_from, $style, $style_id, $num = 0, $intro = '', $share = 1, $type = 0)
    {
        return $this -> charge($user_to, $user_from, $style, $style_id, $num, $intro, $share, $type);
    }

    /**
     * 用户收入金币
     * @param int $user_to 接收金币的用户id
     * @param int $user_from 支出金币用户id
     * @param int $num 金币数量
     * @param string $intro 金币添加的介绍
     * @param int $type 添加金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
//    public function income($user_to, $user_from, $num = 0, $intro = '', $type = 0)
//    {
//        return $this -> charge($user_to, $user_from, $num, $intro, $type);
//    }

    /**
     * 用户减少金币
     * @param int $user_to 接收金币的用户id
     * @param int $user_from 支出金币用户id
     * @param int $style 金币流动的类型
     * @param int $style_id 金币流动的类型所在的id
     * @param int $num 金币数量
     * @param string $intro 金币减少的介绍
     * @param int $type 减少金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
//    public function expend($user_to, $user_from, $num = 0, $intro = '', $type = 0)
//    {
//        return $this -> charge($user_to, $user_from, $num, $intro, $type);
//    }

    /**
     * 给用户添加金币
     * @param int $user_to 接收金币的用户id
     * @param int $user_from 支出金币用户id
     * @param int $style 金币流动的类型
     * @param int $style_id 金币流动的类型所在的id
     * @param int $num 金币数量
     * @param string $intro 金币添加的介绍
     * @param int $share 是否开启分成 0关闭1开启
     * @param int $type 添加金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
    private function charge($user_to, $user_from, $style, $style_id, $num = 0, $intro = '', $share = 1, $type = 0)
    {
        try{
            // 获取增加金币用户集合
            $gold_add = GoldAccount::where('user_id',$user_to)->firstOrFail();

            // 获取支出金币用户集合,排除系统账户
            if(100000 !== $user_from){

                $gold_minus = GoldAccount::where('user_id',$user_from)->firstOrFail();

                // 判断可用金币数量,可用金币不足
                if(0 === $type && $gold_minus -> gold_avaiable < $num) return 2;

                if(1 === $type && $gold_minus -> gold_frozen < $num) return 2;
            }

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            // 修改收入金币账户集合
            $gold_add -> update([
                'gold_total'    => $num + $gold_add -> gold_total,
                'gold_avaiable' => 0 === $type ? $num : 0 + $gold_add -> gold_avaiable,
                'gold_frozen'   => 0 === $type ? 0 : $num + $gold_add -> gold_frozen,
                'time_update'   => $time
            ]);

            // 修改支出金币账户集合,排除系统账户
            if(100000 !== $user_from){
                $gold_minus -> update([
                    'gold_total'    => $gold_minus -> gold_total - $num,
                    'gold_avaiable' => 0 === $type ? $gold_minus -> gold_avaiable - $num : $gold_minus -> gold_avaiable,
                    'gold_frozen'   => 1 === $type ? $gold_minus -> gold_frozen - $num : $gold_minus -> gold_frozen ,
                    'time_update'   => $time
                ]);
            }

            // 判断是否开启分成模式,并且非系统账号 将金币分予关注者
            if(1 === $share && 100000 !== $user_to) {

                // 获取分成比例
                $gold_proportion = Config::active()->firstOrFail(['gold_proportion'])->gold_proportion * 0.01;
                $this -> shareRevenue($user_to, $gold_proportion * $num, $style_id);
            }

            // 保存日志
            GoldTransaction::create([
                'user_to'   => $user_to,
                'user_from' => $user_from,
                'num'       => $num,
                'style'     => $style,
                'style_id'  => $style_id,
                'intro'     => $intro,
                'type'      => $type,
                'time_add'  => $time,
                'time_update'  => $time
            ]);

            // 事务提交
            DB::commit();

            return 1;
        } catch (ModelNotFoundException $e) {
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 所获奖金分成 -- 将部分所得分予他的部分关注者或平台
     * @param int $user_id 被分金币的用户id
     * @param $bonus   所分金币的金额
     * @return bool|void
     */
    private function shareRevenue($user_id, $bonus, $style_id)
    {
        try {
            // 检查用户是否存在
            $user = User::findOrFail($user_id);

            // 获取用户的关注
            $attention_ids = Subscription::where('from', $user_id)->pluck('to')->all();

            // 判断总金额是否小于 1
            if($bonus < 1) return true;

            $time = getTime();

            // 判断用户是否有关注人数，如果没有，将20%金币转予平台
            if (!$count = count($attention_ids)) {

                $this -> transaction(100000, $user_id, 2, $style_id, $bonus, '支出所得赛事金币分成', 0, 0);

                // 存入公司收入日志
                CompanyAccountIncomeLog::create([
                    'user_id'   => $user_id,
                    'type'      => 0,
                    'type_id'   => $user_id,    // 被分金币的用户id
                    'num'       => $bonus,
                    'intro'     => '获取用户“' . $user_id . '”所得赛事金币分成',
                    'time_add'  => $time,
                ]);

                return true;
            }

            // 赛事介绍
            $intro = '粉丝“' . $user->nickname . '”所得赛事金币分成';

            // 开启事务
            DB::beginTransaction();

            // 判断关注者是否只有一个人
            if(1 == $count){

                // 分配金额
                $this -> transaction($attention_ids[0], $style_id, 2, $user_id, $bonus, $intro, 0, 0);

            } else {

                // 每位关注者所得金币 ,自定义的全局变量
                $attention_bonus = floor($bonus/$count);

                // 判断每个人获取的金币是否小于 1
                if($bonus < $count){

                    // 进行随机分配，获取随机用户id,$bonus为可分数量
                    $attention_ids = array_slice(shuffle($attention_ids),0,$bonus);

                    // 金币金额为 1
                    $attention_bonus = 1;
                }

                // 将金币进行平分
                foreach($attention_ids as $attention_id){

                    // 分配金额
                    $this -> transaction($attention_id, $user_id, 2, $style_id, $attention_bonus, $intro, 0, 0);
                }
            }

            // 提交事务
            DB::commit();

            return true;

        }catch(ModelNotFoundException $e){

            // 事务回滚
            DB::rollBack();

            return false;
        }catch(\Exception $e){

            // 事务回滚
            DB::rollBack();

            return false;
        }
    }
}