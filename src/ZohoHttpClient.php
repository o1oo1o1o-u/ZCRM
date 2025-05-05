<?php

namespace ZCRM;

use GuzzleHttp\Client;
use Carbon\Carbon;
use ZCRM\Exceptions\ZCRMException;
use ZCR\Support\ClientManager;

class ZohoHttpClient
{
    protected Client $http;
    protected array $crm;
    protected ClientManager $manager;

    public function __construct(?string $crmName = null)
    {
        $this->manager = new ClientManager();
        $this->crm = $this->manager->getConnection($crmName);

        if (!$this->crm) {
            throw new ZCRMException("Aucune connexion CRM disponible.");
        }

        $this->http = new Client([
            'base_uri' => $this->crm['api_domain'] . '/crm/v2/',
            'timeout' => 10.0,
        ]);
    }

    protected function isTokenExpired(): bool
    {
        if (empty($this->crm['access_token']) || empty($this->crm['expires_at'])) {
            return true;
        }

        return Carbon::parse($this->crm['expires_at'])->isPast();
    }

    protected function refreshToken(): void
    {
        $res = (new Client())->post($this->crm['api_domain'] . '/oauth/v2/token', [
            'form_params' => [
                'refresh_token' => $this->crm['refresh_token'],
                'client_id' => $this->crm['client_id'],
                'client_secret' => $this->crm['client_secret'],
                'grant_type' => 'refresh_token',
            ],
        ]);

        $data = json_decode($res->getBody(), true);

        if (!isset($data['access_token'])) {
            throw new ZCRMException("Erreur lors du refresh_token : " . json_encode($data));
        }

        $this->crm['access_token'] = $data['access_token'];
        $this->crm['expires_at'] = Carbon::now()->addSeconds($data['expires_in'])->toDateTimeString();

        $this->manager->updateTokens($this->crm['name'], $this->crm['access_token'], $this->crm['expires_at']);
    }

    protected function getHeaders(): array
    {
        if ($this->isTokenExpired()) {
            $this->refreshToken();
        }

        return [
            'Authorization' => 'Zoho-oauthtoken ' . $this->crm['access_token'],
            'Content-Type' => 'application/json',
        ];
    }

    public function get(string $endpoint, array $query = []): array
    {
        $res = $this->http->get($endpoint, [
            'headers' => $this->getHeaders(),
            'query' => $query,
        ]);

        return json_decode($res->getBody(), true);
    }

    public function post(string $endpoint, array $data): array
    {
        $res = $this->http->post($endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);

        return json_decode($res->getBody(), true);
    }

    public function put(string $endpoint, array $data): array
    {
        $res = $this->http->put($endpoint, [
            'headers' => $this->getHeaders(),
            'json' => $data,
        ]);

        return json_decode($res->getBody(), true);
    }
}
