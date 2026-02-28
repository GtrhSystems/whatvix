<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Models\BulkSendLog;

class BulkSendController extends Controller
{

    public function index()
    {
        $query = BulkSendLog::query()->where('user_id', activeWorkspaceOwnerId());

        PageHeader::set(
            title: "Bulk Message",
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'title' => 'Total Sends',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:checkbox-checked",
                    'title' => 'Completed Requests',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:x-circle",
                    'title' => 'Pending Requests',
                    'value' => 0,
                ],
            ]
        )->addLink('Send Bulk Message', route('user.whatsapp-web.send-bulk-message.create'), 'bx:plus');

        $bulkSends = $query
            ->where('user_id', activeWorkspaceOwnerId())
            ->with(['platform', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        return Inertia::render('BulkSend/Index', [
            'bulkSends' => $bulkSends,
        ]);
    }
    public function create(Request $request)
    {
        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();

        PageHeader::set("Bulk Message")->addModal('Configure Bulk Send', 'configBulkSend', 'bx:cog')
            ->addModal('Add Contact', 'addContact', 'bx:group');

        $platforms = $user->platforms()
            ->where('status', 'connected')
            ->whatsappWeb()->get()->pluck('name', 'id');

        $templates = $user->templates()->whatsappWeb()->get();

        $groups = $user->groups()->whatsappWeb()->with('customers')->latest()->pluck('name', 'id');

        return Inertia::render('BulkSend/Create', [
            'platforms' => $platforms,
            'templates' => $templates,
            'groups' => $groups,
        ]);
    }
}