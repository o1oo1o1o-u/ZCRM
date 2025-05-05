<?php

namespace ZCRM\Tests;

use ZCRM\ZohoHttpClient;

class FakeZohoHttpClient extends ZohoHttpClient
{
    public function __construct()
    {
        // on évite de faire appel au parent __construct
    }

    public function get(string $endpoint, array $params = []): array
    {
        return [
            'data' => [
                ['id' => 123, 'Name' => 'Test']
            ]
        ];
    }

    public function post(string $endpoint, array $payload = []): array
    {
        return ['data' => [['id' => 456]]];
    }
}
