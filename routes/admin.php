<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin as ADMIN;


Route::get('dashboard', ADMIN\DashboardController::class)->name('dashboard');

// saas
Route::resource('plan', ADMIN\PlanController::class);
Route::patch('plan/sync/{id}', [ADMIN\PlanController::class, 'syncUserPlan'])
    ->name('plan.sync');
Route::resource('order', ADMIN\OrderController::class);

Route::resource('role', ADMIN\RoleController::class);
Route::resource('admin', ADMIN\AdminController::class);
Route::resource('gateways', ADMIN\GatewayController::class);
Route::get('cron-job', ADMIN\CronjobController::class)
    ->name('cron-job.index');
Route::resource('page', ADMIN\PageController::class);
Route::resource('blog', ADMIN\BlogController::class);
Route::resource('category', ADMIN\CategoryController::class);

Route::resource('faq-category', ADMIN\FaqCategoryController::class);
Route::resource('team', ADMIN\TeamController::class);
Route::resource('tag', ADMIN\TagController::class);

Route::resource('language', ADMIN\LanguageController::class);
Route::resource('menu', ADMIN\MenuController::class);
Route::patch('/menu-data/{id}', [ADMIN\MenuController::class, 'updateData'])->name('menu.data.update');


Route::get('customize-page-settings', [ADMIN\SettingsController::class, 'index'])->name('page-settings.index');
Route::post('customize-page-settings/{id}', [ADMIN\SettingsController::class, 'update'])->name('page-settings.update');

Route::resource('seo', ADMIN\SeoController::class);
Route::resource('support', ADMIN\SupportController::class);
Route::resource('notification', ADMIN\NotifyController::class);
Route::post('notifications/{notification}', [ADMIN\AdminController::class, 'adminNotificationsRead'])->name('notifications.read');
Route::get('notifications/clear', [ADMIN\AdminController::class, 'adminNotificationsClear'])->name('notifications.clear');

Route::resource('testimonials', ADMIN\TestimonialsController::class);
Route::resource('faq', ADMIN\FaqController::class);
Route::resource('developer-settings', ADMIN\DeveloperSettingsController::class);
Route::resource('partner', ADMIN\PartnerController::class);
Route::resource('update', ADMIN\UpdateController::class);

Route::post('/language/addkey', [ADMIN\LanguageController::class, 'addKey']);
Route::post('/menu-update/{id}', [ADMIN\MenuController::class, 'storeDate'])->name('menu.content.update');
Route::get('profile', [ADMIN\ProfileController::class, 'settings'])->name('profile.setting');
Route::put('profile/update/{type}', [ADMIN\ProfileController::class, 'update'])->name('profile.update');
Route::put('/option-update/{key}', [ADMIN\OptionController::class, 'update'])->name('option.update');

Route::resource('users', ADMIN\UserController::class)
    ->only(['index', 'show', 'edit', 'update', 'destroy']);

Route::put('users/assign-plan/{user}', [ADMIN\UserController::class, 'assignPlan'])->name('users.assign.plan');

// saas
Route::resource('service-categories', ADMIN\ServiceCategoryController::class);
Route::resource('services', ADMIN\ServiceController::class);

Route::get('clear-cache', [ADMIN\SystemController::class, 'clearCache'])->name('clear-cache');

// module
Route::resource('module-developer-settings', ADMIN\ModuleDeveloperSettingsController::class);
Route::post('module-settings-check-update/{id}', [ADMIN\ModuleDeveloperSettingsController::class, 'updateModulesCheck'])
    ->name('module-settings-check-update');
Route::get(
    'module-developer-settings-version/{id}',
    [ADMIN\ModuleDeveloperSettingsController::class, 'versionView']
)->name('module-settings-check-version');
Route::post('module-settings-change-status', [ADMIN\ModuleDeveloperSettingsController::class, 'changeStatus'])
    ->name('module-settings-change-status');

// sidebar menu
Route::resource('sidebar-menu', ADMIN\SidebarMenuController::class)
    ->except(['show', 'destroy']);
Route::get('sidebar-menu/{id}/{location}', [ADMIN\SidebarMenuController::class, 'show'])
    ->name('sidebar-menu.show');
Route::delete('sidebar-menu/{id}/{location}', [ADMIN\SidebarMenuController::class, 'destroy'])
    ->name('sidebar-menu.destroy');
Route::patch('sidebar-menu-arrange/{location}', [ADMIN\SidebarMenuController::class, 'arrange'])
    ->name('sidebar-menu.arrange');
Route::patch('/sidebar-menu-data/{id}', [ADMIN\SidebarMenuController::class, 'updateData'])
    ->name('sidebar-menu.data.update');

// ai
Route::resource('ai-models', ADMIN\AiModelController::class)->only(['index', 'store', 'update', 'destroy']);
Route::resource('ai-templates', ADMIN\AiTemplateController::class);
Route::resource('ai-language', ADMIN\AiLanguageController::class);
Route::resource('ai-generated-history', ADMIN\GeneratedHistoryController::class)
    ->only(['index', 'update', 'edit'])
    ->names('ai-generated-history');
Route::resource('credit-logs', ADMIN\CreditController::class)->only(['index', 'update']);
Route::put('update-credit-fee', [ADMIN\CreditController::class, 'updateCreditFee'])->name('update-credit-fee');

// activity logs
Route::resource('activity-logs', ADMIN\ActivityLogController::class);

// user logs
Route::prefix('logs')->as('logs.')->group(function () {
    Route::resource('workspaces', ADMIN\Logs\WorkspaceController::class)->only(['index', 'destroy']);
    Route::resource('members', ADMIN\Logs\MemberController::class)->except('show');
    Route::resource('platforms', ADMIN\Logs\PlatformController::class)->only(['index', 'destroy']);
    Route::resource('apps', ADMIN\Logs\AppController::class)->only(['index']);
    Route::resource('campaigns', ADMIN\Logs\CampaignController::class)->only(['index', 'destroy']);
    Route::resource('templates', ADMIN\Logs\TemplateController::class)->only(['index', 'destroy']);
    Route::resource('quick-replies', ADMIN\Logs\QuickReplyController::class)->only(['index', 'destroy']);
    Route::resource('auto-replies', ADMIN\Logs\AutoReplyController::class)->only(['index', 'destroy']);
    Route::resource('flows', ADMIN\Logs\FlowController::class)->only(['index', 'destroy']);
    Route::resource('customers', ADMIN\Logs\CustomerController::class)->only(['index', 'destroy']);
    Route::resource('groups', ADMIN\Logs\GroupController::class)->only(['index', 'destroy']);
});

// web scraping
Route::resource('web-scraping', ADMIN\WebScrapingController::class);
Route::delete('/web-scraping/destroy_data/{id}', [ADMIN\WebScrapingController::class, 'destroy_data'])
    ->name('web-scraping.destroy_data');
Route::get('web-scraping-category', [ADMIN\WebScrapingController::class, 'category'])
    ->name('web-scraping.category');
