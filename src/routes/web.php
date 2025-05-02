<?php

use Illuminate\Support\Facades\Route;

Route::get('/zcrm/callback', function (\Illuminate\Http\Request $request) {
    $code = $request->get('code');

    if (!$code) {
        return response('Code manquant dans l’URL', 400);
    }

    return response()->json([
        'code' => $code,
        'message' => 'Copie ce code et exécute la commande : php artisan zcrm:exchange-code ...',
    ]);
});
