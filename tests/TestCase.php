<?php

namespace ZCRM\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \ZCRM\ZCRMServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'ZCRM' => \ZCRM\ZCRM::class,
        ];
    }
    protected function ensureDummyConnectionExists(): void
    {
        $manager = new \ZCRM\Support\ClientManager();

        if (!$manager->getConnection('dummy')) {
            $manager->addConnection([
                'name' => 'dummy',
                'client_id' => 'client123',
                'client_secret' => 'secret123',
                'refresh_token' => 'refresh123',
            ]);
        }
    }

    protected function resetDummyConnection(): void
    {
        $manager = new \ZCRM\Support\ClientManager();
        $manager->removeConnection('dummy');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('zcrm.connections', [
            'dummy' => [
                'name' => 'dummy',
                'client_id' => 'xxx',
                'client_secret' => 'xxx',
                'refresh_token' => 'xxx',
                'access_token' => 'xxx',
                'expires_at' => now()->addHour()->toDateTimeString(),
                'region' => 'eu',
                'api_domain' => 'https://www.zohoapis.eu',
            ]
        ]);
        $app['config']->set('zcrm.storage_path', __DIR__ . '/tmp');
    }
}
