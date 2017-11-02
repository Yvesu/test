<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;
class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        /**
         * 后台全页面共享的数据
         */
        view()->composer(
            'admin.layer','App\Http\ViewComposers\Admin\LayerProfileComposer'
        );

        /**
         * 家族设置页面共享的数据
         */
        view()->composer(
            'admin.management.family.setting','App\Http\ViewComposers\Admin\Management\Family\SettingProfileComposer'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
