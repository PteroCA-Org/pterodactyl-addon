<?php

use Illuminate\Support\Facades\Route;
use Pteroca\PterodactylAddon\Http\Controllers\ApiKeyController;
use Pteroca\PterodactylAddon\Http\Controllers\SSOAuthorizationController;
use Pteroca\PterodactylAddon\Http\Controllers\PterocaPluginVersionController;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Routing\Middleware\SubstituteBindings;

// Mirror Pterodactyl core's /api/application stack (RouteServiceProvider, 1.11 - 1.15):
// 'api' authenticates the bearer token (sanctum), 'application-api' requires a root admin.
Route::prefix('/api/application')->middleware(['api', 'application-api', 'throttle:api.application'])->group(function () {
    Route::group(['prefix' => '/users'], function () {
        /** Api-Keys */
        Route::get('{user}/api-keys', [ApiKeyController::class, 'index']);
        Route::post('{user}/api-keys', [ApiKeyController::class, 'store']);
        Route::delete('{user}/api-keys/{identifier}', [ApiKeyController::class, 'delete']);
    });
    
    /** Plugin Version */
    Route::get('/pteroca/version', [PterocaPluginVersionController::class, 'getVersion']);
});

Route::middleware([
    EncryptCookies::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    SubstituteBindings::class,
])->group(function () {
    Route::post('/pteroca/authorize', [SSOAuthorizationController::class, 'index']);
});
