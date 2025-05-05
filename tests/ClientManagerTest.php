<?php

namespace ZCRM\Tests;

use ZCRM\Support\ClientManager;
use ZCRM\Tests\TestCase;

class ClientManagerTest extends TestCase
{
    protected string $dbPath;

    protected function setUp(): void
    {
        parent::setUp();

        config(['zcrm.storage_path' => __DIR__ . '/tmp']);
        $this->dbPath = __DIR__ . '/tmp/crm_connections.sqlite';

        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        }
        $this->resetDummyConnection();
        $this->ensureDummyConnectionExists();
    }

    public function testCreatesDatabase()
    {
        $manager = new ClientManager();
        $this->assertFileExists($this->dbPath);
    }

    public function testCanAddConnection()
    {
        $manager = new ClientManager();
        $manager->addConnection([
            'name' => 'mycrm',
            'client_id' => 'client123',
            'client_secret' => 'secret123',
            'refresh_token' => 'refresh123',
        ]);
        $conn = $manager->getConnection('mycrm');
        $this->assertEquals('mycrm', $conn['name']);
    }
}
