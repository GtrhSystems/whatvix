<?php

use Illuminate\Support\Facades\Route;
use Modules\WebScraping\App\Http\Controllers as MODULE;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('/scrape', MODULE\WebScrapingController::class);
    Route::patch('/scrape/store_data/{uuid}', [MODULE\WebScrapingController::class, 'store_data'])
        ->name('scrape.store_data');
    Route::delete('/scrape/destroy_data/{id}', [MODULE\WebScrapingController::class, 'destroy_data'])
        ->name('scrape.destroy_data');
    Route::get('/scrape/export/{id}', [MODULE\WebScrapingController::class, 'export_data'])
        ->name('scrape.export_data');
});
