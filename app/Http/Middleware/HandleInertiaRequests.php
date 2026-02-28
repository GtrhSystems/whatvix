<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Helpers\Toastr;
use Inertia\Middleware;
use App\Helpers\SeoMeta;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Cache;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    // protected $rootView = 'app';

    public function rootView(Request $request): string
    {
        $rootViews = config('inertia.root_views', false);
        $moduleName = $request->segments()[0] ?? false;

        if (is_array($rootViews)) {
            if ($moduleName && array_key_exists($moduleName, $rootViews)) {
                return $this->resolvePreFixableViewPath($rootViews[$moduleName]);
            }
        }
        $modules = Module::collections()->keys()->toArray();

        if (in_array($moduleName, $modules)) {
            return $this->resolvePreFixableViewPath('Modules');
        }

        return $this->resolvePreFixableViewPath(config('inertia.default_view'));
    }

    private function resolvePreFixableViewPath($viewPath): string
    {
        $prefix = config('inertia.root_view_prefix');
        if ($prefix) {
            return $prefix . '.' . $viewPath;
        }

        return $viewPath;
    }

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        if (request()->is('install/*') || !file_exists(base_path('public/uploads/installed'))) {
            return [];
        }
        
        $activeWorkspace = [];
        $userWorkspaces = [];

        $authUser = null;
        if (auth()->check()) {
            /**
             * @var \App\Models\User
             */
            $authUser = auth()->user();
            $activeWorkspace = $authUser->activeWorkspace;
            $userWorkspaces = $authUser->allWorkspaces();
        }

        $activeModule = getRequestModuleName()->value();
        if (!$activeModule || $activeModule == '')
            $activeModule = null;

       

        $locale = current_locale();
        $menu = Cache::remember(
            'menu_' . $locale,
            env('CACHE_LIFETIME', 1800),
            function () use ($locale) {
                return Menu::where('lang', $locale)->where('status', 1)->oldest()->get();
            }
        );

        $broadcastConfig = match (config('broadcasting.default', 'pusher')) {
            'pusher' => [
                'broadcaster' => 'pusher',
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                'encrypted' => config('broadcasting.connections.pusher.options.encrypted'),
            ],
            'reverb' => [
                'broadcaster' => 'reverb',
                'key' => config('broadcasting.connections.reverb.key'),
                'wsHost' => config('broadcasting.connections.reverb.options.host'),
                'wsPort' => config('broadcasting.connections.reverb.options.port'),
                'wssPort' => config('broadcasting.connections.reverb.options.port'),
                'forceTLS' => config('broadcasting.connections.reverb.options.useTLS'),
                'enabledTransports' => ['ws', 'wss'],
            ],
            default => [],
        };

        return array_merge(parent::share($request), [
            ...parent::share($request),
            'app_name' => config('app.name'),
            'authUser' => $authUser,
            'languages' => get_option('languages'),
            'currency' => get_option('base_currency', true),
            'primaryData' => get_option('primary_data'),
            'locale' => session('locale', 'en'),
            'toast' => fn() => Toastr::Toast(),
            'pageHeader' => fn() => PageHeader::toArray(),
            'activeModule' => fn() => $activeModule,
            'userWorkspaces' => fn() => $userWorkspaces,
            'activeWorkspace' => fn() => $activeWorkspace,
            'pusher_app_key' => config('broadcasting.connections.pusher.key'),

            'seoMeta' => fn() => SeoMeta::get(),
            'homeData' => fn() => $request->is('/') ? get_option('home_page') : [],
            'newsletter_api' => fn() => env('NEWSLETTER_API_KEY', null),
            'menus' => $menu,
            'broadcast_config' => fn() => $broadcastConfig,
        ]);
    }
}
