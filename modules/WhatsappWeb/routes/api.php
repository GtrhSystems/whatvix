<?php

use Illuminate\Support\Facades\Route;
use Modules\WhatsappWeb\App\Http\Controllers\Api\AppController;
use Modules\WhatsappWeb\App\Http\Controllers\Api\ChatController;
use Modules\WhatsappWeb\App\Http\Controllers\Api\DeviceController;
use Modules\WhatsappWeb\App\Http\Controllers\Api\WebhookController;
use Modules\WhatsappWeb\App\Http\Controllers\Api\BulkSendController;

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

Route::middleware(['web'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/platforms/connection', [DeviceController::class, 'connection'])
            ->name('platforms.connection');
        Route::get('/platforms/code', [DeviceController::class, 'code'])
            ->name('platforms.code');
        Route::get('/platforms/status', [DeviceController::class, 'checkStatus'])
            ->name('platforms.check-status');
        Route::get('/platforms/{uuid}/check-verification', [DeviceController::class, 'check_verification'])
            ->name('platforms.check-verification');
        Route::delete('/platforms/{uuid}', [DeviceController::class, 'destroy'])
            ->name('platforms.destroy');

        // bulk send
        Route::get('/bulk-send/contact-list', [BulkSendController::class, 'contact_list'])
            ->name('bulk-send.contact_list');

        Route::post('/bulk-send-message', [BulkSendController::class, 'send_message'])
            ->name('bulk-send.message');

        Route::get('/{sessionId}/chats/{jid}/photo', [ChatController::class, 'getContactPhoto']);

        Route::get('/{sessionId}/chats', [ChatController::class, 'chats']);
        Route::post('/{sessionId}/chats/{jid}/read', [ChatController::class, 'readChat']);
        Route::get('/{sessionId}/chats/{chatId}', [ChatController::class, 'chatMessages']);
        Route::get('/{sessionId}/groups/{groupId}', [ChatController::class, 'groupMessages']);
        Route::post('/{sessionId}/messages/download', [ChatController::class, 'getMedia']);
        Route::post('/{sessionId}/messages/send', [ChatController::class, 'sendMessage']);
        Route::post('/{sessionId}/messages/read', [ChatController::class, 'readMessages']);
    });

// chats

// send message (api)
Route::post('send-message', [AppController::class, 'sendMessage'])->name('send-message')->middleware('throttle');

Route::any('webhook', [WebhookController::class, 'store'])->name('webhook');
