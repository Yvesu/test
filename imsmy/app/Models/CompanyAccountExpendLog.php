<?php
namespace App\Models;

/**
 * 公司账户支出表
 * Class CompanyAccountExpendLog
 * @package App\Models
 */
class CompanyAccountExpendLog extends Common
{
    protected  $table = 'zx_company_account_expend_log';

    protected $fillable = [
        'user_id',
        'type',
        'type_id',
        'num',
        'intro',
        'time_add',
    ];

    public $timestamps = false;

}