<?php

use Illuminate\Support\Facades\Route;

Route::get('/zcrm/callback', [OAuthCallbackController::class, 'handle']);
