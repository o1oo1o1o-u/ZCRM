<?php

namespace ZCRM\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use ZCRM\Exceptions\ZCRMException;

class ExchangeCodeCommand extends Command
{
    protected $signature = 'zcrm:exchange-code 
        {--client_id= : Le Client ID}
        {--client_secret= : Le Client Secret}
        {--code= : Le code d’autorisation Zoho reçu}
        {--redirect_uri= : L’URL de redirection utilisée}
        {--region=eu : Région (us, eu, in, cn, jp)}';

    protected $description = 'Échange le code OAuth contre un refresh_token Zoho';

    public function handle()
    {
        $clientId = $this->option('client_id');
        $clientSecret = $this->option('client_secret');
        $code = $this->option('code');
        $redirectUri = $this->option('redirect_uri');
        $region = $this->option('region') ?? 'eu';

        if (!$clientId || !$clientSecret || !$code || !$redirectUri) {
            $this->error('Tous les paramètres sont obligatoires : --client_id, --client_secret, --code, --redirect_uri');
            return 1;
        }

        $http = new Client();
        $domain = "https://accounts.zoho.{$region}";

        try {
            $res = $http->post("$domain/oauth/v2/token", [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ],
            ]);

            $data = json_decode($res->getBody(), true);

            if (!isset($data['refresh_token'])) {
                throw new ZCRMException('Impossible de récupérer le refresh_token : ' . json_encode($data));
            }

            $this->info("\n✅ Refresh token obtenu avec succès :");
            $this->line($data['refresh_token']);

            $this->info("\n📌 Utilise cette commande pour enregistrer le CRM :");
            $this->line("php artisan zcrm:add-crm --name=... --client_id=$clientId --client_secret=$clientSecret --refresh_token={$data['refresh_token']}");
        } catch (\Throwable $e) {
            $this->error("Erreur : " . $e->getMessage());
            return 1;
        }
    }
}
