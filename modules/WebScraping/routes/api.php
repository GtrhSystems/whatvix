<?php

use Illuminate\Support\Facades\Route;
use Modules\WebScraping\App\Http\Controllers as MODULE;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('scrape/{record}/fetch', [MODULE\Api\WebScrapingController::class, 'index'])
        ->name('scrape.index');
});
