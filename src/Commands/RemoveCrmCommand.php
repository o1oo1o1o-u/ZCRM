<?php

namespace ZCRM\Commands;

use Illuminate\Console\Command;
use ZCRM\ClientManager;

/**
 * php artisan zcrm:remove-crm test
 */
class RemoveCrmCommand extends Command
{
    protected $signature = 'zcrm:remove-crm {name : Nom de la connexion CRM à supprimer}';
    protected $description = 'Supprime une connexion CRM Zoho';

    public function handle()
    {
        $name = $this->argument('name');

        $manager = new ClientManager();
        $removed = $manager->removeConnection($name);

        if ($removed) {
            $this->info("Connexion CRM '{$name}' supprimée avec succès.");
        } else {
            $this->error("Aucune connexion trouvée avec le nom '{$name}'.");
        }
    }
}
