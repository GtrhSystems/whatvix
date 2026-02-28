<?php

use Illuminate\Support\Facades\Route;
use Modules\NumberChecker\App\Http\Controllers\NumberScannerController;

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
    Route::resource('scanner', NumberScannerController::class)
        ->names('scanner');
});
