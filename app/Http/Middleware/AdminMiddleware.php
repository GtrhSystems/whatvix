<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Toastr;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            Auth::logout();
            Toastr::danger(__('You Are Not Admin'));
            return inertia()->location('/login');
        }

        if ($admin->status == 1) {
            $menu = Cache::remember(
                'menu_sidebar' . '_admin_menu',
                env('CACHE_LIFETIME', 1800),
                fn() => MenuService::getMenu('admin')
            );
            Inertia::share(['sidebar_menu' => $menu]);
            return $next($request);
        }

        Auth::logout();
        Toastr::danger(__('Your Account Is Deactivated'));
        return inertia()->location('/login');
    }
}
