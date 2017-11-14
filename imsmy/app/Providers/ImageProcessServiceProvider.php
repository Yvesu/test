<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ImageProcess;
class ImageProcessServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('image_process',function(){
            return new ImageProcess();
        });
    }
}
