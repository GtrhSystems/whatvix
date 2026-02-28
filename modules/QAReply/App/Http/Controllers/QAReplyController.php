<?php

namespace Modules\QAReply\App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\QAReply\App\Models\AutoResponse;
use Modules\QAReply\App\Http\Controllers\Requests\AutoResponseRequest;

class QAReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set(
            'QA Replies',
            buttons: [
                [
                    'icon' => 'fe:plus',
                    'text' => 'Add QA Reply',
                    'url' => '/user/qareply/qareplies/create',
                ]
            ]
        );

        $qaReplies = Auth::user()
            ->autoResponses()
            ->withCount('items')
            ->paginate();

        return Inertia::render('QAReplies/Index', [
            'qaReplies' => $qaReplies
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        PageHeader::set(
            'Add QA Reply Dataset',
            buttons: [
                [
                    'text' => 'Back to List',
                    'url' => '/user/qareply/qareplies',
                    'icon' => 'fe:arrow-left',
                ]
            ]
        );
        return Inertia::render('QAReplies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AutoResponseRequest $request)
    {
        DB::beginTransaction();

        $qaReplies = AutoResponse::create([
            'owner_id' => Auth::id(),
            'title' => $request->input('title'),
        ]);

        $items = $request->input('items', []);

        foreach ($items as $item) {
            $item['owner_id'] = Auth::id();
            $qaReplies->items()->create($item);
        }

        DB::commit();

        return to_route('user.qareply.qareplies.index')
            ->with('success', 'QA Reply created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(AutoResponse $qareply)
    {
        return Inertia::render('QAReplies/Show', [
            'qaReplies' => $qareply
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AutoResponse $qareply)
    {
        PageHeader::set(
            'Edit Dataset',
            buttons: [
                [
                    'text' => 'Back to List',
                    'url' => '/user/qareply/qareplies',
                    'icon' => 'fe:arrow-left',
                ]
            ]
        );

        return Inertia::render('QAReplies/Create', [
            'qaReplies' => $qareply->load('items'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AutoResponseRequest $request, AutoResponse $qareply)
    {

        DB::beginTransaction();

        $qareply->update([
            'title' => $request->input('title'),
        ]);

        $items = $request->input('items', []);

        foreach ($items as $item) {
            $qareply->items()->updateOrCreate(
                [
                    'id' => $item['id'] ?? null
                ],
                [
                    ...$item,
                    'owner_id' => Auth::id(),
                ]
            );
        }

        DB::commit();

        return to_route('user.qareply.qareplies.index')
            ->with('success', 'Dataset updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AutoResponse $qareply)
    {
        $qareply->delete();
        return back()->with('success', 'Dataset deleted successfully');
    }
}
