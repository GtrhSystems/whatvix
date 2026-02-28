<?php

use Illuminate\Support\Facades\Route;
use Modules\NumberChecker\App\Http\Controllers\Api\NumberScannerController;
use Modules\NumberChecker\Http\Controllers\NumberCheckerController;

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

Route::middleware(['web'])->prefix('v1')->group(function () {
    Route::post('/scanner', [NumberScannerController::class, 'scanner'])
        ->name('scanner');
});
