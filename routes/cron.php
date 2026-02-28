<?php

use App\Http\Controllers\CronController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'cron', 'as' => 'cron.'], function () {

    Route::get('/notify-to-user/{days}', [CronController::class, 'notifyToUser']);
    Route::get('/delete-activity-log', [CronController::class, 'pruneActivityLog']);
});
