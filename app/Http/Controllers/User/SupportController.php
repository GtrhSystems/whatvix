<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Models\Support;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index(Request $request)
    {

        PageHeader::set()->title(__('Supports'))
            ->addLink(__('Add New'), route('user.supports.create'), 'bx:plus')
            ->addOverview(__('Total Support'), Support::where('user_id', Auth::id())->count(), 'bx:grid-alt')
            ->addOverview(__('Pending Support'), Support::where('user_id', Auth::id())->where('status', "2")->count(), 'bx:history')
            ->addOverview(__('Open Support'), Support::where('user_id', Auth::id())->where('status', "1")->count(), 'bx:archive-in')
            ->addOverview(__('Closed Support'), Support::where('user_id', Auth::id())->where('status', "0")->count(), 'bx:archive-out');


        $supports = Support::where('user_id', Auth::id())
            ->withCount('conversations')
            ->orderBy('created_at', $request->order ?? 'desc')->paginate(20);

        return Inertia::render('User/Support/Index', [
            'supports' => $supports
        ]);
    }

    public function create()
    {
        PageHeader::set(__('Create Support'))->addBackLink(route('user.supports.index'));
        return Inertia::render('User/Support/Create', [
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|max:100|min:10',
            'message' => 'required|max:1000',
        ]);

        $support = new Support;
        $support->user_id = Auth::id();
        $support->subject = $request->subject;
        $support->save();

        $support->conversations()->create([
            'comment' => $request->message,
            'is_admin' => 0,
            'user_id' => Auth::id()
        ]);

        return to_route('user.supports.index');
    }

    public function show(string $id)
    {
        PageHeader::set(__('Support Details'))->addBackLink(route('user.supports.index'));

        $support = Support::where('user_id', Auth::id())
            ->with(['conversations.user', 'user'])->findOrFail($id);

        return Inertia::render('User/Support/Show', [
            'support' => $support,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'message' => 'required|max:1000',
        ]);

        $support = Support::where('user_id', Auth::id())->where('status', 1)->findOrFail($id);

        $support->conversations()->create([
            'comment' => $request->message,
            'is_admin' => 0,
            'seen' => 0,
            'user_id' => Auth::id()
        ]);

        return back();
    }
}
