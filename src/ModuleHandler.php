<?php

namespace ZCRM;

use ZCRM\Exceptions\ZCRMException;

/**
 * use ZCRM;
 * $deals = ZCRM::use('crm-test')->useModule('Deals')->getRecords();
 *
 * $lead = ZCRM::useModule('Leads')->getRecord('1234567890'); // utilise le premier CRM si pas précisé
 *
 * $newContact = ZCRM::useModule('Contacts')->createRecord([
 *   'First_Name' => 'Ju',
 *   'Last_Name' => 'Devreux',
 *   'Email' => 'ju@devreux.fr',
 * ]);
 *
 * ZCRM::useModule('Contacts')->updateRecord('987654321', [
 *   'Phone' => '0612345678',
 * ]);
 * 
 * $tousLesLeads = ZCRM::useModule('Leads')->getAllRecords();
 * $clients = ZCRM::useModule('Contacts')->getAllRecords([
 *     'fields' => 'First_Name,Last_Name,Email'
 * ]);
 * 
 * ZCRM::useModule('Leads')->deleteRecord('12345');
 * ZCRM::useModule('Deals')->uploadFile('12345', storage_path('some.pdf'));
 * ZCRM::useModule('Contacts')->findByCriteria('(City:equals:Paris)');
 * $criteria = ZCRMSearchBuilder::make()
 *   ->where('Email', 'starts_with', 'contact@')
 *   ->andWhere('City', 'equals', 'Lyon');
 *
 * ZCRM::useModule('Contacts')->findByCriteria($criteria);
 */
class ModuleHandler
{
    protected string $module;
    protected ZohoHttpClient $client;

    public function __construct(?string $crmName, string $module)
    {
        $this->module = $module;
        $this->client = new ZohoHttpClient($crmName);
    }

    public function getRecords(array $options = []): array
    {
        $params = array_merge([
            'per_page' => 200,
            'page' => 1,
        ], $options);

        $response = $this->client->get($this->module, $params);

        if (!isset($response['data'])) {
            throw new ZCRMException(
                "Erreur lors de la récupération des enregistrements.\nRéponse brute : " . json_encode($response)
            );
        }

        return $response['data'];
    }


    public function getRecord(string $id): array
    {
        if (!preg_match('/^\d+$/', $id)) {
            throw new ZCRMException("L’ID fourni pour le module {$this->module} est invalide : $id");
        }

        $response = $this->client->get("{$this->module}/{$id}");

        if (!isset($response['data'][0])) {
            throw new ZCRMException("Enregistrement non trouvé dans le module {$this->module}.");
        }

        return $response['data'][0];
    }

    public function createRecord(array $data): array
    {
        $payload = ['data' => [$data]];
        $response = $this->client->post($this->module, $payload);

        if (!isset($response['data'][0])) {
            throw new ZCRMException("Erreur lors de la création de l'enregistrement.");
        }

        return $response['data'][0];
    }

    public function updateRecord(string $id, array $data): array
    {
        $payload = ['data' => [$data]];
        $response = $this->client->put("{$this->module}/{$id}", $payload);

        if (!isset($response['data'][0])) {
            throw new ZCRMException("Erreur lors de la mise à jour de l'enregistrement.");
        }

        return $response['data'][0];
    }

    public function getAllRecords(array $options = []): array
    {
        $all = [];
        $page = 1;
        $perPage = $options['per_page'] ?? 200;

        do {
            $records = $this->getRecords(array_merge($options, [
                'page' => $page,
                'per_page' => $perPage,
            ]));

            $count = count($records);
            $all = array_merge($all, $records);
            $page++;
        } while ($count === $perPage);

        return $all;
    }

    public function deleteRecord(string $id): bool
    {
        $response = $this->client->http->delete("{$this->module}/{$id}", [
            'headers' => $this->client->getHeaders()
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['data'][0]['status']) || $data['data'][0]['status'] !== 'success') {
            throw new ZCRMException("Erreur lors de la suppression de l'enregistrement.");
        }

        return true;
    }

    public function uploadFile(string $recordId, string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new ZCRMException("Fichier introuvable à l'emplacement : {$filePath}");
        }

        $endpoint = "{$this->module}/{$recordId}/Attachments";

        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->client->getCrmInfo()['api_domain'] . '/crm/v2/',
            'timeout' => 10.0,
        ]);

        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $this->client->getCrmInfo()['access_token'],
            ],
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath),
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (!isset($data['data'][0]['code']) || $data['data'][0]['code'] !== 'SUCCESS') {
            throw new ZCRMException("Erreur lors de l'upload du fichier.");
        }

        return $data['data'][0];
    }

    public function findByCriteria(string $criteria): array
    {
        $response = $this->client->get("{$this->module}/search", [
            'criteria' => $criteria,
        ]);

        if (!isset($response['data'])) {
            throw new ZCRMException("Aucun résultat trouvé pour : {$criteria}");
        }

        return $response['data'];
    }
}
