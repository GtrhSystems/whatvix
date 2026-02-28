<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Models\WhatsappWebApp;

class AppController extends Controller
{
    public function index()
    {
        $query = WhatsappWebApp::query();
        PageHeader::set(
            title: 'Apps',
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'title' => 'Total Apps',
                    'value' => $query->count()
                ],
                [
                    'title' => 'Created In Last 7 Days',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                    'icon' => "bx:history",
                ],
                [
                    'title' => 'Created In Last 30 Days',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                    'icon' => "bx:calendar",
                ],

            ]
        );

        $apps = $query->with(['platform:id,name', 'user:id,name'])->paginate();
        return Inertia::render('Admin/Logs/Apps/Index', [
            'apps' => $apps
        ]);
    }

    public function destroy($id)
    {
        $app = WhatsappWebApp::find($id);
        $app->delete();
        return back()->with('success', 'Deleted Successfully');
    }
}
