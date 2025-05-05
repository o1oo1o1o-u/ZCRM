<?php

namespace ZCRM\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use GuzzleHttp\Client;
use ZCRM\Exceptions\ZCRMException;
use Illuminate\Support\Facades\File;
use ZCRM\Support\ClientManager;

class OAuthCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $code = $request->get('code');
        if (!$code) return response('Code manquant', 400);

        $pendingFile = storage_path('app/zcrm/pending.json');
        if (!file_exists($pendingFile)) return response('Aucun zcrm:init-auth en cours', 400);

        $pending = json_decode(file_get_contents($pendingFile), true);
        extract($pending); // $name, $client_id, $client_secret, $region, $redirect_uri

        try {
            $http = new Client();
            $domain = "https://accounts.zoho.{$region}";

            $res = $http->post("$domain/oauth/v2/token", [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                    'redirect_uri' => $redirect_uri,
                    'code' => $code,
                ],
            ]);

            $data = json_decode($res->getBody(), true);

            if (!isset($data['refresh_token'])) {
                throw new ZCRMException('Pas de refresh_token reçu : ' . json_encode($data));
            }

            // Enregistrer automatiquement avec ton ClientManager SQLite
            $manager = new ClientManager();
            $manager->addConnection([
                'name' => $name,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $data['refresh_token'],
                'api_domain' => "https://www.zohoapis.$region",
                'region' => $region,
            ]);

            File::delete($pendingFile); // nettoyage

            return response("✅ CRM [$name] ajouté avec succès !");
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
