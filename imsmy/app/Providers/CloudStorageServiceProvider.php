<?php

namespace App\Providers;

use App\Services\CloudStorage;
use Illuminate\Support\ServiceProvider;

class CloudStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cloud_storage',function(){
            return new CloudStorage();
        });
    }
}
