<?php

namespace Modules\Whatsapp\App\Providers;

use Illuminate\Support\Facades\Route;
use App\Providers\RouteServiceProvider as BaseRouteServiceProvider;
use Moduler\Routers\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Whatsapp\App\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->prefix('user/whatsapp')
            ->as('user.whatsapp.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Whatsapp', '/routes/web.php'));

        Route::namespace($this->moduleNamespace)
            ->prefix('whatsapp')
            ->group(module_path('Whatsapp', '/routes/cron.php'));

        Route::middleware(['api'])
            ->prefix('api/whatsapp')
            ->as('api.whatsapp.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Whatsapp', '/routes/api.php'));
    }
}
