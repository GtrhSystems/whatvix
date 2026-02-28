<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User as USER;

Route::patch('set-workspace/{id}', [USER\WorkspaceController::class, 'switch'])->name('set-workspace');
Route::get('profile', [USER\UserPanelController::class, 'accountSetting'])->name('account-settings');
Route::put('profile', [USER\UserPanelController::class, 'accountSettingUpdate'])->name('account-settings.update');
Route::get('change-password', [USER\UserPanelController::class, 'changePassword'])->name('change-password');
Route::put('change-password', [USER\UserPanelController::class, 'updatePassword'])->name('update-password');
Route::post('regenerate-key', [USER\UserPanelController::class, 'regenerateKey'])->name('account-settings.regenerate-key');

// ---------------------------- a user authorization routes start ----------------------------//
Route::get('dashboard', USER\DashboardController::class)->name('dashboard')
    ->withoutMiddleware(['check_subscription']);
Route::resource('workspaces', USER\WorkspaceController::class);
Route::delete('workspace-members/{workspace}/{member}', [USER\WorkspaceController::class, 'removeMember'])->name('workspaces.members.remove');
Route::delete('leave-workspace/{workspace}', [USER\WorkspaceController::class, 'leave'])->name('workspaces.leave');
Route::resource('teams', USER\TeamController::class);
Route::resource('assets', USER\AssetController::class)->names('assets');
// ai tools
Route::resource('ai-tools', USER\AiToolsController::class)->only(['index', 'show'])->names('ai-tools');
Route::resource('ai-generated-history', USER\GeneratedHistoryController::class)->names('ai-generated-history');
Route::post('ai-tools/bookmark', [USER\AiToolsController::class, 'bookmark'])->name('ai-tools.bookmark');

Route::resource('credits', USER\CreditController::class)->only('index');
Route::get('credit-logs', [USER\CreditLogController::class, 'index'])->name('credit-logs.index');
Route::get('credit-history', [USER\CreditLogController::class, 'history'])->name('credit-logs.history');
// subscription
Route::get('subscription', [USER\SubscriptionController::class, 'index'])->name('subscription.index')
    ->withoutMiddleware(['check_subscription']);
Route::get('subscription/payment/{id}', [USER\SubscriptionController::class, 'payment'])->name('subscription.payment')
    ->withoutMiddleware(['check_subscription']);
Route::post('subscription/subscribe', [USER\SubscriptionController::class, 'subscribe'])
    ->name('subscription.subscribe')
    ->withoutMiddleware(['check_subscription']);
Route::get('subscription/plan/{status}', [USER\SubscriptionController::class, 'status'])
    ->withoutMiddleware(['check_subscription']);


Route::resource('supports', USER\SupportController::class);

// ai training
Route::resource('ai-training', USER\AiTrainingController::class);
Route::delete('ai-training/{aiTraining}/record', [USER\AiTrainingController::class, 'destroyRecord'])
    ->name('ai-training.destroy-record');
Route::get('ai-training/{aiTraining}/test-prompt', [USER\AiTrainingController::class, 'testPrompt'])
    ->name('ai-training.test-prompt');
Route::patch('ai-training/{aiTraining}/status', [USER\AiTrainingController::class, 'checkStatus'])
    ->name('ai-training.check-status');
Route::post('ai-training-credential', [USER\AiTrainingController::class, 'storeCredentials'])
    ->name('ai-training-credential.store');
Route::delete('ai-training-credential/{provider}', [USER\AiTrainingController::class, 'destroyCredentials'])
    ->name('ai-training-credential.destroy');
Route::post('ai-training-import-dataset', [USER\AiTrainingController::class, 'importDataset'])
    ->name('ai-training.import-dataset');
Route::get('ai-training-sync/{provider}', [USER\AiTrainingController::class, 'syncFineTuning'])
    ->name('ai-training.sync');

// activity logs
Route::resource('activity-logs', USER\ActivityLogController::class);
//---------------------- a user authorization routes end--------------------------//

//---------------------- global chat module--------------------------//
Route::get('conversations/api/{data}', [USER\ChatController::class, 'api'])->name('conversations.api');
Route::put('conversations/{conversation}/badge', [USER\ChatController::class, 'assignBadge'])->name('conversations.badge.assign');
Route::delete('conversations/{conversation}/badge', [USER\ChatController::class, 'removeBadge'])->name('conversations.badges.destroy');
Route::resource('conversations', USER\ChatController::class);
Route::resource('messages', USER\MessageController::class)->only(['index', 'store']);
Route::resource('badges', USER\BadgeController::class);
