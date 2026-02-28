<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Whatsapp\App\Models\CloudApp;

class AppController extends Controller
{
    public function index()
    {
        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();
        $query = CloudApp::where('user_id', $user->id);

        PageHeader::set(
            title: 'My Apps',
        )->addModal('Add New', 'appModal')
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
        $apps = $query->filterOn(['name'])->with('platform')->paginate(15);
        $devices = $user->platforms()->whatsapp()->get();
        return Inertia::render('Apps/Index', [
            'apps' => $apps,
            'devices' => $devices
        ]);
    }

    public function store(Request $request)
    {
        validateWorkspacePlan('apps');

        $request->validate([
            'name' => ['required', 'max:255'],
            'site_link' => ['required', 'string', 'max:255'],
            'platform_id' => ['required', 'exists:platforms,id'],
        ]);

        CloudApp::create([
            'name' => $request->name,
            'site_link' => $request->site_link,
            'platform_id' => $request->platform_id,
            'user_id' => activeWorkspaceOwnerId(),
            'key' => str()->uuid()
        ]);

        return back()->with('success', 'Created successfully');
    }

    public function destroy($id)
    {
        $app = CloudApp::where('user_id', activeWorkspaceOwnerId())->findOrFail($id);
        $app->delete();
        return back()->with('success', 'Deleted Successfully');
    }

    public function integration($uuid)
    {
        PageHeader::set(title: 'Whatsapp Api Integration')
            ->buttons([
                [
                    'text' => 'Back',
                    'url' => route('user.whatsapp.apps.index')
                ]
            ]);

        $app = CloudApp::where('user_id', activeWorkspaceOwnerId())
            ->where('uuid', $uuid)
            ->firstOrFail();

        $authKey = Auth::user()->authKey ?? 'Your Auth Key';
        return Inertia::render('Apps/Integration', [
            'app' => $app,
            'authKey' => $authKey
        ]);
    }

    public function logs($uuid)
    {
        $app = CloudApp::query()
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
