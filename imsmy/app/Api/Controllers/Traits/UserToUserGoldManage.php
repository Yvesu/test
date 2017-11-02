<?php
namespace App\Api\Controllers\Traits;

use App\Models\GoldAccount;
use App\Models\GoldTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;

/**
 * 用户与用户之间的金币流通
 *
 * Class UserToUserGoldManage
 * @package App\Api\Controllers\Traits
 */
trait UserToUserGoldManage
{

    /**
     * 用户收入金币
     * @param int $user_to 接收金币的用户id
     * @param int $user_from 支出金币用户id
     * @param int $num 金币数量
     * @param string $intro 金币添加的介绍
     * @param int $type 添加金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
    public function incomeGold($user_to, $user_from, $num = 0, $intro = '', $type = 0)
    {
        return $this -> charge($user_to, $user_from, $num, $intro, $type);
    }

    /**
     * 用户减少金币
     * @param int $user_from 支出金币用户id
     * @param int $user_to 接收金币的用户id, 默认为系统
     * @param int $num 金币数量
     * @param string $intro 金币减少的介绍
     * @param int $type 减少金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
    public function expendGold($user_from, $user_to = 100000, $num = 0, $intro = '', $type = 0)
    {
        return $this -> charge($user_to, $user_from, $num, $intro, $type);
    }

    /**
     * 给用户添加金币
     * @param int $user_to 接收金币的用户id
     * @param int $user_from 支出金币用户id
     * @param int $num 金币数量
     * @param string $intro 金币添加的介绍
     * @param int $type 添加金币类型 0可用1冻结
     * @return int 返回值 0失败，1成功
     */
    private function charge($user_to, $user_from, $num = 0, $intro = '', $type = 0)
    {
        try {
            // 获取增加金币用户集合
            $gold_add = GoldAccount::where('user_id', $user_to)->firstOrFail();

            // 获取支出金币用户集合
            $gold_minus = GoldAccount::where('user_id', $user_from)->firstOrFail();

            // 判断可用金币数量,可用金币不足
            if (0 === $type && $gold_minus->gold_avaiable < $num) return 2;

            if (1 === $type && $gold_minus->gold_frozen < $num) return 2;

            $time = getTime();

            // 开启事务
            DB::beginTransaction();

            // 修改收入金币账户集合
            $gold_add->update([
                'gold_total' => $num + $gold_add->gold_total,
                'gold_avaiable' => 0 === $type ? $num : 0 + $gold_add->gold_avaiable,
                'gold_frozen' => 0 === $type ? 0 : $num + $gold_add->gold_frozen,
                'time_update' => $time
            ]);

            // 修改支出金币账户集合
            $gold_minus->update([
                'gold_total' => $gold_minus->gold_total - $num,
                'gold_avaiable' => 0 === $type ? $gold_minus->gold_avaiable - $num : $gold_minus->gold_avaiable,
                'gold_frozen' => 1 === $type ? $gold_minus->gold_frozen - $num : $gold_minus->gold_frozen,
                'time_update' => $time
            ]);

            // 保存日志
            GoldTransaction::create([
                'user_to' => $user_to,
                'user_from' => $user_from,
                'num' => $num,
                'intro' => $intro,
                'type' => $type,
                'time_add' => $time,
                'time_update' => $time
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
}