<?php

namespace Bizly\ContentPublishing;

use Illuminate\Support\ServiceProvider;

class ContentPublishingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/bizly.content-publishing.php' => config_path('bizly.content-publishing.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
