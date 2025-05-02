<?php

namespace ZCRM\Commands;

use Illuminate\Console\Command;

class InitAuthCommand extends Command
{
    protected $signature = 'zcrm:init-auth 
        {--client_id= : Le Client ID de l’application Zoho}
        {--redirect_uri= : L’URL de redirection après connexion}
        {--scope=ZohoCRM.modules.ALL : Le scope à demander}
        {--region=eu : La région (us, eu, in, cn, jp)}';

    protected $description = 'Génère un lien OAuth Zoho à ouvrir dans le navigateur pour obtenir un code d’autorisation';

    public function handle()
    {
        $clientId = $this->option('client_id');
        $redirectUri = $this->option('redirect_uri');
        $scope = $this->option('scope') ?? 'ZohoCRM.modules.ALL';
        $region = $this->option('region') ?? 'eu';

        if (!$clientId || !$redirectUri) {
            $this->error('Vous devez fournir --client_id et --redirect_uri');
            return 1;
        }

        $authUrl = sprintf(
            'https://accounts.zoho.%s/oauth/v2/auth?scope=%s&client_id=%s&response_type=code&access_type=offline&redirect_uri=%s',
            $region,
            urlencode($scope),
            $clientId,
            urlencode($redirectUri)
        );

        $this->info("👉 Ouvre ce lien dans ton navigateur pour autoriser l’application :\n");
        $this->line($authUrl);

        $this->info("\n🔁 Une fois le code obtenu, lance :");
        $this->line("php artisan zcrm:exchange-code --client_id=... --client_secret=... --code=... --redirect_uri=...");
    }
}
