<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SystemController;


Route::prefix('system')->group(
    function () {
        Route::get('clear', [SystemController::class, 'clearCache'])->name('system.clear');
        Route::get('reset', [SystemController::class, 'reset'])->name('system.reset');
    }
);

Route::prefix('fb')->group(function () {
    Route::get('webhook', function (Request $request) {
        echo $request->input('hub_challenge');
        exit;
    });

    Route::post('webhook', fn(Request $request) => response()->json($request->all()));
});