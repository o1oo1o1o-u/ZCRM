<?php

namespace ZCRM\Tests;

use ZCRM\Tests\TestCase;
use ZCRM\ModuleHandler;
use ZCRM\Exceptions\ZCRMException;
use ZCRM\Tests\FakeZohoHttpClient;

class ModuleHandlerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        config(['zcrm.storage_path' => __DIR__ . '/tmp']);

        $this->ensureDummyConnectionExists();
    }


    public function testThrowsOnInvalidId()
    {
        $this->expectException(ZCRMException::class);
        $module = new ModuleHandler('fakecrm', 'Leads');
        $module->getRecord('abc'); // invalide
    }

    public function testGetRecordsWithFakeHttpClient()
    {
        $http = new FakeZohoHttpClient();

        $module = new \ZCRM\ModuleHandler('dummy', 'Deals');
        $ref = new \ReflectionProperty($module, 'client');
        $ref->setAccessible(true);
        $ref->setValue($module, $http);

        $result = $module->getRecords();
        $this->assertEquals(123, $result[0]['id']);
    }
}
