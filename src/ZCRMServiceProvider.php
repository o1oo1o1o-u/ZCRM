<?php

namespace ZCRM;

use Illuminate\Support\ServiceProvider;
use ZCRM\Commands\ListCrmCommand;
use ZCRM\Commands\RemoveCrmCommand;
use ZCRM\Commands\InitAuthCommand;

class ZCRMServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/zcrm.php', 'zcrm');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/zcrm.php' => config_path('zcrm.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ListCrmCommand::class,
                RemoveCrmCommand::class,
                InitAuthCommand::class,
            ]);
        }

        // 👉 Route auto
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }
}
