<?php

namespace App\Http\Middleware;

use App\Helpers\Toastr;
use Closure;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\MenuService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserMiddleware
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
        $user = activeWorkspaceOwner() ?? $request->user();

        if ($user->status !== 1) {
            Auth::logout();
            Session::flash('danger', __('Your Account Has Been Disabled By Admin. Please Contact Us'));
            return inertia()->location('/login');
        }


        if ($user->role === 'user' || $user->role === 'team_member') {
            $this->setupUserMenu($user);
            return $next($request);
        }

        return inertia()->location(route($user->getDashboardRoute()));
    }

    private function setupUserMenu($user)
    {
        $activeWorkspace = $user->activeWorkspace;
        $accessibleModules = ['user'];

        if ($activeWorkspace && isset($activeWorkspace['modules'])) {
            $workspaceModules = array_map('strtolower', $activeWorkspace['modules']);
            $eligibleModules = array_filter($workspaceModules, function ($module) use ($user) {
                $userPlanModules = collect(data_get($user->plan_data, 'modules.value', []))->map(fn($module) => str($module)->kebab()->toString())->toArray();
                return in_array($module, $userPlanModules);
            });
            $accessibleModules = array_merge($accessibleModules, $eligibleModules);

        }

        $menu = collect(MenuService::getMenu('user'))->whereIn('module', $accessibleModules)->values()->all();


        Inertia::share(['sidebar_menu' => $menu]);
    }
}
