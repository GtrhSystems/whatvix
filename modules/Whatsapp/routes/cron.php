<?php

use Illuminate\Support\Facades\Route;
use Modules\Whatsapp\App\Http\Controllers\CronController;

Route::group(['prefix' => 'cron', 'as' => 'cron.'], function () {
    Route::get('/dispatch-scheduled-campaigns', CronController::class);
});
