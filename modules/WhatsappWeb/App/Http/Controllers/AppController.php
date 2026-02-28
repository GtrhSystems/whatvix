<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Models\WhatsappWebApp;

class AppController extends Controller
{
    public function index()
    {
        $query = WhatsappWebApp::where('user_id', activeWorkspaceOwnerId());
        PageHeader::set('My Apps')
            ->addModal('Add New', 'appModal', 'bx-plus')
            ->overviews([
                [
                    'icon' => "bx-list-ul",
                    'value' => $query->clone()->count(),
                    'title' => 'Total Apps'
                ],
                [
                    'icon' => "bx:history",
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                    'title' => 'Created In Last 7 Days'
                ],
                [
                    'icon' => "bx:calendar",
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                    'title' => 'Created In Last 30 Days'
                ]
            ]);
        $apps = $query->with('platform')->paginate();
        $platforms = activeWorkspaceOwner()->platforms()->whatsappWeb()->get();
        return Inertia::render('Apps/Index', [
            'apps' => $apps,
            'platforms' => $platforms,
            'authKey' => activeWorkspaceOwner()->getAttribute('authkey')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:255'],
            'site_link' => ['required', 'string', 'max:255'],
            'platform_id' => ['required', 'exists:platforms,id'],
        ]);

        WhatsappWebApp::create([
            'name' => $request->name,
            'site_link' => $request->site_link,
            'platform_id' => $request->platform_id,
            'user_id' => activeWorkspaceOwnerId(),
            'key' => str()->uuid()
        ]);

        return back()->with('success', 'Created successfully');
    }

    public function show($uuid)
    {
        $app = WhatsappWebApp::where('user_id', activeWorkspaceOwnerId())->where('uuid', $uuid)->firstOrFail();
        PageHeader::set(title: "{$app->name} Api Integration")->buttons([
            [
                'text' => 'Back',
                'url' => route('user.whatsapp-web.apps.index')
            ]
        ]);
        return Inertia::render('Apps/Show', [
            'app' => $app,
            'authKey' => auth()->user()->getAttribute('authkey')
        ]);
    }

    public function destroy($uuid)
    {
        $app = WhatsappWebApp::where('user_id', activeWorkspaceOwnerId())->where('uuid', $uuid)->firstOrFail();
        $app->delete();
        return back()->with('success', 'Deleted Successfully');
    }

    public function logs($uuid)
    {

        $app = WhatsappWebApp::query()
            ->where('user_id', activeWorkspaceOwnerId())
            ->where('uuid', $uuid)
            ->firstOrFail();


        $query = $app->logs();

        PageHeader::set(title: 'Logs')
            ->overviews([
                [
                    'icon' => "bx:list-ul",
                    'value' => $query->clone()->count(),
                    'title' => 'Total'
                ],
                [
                    'icon' => "bx:checkbox-checked",
                    'value' => $query->clone()->where('status_code', '200')->count(),
                    'title' => 'Success'
                ],
                [
                    'icon' => "bx:checkbox",
                    'value' => $query->clone()->where('status_code', '!=', '200')->count(),
                    'title' => 'Failed'
                ]
            ]);

        return Inertia::render('Apps/Logs', [
            'app' => $app,
            'logs' => $app->logs()
                ->with('platform')
                ->paginate(),
        ]);
    }
}
