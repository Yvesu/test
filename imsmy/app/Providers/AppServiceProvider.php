<?php

namespace App\Providers;

use App\Models\Admin\Department;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Auth;
use App;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 部门添加及更新记录
         */
        /*Department::saved(function($department){
            $date = Carbon::now()->format('Y-m');
            Log::useFiles(App::storagePath().'/logs/Departments/'. $date .'.log','info');
            Log::info('SAVED::Department Attributes:'.json_encode($department->getAttributes()).
                ',AdminID:'.Auth::guard('web')->user()->id
            );
        });*/

        /**
         * 部门删除前记录
         */
        /*Department::deleting(function($department){
            $date = Carbon::now()->format('Y-m');
            Log::useFiles(App::storagePath().'/logs/Departments/'. $date .'.log','info');
            Log::info('DELETING::Department Attributes:'.json_encode($department->getAttributes()).
                ',AdminID:'.Auth::guard('web')->user()->id
            );
        });*/

        //添加验证规则 字符串只能是数字、英文、中文、-_
        Validator::extend('chinese', function($attribute, $value, $parameters) {
            return regex_name($value);
        });

        /**
         * utf8-mb4 索引或主键 长度不能超过191
         */
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Facade to Object binding
        $this->app->bind('commandlog', 'App\Helpers\CommandWriter');
    }
}
