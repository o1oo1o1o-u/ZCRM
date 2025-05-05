<?php

use Illuminate\Support\Facades\Route;
use ZCRM\Http\Controllers\OAuthCallbackController;

Route::get('/zcrm/callback', [OAuthCallbackController::class, 'handle']);
