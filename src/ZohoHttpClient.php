<?php

namespace ZCRM;

use GuzzleHttp\Client;
use Carbon\Carbon;
use ZCRM\Exceptions\ZCRMException;
use ZCRM\Support\ClientManager;

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
        $accountsDomain = 'https://accounts.zoho.' . ($this->crm['region'] ?? 'eu');

        $res = (new Client())->post($accountsDomain . '/oauth/v2/token', [
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

    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $res = $this->http->request($method, $endpoint, array_merge([
                'headers' => $this->getHeaders(),
            ], $options));

            $body = (string) $res->getBody();
            $data = json_decode($body, true);

            if (!is_array($data)) {
                logger()->error("🛑 Zoho ($method $endpoint) – Réponse non JSON :\n" . $body);
                throw new ZCRMException("Réponse inattendue de Zoho ($method $endpoint) : contenu non JSON.");
            }

            return $data;
        } catch (\Throwable $e) {
            logger($e);
            $body = method_exists($e, 'getResponse') && $e->getResponse()
                ? (string) $e->getResponse()->getBody()
                : '⚠️ Aucune réponse ou accès impossible à getResponse().';

            logger()->error("❌ Erreur Zoho ($method $endpoint) :
        Message : " . $e->getMessage() . "
        Code : " . $e->getCode() . "
        Classe : " . get_class($e) . "
        Contenu : " . $body);

            throw new ZCRMException("Erreur lors de la requête $method $endpoint : " . $e->getMessage(), $e->getCode(), $e);
        }
    }


    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    public function put(string $endpoint, array $data): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }
}
