<?php

namespace ZCRM;

use Illuminate\Support\ServiceProvider;
use ZCRM\Commands\AddCrmCommand;
use ZCRM\Commands\ListCrmCommand;
use ZCRM\Commands\RemoveCrmCommand;

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
                AddCrmCommand::class,
                ListCrmCommand::class,
                RemoveCrmCommand::class,
            ]);
        }
    }
}
