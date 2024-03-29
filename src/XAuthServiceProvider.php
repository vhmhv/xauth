<?php

namespace vhmhv\Xauth;

use Illuminate\Support\ServiceProvider;

class XAuthServiceProvider extends ServiceProvider
{
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class.'@handle',
            \SocialiteProviders\Apple\AppleExtendSocialite::class.'@handle',
        ],
    ];

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/login_routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../config/xauth.php' => config_path('xauth.php'),
                ],
                'config'
            );

            $this->publishes(
                [
                    __DIR__ . '/../resources/views' => base_path('resources/views/vendor/xauth'),
                ],
                'views'
            );
        }
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'xauth');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xauth.php', 'xauth');
        $this->app->register(EventServiceProvider::class);
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path('migrations/*.php')) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
