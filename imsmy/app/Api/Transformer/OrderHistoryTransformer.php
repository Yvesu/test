<?php
/**
 * Description:
 * User: Yy
 * Date: 2018/3/2 0002
 */

namespace App\Api\Transformer;


class OrderHistoryTransformer extends Transformer
{
    public  function transform($item)
    {
        return[
            'id'    =>  $item->id,
            'order_number'  =>  $item->order_number,
            'money'         =>  (string)($item->money)/100,
            'gold_num'      =>  $item->gold_num,
            'pay_type'      =>  $item->pay_type,
            'status'        =>  $item->status,
            'time_add'      =>  $item->time_add,
        ];
    }
}