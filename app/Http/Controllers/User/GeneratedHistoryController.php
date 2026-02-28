<?php

namespace App\Http\Controllers\User;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\AiGenerate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneratedHistoryController extends Controller
{
    public function index()
    {
        $aiGenerated = AiGenerate::query()->where('user_id', activeWorkspaceOwnerId());
        PageHeader::set(title: 'Generated History', overviews: [
            [
                'icon' => "bx:box",
                'title' => 'Total Generated Requests',
                'value' => $aiGenerated->clone()->count(),
            ],
            [
                'icon' => "bx:dollar-circle",
                'title' => 'Total Charges',
                'value' => $aiGenerated->clone()->sum('charge'),
            ],
            [
                'icon' => "bx:check-circle",
                'title' => 'Total Results',
                'value' => $aiGenerated->clone()->sum('result'),
            ],
        ]);

        $aiGenerated = $aiGenerated
            ->filterOn(['status', 'content', 'created_at'])
            ->latest()
            ->paginate(10)->through(function ($item) {
                return [
                    ...$item->toArray(),
                    'content' => strip_tags($item->content),
                ];
            });

        return Inertia::render('User/GeneratedHistory/Index', [
            'aiGenerated' => $aiGenerated
        ]);
    }

    public function edit($uuid)
    {
        PageHeader::set(title: 'Edit Generated History');
        $aiGenerated = AiGenerate::query()
            ->where('user_id', activeWorkspaceOwnerId())
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('User/GeneratedHistory/Edit', [
            'aiGenerated' => $aiGenerated,
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $aiGenerated = AiGenerate::query()
            ->where('user_id', activeWorkspaceOwnerId())
            ->where('uuid', $uuid)
            ->firstOrFail();

        $aiGenerated->update([
            'content' => $request->input('content'),
        ]);

        return redirect()->route('user.ai-generated-history.index');
    }
}
