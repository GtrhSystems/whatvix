<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AiToolsGenerateController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\OptionController;
use App\Http\Controllers\Api\UserDashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/assets', [AssetController::class, 'index'])
        ->name('api-assets');
    Route::get('/assets-all', [AssetController::class, 'index'])
        ->name('api-assets-all');

    Route::post('ai-generate-text', [AiToolsGenerateController::class, 'text'])
        ->name('api-ai-generate-text');
    Route::get('user/dashboard', [UserDashboardController::class, 'index'])->middleware('api');
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth']);
});

Route::get('options/{key}', OptionController::class);





