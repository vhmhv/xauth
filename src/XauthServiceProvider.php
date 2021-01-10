<?php

namespace vhmhv\Xauth;

use Illuminate\Support\ServiceProvider;
use vhmhv\Xauth\Commands\XauthCommand;

class XauthServiceProvider extends ServiceProvider
{
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\Graph\GraphExtendSocialite@handle',
        ],
    ];

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/login_routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/xauth.php' => config_path('xauth.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/xauth'),
            ], 'views');

            $migrationFileName = 'extend_users_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
            }
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'xauth');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xauth.php', 'xauth');
        $this->app->register(EventServiceProvider::class);
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
