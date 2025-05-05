<?php

namespace ZCRM\Commands;

use Illuminate\Console\Command;

class InitAuthCommand extends Command
{
    protected $signature = 'zcrm:init-auth 
        {--name= : Nom du CRM à enregistrer (ex: moncrm)}
        {--client_id= : Le Client ID de l’application Zoho}
        {--client_secret= : Le Client Secret de l’app Zoho}
        {--redirect_uri= : L’URL de redirection (optionnelle)}
        {--scope=ZohoCRM.modules.ALL : Le scope à demander}
        {--region=eu : La région (us, eu, in, cn, jp)}';

    protected $description = 'Génère un lien OAuth Zoho et prépare l’authentification complète en une étape.';

    public function handle()
    {
        $name = $this->option('name');
        $clientId = $this->option('client_id');
        $clientSecret = $this->option('client_secret');
        $redirectUri = $this->option('redirect_uri') ?? rtrim(config('app.url'), '/') . '/zcrm/callback';
        $scope = $this->option('scope') ?? 'ZohoCRM.modules.ALL';
        $region = $this->option('region') ?? 'eu';

        if (!$name || !$clientId || !$clientSecret) {
            $this->error('Champs requis : --name, --client_id, --client_secret');
            return 1;
        }

        // Sauvegarde dans storage
        $pendingFile = storage_path('app/zcrm/pending.json');

        $pendingDir = storage_path('app/zcrm');
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0755, true);
        }

        file_put_contents($pendingFile, json_encode([
            'name' => $name,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'region' => $region,
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'timestamp' => now()->timestamp,
        ]));

        $authUrl = sprintf(
            'https://accounts.zoho.%s/oauth/v2/auth?scope=%s&client_id=%s&response_type=code&access_type=offline&redirect_uri=%s',
            $region,
            urlencode($scope),
            $clientId,
            urlencode($redirectUri)
        );

        $this->info("👉 Ouvre ce lien dans ton navigateur pour autoriser l’app :\n");
        $this->line($authUrl);

        $this->info("\n⏳ En attente du callback... Tu peux fermer cette console une fois le code autorisé.");
        return 0;
    }
}
