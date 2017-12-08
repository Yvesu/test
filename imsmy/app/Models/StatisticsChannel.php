<?php
/**
 * Created by PhpStorm.
 * User: Ma biao
 * Date: 2016/5/24
 * Time: 10:14
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StatisticsChannel extends Model
{
    protected  $table = 'statistics_channel';

    protected $fillable = [
        'channel_id',
        'forwarding_time',
        'comment_time',
        'work_count',
        'created_at'
    ];

    public $timestamps = false;
}