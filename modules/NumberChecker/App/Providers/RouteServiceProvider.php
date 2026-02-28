<?php

namespace Modules\NumberChecker\App\Providers;

use Illuminate\Support\Facades\Route;
use Moduler\Routers\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\NumberChecker\App\Http\Controllers';
    protected string $name = 'NumberChecker';

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
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'auth', 'user', 'access_module:number-checker'])
            ->prefix('user/number-checker')
            ->name('user.number-checker.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('NumberChecker', '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/number-checker')
            ->name('user.number-checker.api.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('NumberChecker', '/routes/api.php'));
    }
}
