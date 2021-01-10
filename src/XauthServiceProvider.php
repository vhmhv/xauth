<?php

namespace vhmhv\Xauth;

use Illuminate\Support\ServiceProvider;
use vhmhv\Xauth\Commands\XauthCommand;

class XauthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/xauth.php' => config_path('xauth.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/xauth'),
            ], 'views');

            $migrationFileName = 'create_xauth_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                XauthCommand::class,
            ]);

            $this->loadRoutesFrom('login_routes.php');

        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'xauth');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/xauth.php', 'xauth');
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
