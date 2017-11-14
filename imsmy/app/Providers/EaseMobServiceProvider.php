<?php

namespace App\Providers;

use App\Services\EaseMob;
use Illuminate\Support\ServiceProvider;

class EaseMobServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('ease_mob',function(){
            return new EaseMob([
                'client_id'     => 'YXA6s_BQYBD9Eea0TeO-SbA2gA',
                'client_secret' => 'YXA6McICdduGldqPtYiFBUPDtmH0Czc',
                'org_name'      => 'goobird',
                'app_name'      => 'goobird'
            ]);
        });
    }
}
