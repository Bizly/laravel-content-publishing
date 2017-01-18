<?php
/**
 * class ContentPublishingServiceProvider
 *
 * @package    namespace Bizly\ContentPublishing;
 * @version    1.0.0
 * @author     Tor Miller
 * @copyright  (c) 2015-2017, Bizly, Inc., All Rights Reserved
 */

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

        //TODO: Add Necessary migrations for versioning here:
        // $this->publishes([
        //     base_path('vendor/bizly/laravel-content-publishing/database/migrations') => base_path('database/migrations'),
        // ], 'migrations');

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
