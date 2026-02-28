<?php

use Illuminate\Support\Facades\Route;
use Modules\QAReply\App\Http\Controllers\QAReplyController;

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

Route::group(['middleware' => ['auth', 'user', 'access_module:whatsapp']], function () {
    Route::resource('qareplies', QAReplyController::class);
});
