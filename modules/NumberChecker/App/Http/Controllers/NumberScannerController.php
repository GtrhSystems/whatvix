<?php

namespace Modules\NumberChecker\App\Http\Controllers;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\NumberChecker\App\Models\NumberScanner;

class NumberScannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set('Number Scanner')
            ->addModal('Configure Scanner', 'configBulkSend', 'bx:cog');
        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();
        $devices = $user->platforms()
            ->whatsappWeb()
            ->select('id', 'name')
            ->get();
        $groups = $user->groups()->whatsappWeb()->with('customers')
            ->select('id', 'name')
            ->latest()->get();
        return Inertia::render('Scanner/Index', [
            'devices' => $devices,
            'groups' => $groups,
            'number_scanned' => NumberScanner::where('user_id', $user->id)->first()['number_scanned'] ?? 0
        ]);
    }
}
