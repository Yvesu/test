<?php
namespace App\Models;

/**
 * 公司收入
 * Class CompanyAccountIncomeLog
 * @package App\Models
 */
class CompanyAccountIncomeLog extends Common
{
    protected  $table = 'zx_company_account_income_log';

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