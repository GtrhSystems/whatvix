<?php

use Illuminate\Support\Facades\Route;
use Modules\Whatsapp\App\Http\Controllers as MODULE;
use Modules\Whatsapp\App\Http\Controllers\TemplateController;

Route::group(['middleware' => ['auth', 'user', 'access_module:whatsapp']], function () {

    Route::get('overview', MODULE\DashboardController::class)->name('overview');

    Route::get('platforms/{platform}/logs', [MODULE\PlatformController::class, 'logs'])->name('platforms.logs');
    Route::resource('platforms', MODULE\PlatformController::class)->except('edit');
    Route::get('platforms/{platform_uuid}/conversations/{conversation_id}', [MODULE\ChatController::class, 'showConversation'])->name('platforms.conversations.show');

    // Apps
    Route::resource('apps', MODULE\AppController::class);
    Route::get('apps/{app}/logs', [MODULE\AppController::class, 'logs'])->name('apps.logs');
    Route::get('/apps/integration/{app}', [MODULE\AppController::class, 'integration'])->name('app.integration');


    // campaigns
    Route::get('campaigns/{campaign}/send', [MODULE\CampaignController::class, 'send'])->name('campaigns.send');
    Route::get('campaigns/{campaign}/copy', [MODULE\CampaignController::class, 'copy'])->name('campaigns.copy');
    Route::resource('campaigns', MODULE\CampaignController::class);
    Route::get('get-device-template-list', [MODULE\TemplateController::class, 'getDeviceTemplateList'])->name('device.template.list');
    Route::resource('devices/{device}/qr-codes', MODULE\DeviceQRCodeController::class)
        ->only(['index', 'store', 'destroy']);

    // templates
    Route::get('templates/{template}/copy', [TemplateController::class, 'copy'])->name('templates.copy');
    Route::resource('templates', MODULE\TemplateController::class);

    // quick replies
    Route::resource('quick-replies', '\App\Http\Controllers\User\QuickReplyController')->except('show');

    // auto replies
    Route::resource('auto-replies', MODULE\AutoReplyController::class);

    // customers
    Route::post('customers/bulk-import', [MODULE\CustomerController::class, 'bulkImport'])->name('customers.bulk-import');
    Route::resource('customers', MODULE\CustomerController::class);

    // groups
    Route::resource('groups', MODULE\GroupController::class);

    // messages.load-attachment
    Route::post('messages/{wamid}/load-attachment', [MODULE\MessageController::class, 'loadAttachment'])->name('messages.load-attachment');
});
