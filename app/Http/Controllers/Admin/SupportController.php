<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use Inertia\Inertia;
use App\Models\Support;
use App\Models\Supportlog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:support');
    }

    public function index(Request $request)
    {
        $supports = Support::query()
            ->filterOn(['ticket_no', 'subject', 'user_email', 'status'])
            ->with('user')->withCount('conversations')->latest()->paginate(20);
        $pendingSupport = Support::where('status', "2")->count();
        $openSupport = Support::where('status', "1")->count();
        $closedSupport = Support::where('status', "0")->count();
        $totalSupports = $pendingSupport + $openSupport + $closedSupport;

        $type = $request->type ?? 'email';
        PageHeader::set(title: 'Supports', overviews: [
            [
                'icon' => "bx:bar-chart",
                'title' => 'Total Supports',
                'value' => $totalSupports,
            ],
            [
                'icon' => "bx:badge",
                'title' => 'Pending Supports',
                'value' => $pendingSupport,
            ],
            [
                'icon' => "bx:x-circle",
                'title' => 'Closed Supports',
                'value' => $closedSupport,
            ],
            [
                'icon' => "bx:archive-in",
                'title' => 'Open Supports',
                'value' => $openSupport,
            ],
        ]);
        return Inertia::render('Admin/Support/Index', [
            'request' => $request,
            'supports' => $supports,
            'type' => $type,
        ]);
    }

    public function show($id)
    {
        PageHeader::set(__('Support Details'))->addBackLink(route('admin.support.index'));

        $support = Support::with('conversations.user', 'user')->findOrFail($id);
        Supportlog::where('is_admin', 0)
            ->where('support_id', $id)
            ->update([
                'seen' => 1
            ]);

        return Inertia::render('Admin/Support/Show', [
            'support' => $support,
        ]);
    }


    public function update(Request $request, $id)
    {
        $support = Support::findOrFail($id);


        $support->status = $request->status;
        $support->save();
        if ($request->filled('message')) {
            $request->validate([
                'message' => 'required|max:1000',
            ]);
            $support->conversations()->create([
                'comment' => $request->message,
                'is_admin' => 1,
                'seen' => 0,
                'user_id' => Auth::id()
            ]);
        }

        return back();
    }
}
