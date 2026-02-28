<?php

namespace Modules\WhatsappWeb\App\Providers;

use Illuminate\Support\Facades\Route;
use Moduler\Routers\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\WhatsappWeb\App\Http\Controllers';
    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        Route::namespace($this->moduleNamespace)
            ->prefix('whatsapp-web')
            ->group(module_path('WhatsappWeb', '/routes/cron.php'));
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->prefix('user/whatsapp-web')
            ->name('user.whatsapp-web.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('WhatsappWeb', '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/whatsapp-web')
            ->name('user.whatsapp-web.api.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('WhatsappWeb', '/routes/api.php'));
    }
}
