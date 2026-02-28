<?php

namespace Modules\WebScraping\App\Providers;

use Illuminate\Support\Facades\Route;
use Moduler\Routers\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\WebScraping\App\Http\Controllers';
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
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'auth', 'user', 'access_module:web-scraping'])
            ->prefix('user/web-scraping')
            ->name('user.web-scraping.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('WebScraping', '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/webscraping')
            ->name('user.web-scraping.api.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('WebScraping', '/routes/api.php'));
    }
}
