<?php

namespace ZCRM\Commands;

use Illuminate\Console\Command;
use ZCRM\ClientManager;

/**
 * php artisan zcrm:list-crm

 */
class ListCrmCommand extends Command
{
    protected $signature = 'zcrm:list-crm';
    protected $description = 'Liste les connexions CRM Zoho enregistrées';

    public function handle()
    {
        $manager = new ClientManager();
        $list = $manager->listConnections();

        if (empty($list)) {
            $this->warn('Aucune connexion CRM enregistrée.');
            return;
        }

        $this->table(
            ['Nom', 'Client ID', 'Région', 'API Domain', 'Expires At'],
            collect($list)->map(function ($crm) {
                return [
                    $crm['name'],
                    substr($crm['client_id'], 0, 10) . '...',
                    $crm['region'],
                    $crm['api_domain'],
                    $crm['expires_at'] ?? '–',
                ];
            })
        );
    }
}
