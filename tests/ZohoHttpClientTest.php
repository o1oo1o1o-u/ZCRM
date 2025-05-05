<?php

namespace ZCRM\Tests;

use ZCRM\ZohoHttpClient;
use ZCRM\Exceptions\ZCRMException;
use ZCRM\Tests\TestCase;
use ZCRM\Tests\FakeZohoHttpClient;

class ZohoHttpClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['zcrm.storage_path' => __DIR__ . '/tmp']);

        $this->ensureDummyConnectionExists();
    }

    public function testCanInstantiateClient()
    {
        $this->expectException(ZCRMException::class);

        new ZohoHttpClient('fake-account-name');
    }

    public function testMockedGetReturnsExpectedResponse()
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
