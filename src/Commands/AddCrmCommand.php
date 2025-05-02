<?php

namespace ZCRM\Commands;

use Illuminate\Console\Command;
use ZCRM\ClientManager;

/**
 * php artisan zcrm:add-crm \
 * --name=crm-test \
 * --client_id=1000.ABC123 \
 * --client_secret=abcXYZ987654 \
 * --refresh_token=1000.xxxx.yyyy.zzzz \
 * --region=eu
 */
class AddCrmCommand extends Command
{
    protected $signature = 'zcrm:add-crm 
                            {--name= : Nom unique du CRM} 
                            {--client_id= : Client ID Zoho} 
                            {--client_secret= : Client Secret Zoho} 
                            {--refresh_token= : Refresh Token obtenu via l’OAuth Zoho} 
                            {--region=eu : Région Zoho (eu, us, in, etc.)} 
                            {--api_domain= : Domaine API (optionnel)}';

    protected $description = 'Ajoute une nouvelle connexion CRM Zoho';

    public function handle()
    {
        $name = $this->option('name');
        $clientId = $this->option('client_id');
        $clientSecret = $this->option('client_secret');
        $refreshToken = $this->option('refresh_token');
        $region = $this->option('region') ?? 'eu';
        $apiDomain = $this->option('api_domain') ?? $this->guessApiDomain($region);

        if (!$name || !$clientId || !$clientSecret || !$refreshToken) {
            $this->error('Tous les champs obligatoires (--name, --client_id, --client_secret, --refresh_token) doivent être renseignés.');
            return 1;
        }

        try {
            $manager = new ClientManager();
            $manager->addConnection([
                'name' => $name,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'region' => $region,
                'api_domain' => $apiDomain,
            ]);

            $this->info("CRM '{$name}' ajouté avec succès.");
            return 0;
        } catch (\Throwable $e) {
            $this->error("Erreur lors de l'ajout : " . $e->getMessage());
            return 1;
        }
    }

    protected function guessApiDomain(string $region): string
    {
        return match ($region) {
            'us' => 'https://www.zohoapis.com',
            'eu' => 'https://www.zohoapis.eu',
            'in' => 'https://www.zohoapis.in',
            'cn' => 'https://www.zohoapis.com.cn',
            default => 'https://www.zohoapis.eu',
        };
    }
}
