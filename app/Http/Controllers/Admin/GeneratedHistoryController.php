<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\AiGenerate;
use App\Models\AiTemplate;
use App\Models\Brand;
use App\Models\BrandPost;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneratedHistoryController extends Controller
{
   
    public function index(Request $request)
    {
        PageHeader::set(__('Generated History'))
            ->addOverview(
                __('Total Generated Contents'),
                AiGenerate::count(),
                'bx:grid-alt'
            )
            ->addOverview(
                __('Total Charges'),
                AiGenerate::sum('charge'),
                'bx:money'
            )
            ->addOverview(
                __('Last 7 Days Generated Contents'),
                AiGenerate::where('created_at', '>=', now()->subDays(7))->count(),
                'bx:history'
            );
        $aiGenerated = AiGenerate::query();

        if (!empty($request->search)) {
            if ($request->type == 'email') {
                $aiGenerated = $aiGenerated->whereHas('user', function ($q) {
                    return $q->where('email', request('search'));
                });
            } else {
                $aiGenerated = $aiGenerated->where($request->type, 'LIKE', '%' . $request->search . '%');
            }
        }
        $aiGenerated = $aiGenerated
            ->with(['user:id,name,created_at,role'])
            ->paginate(10)
            ->through(function ($item) {
                return [
                    ...$item->toArray(),
                    'content' => strip_tags($item->content),
                ];
            });

        $total = AiGenerate::query()->count();
        $totalCharges = AiGenerate::query()->sum('charge');
        $totalResults = AiGenerate::query()->sum('result');

        $type = $request->type ?? 'email';

        return Inertia::render('Admin/GeneratedHistory/Index', [
            'aiGenerated' => $aiGenerated,
            'total' => $total ?? [],
            'totalCharges' => $totalCharges ?? [],
            'totalResults' => $totalResults ?? [],
            'request' => $request,
            'type' => $type,
        ]);
    }
    public function edit($uuid)
    {
        PageHeader::set(title: 'Edit Generated History');
        $aiGenerated = AiGenerate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('Admin/GeneratedHistory/Edit', [
            'aiGenerated' => $aiGenerated,
        ]);
    }

    public function update(Request $request, $uuid)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        $aiGenerated = AiGenerate::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $aiGenerated->update([
            'content' => $request->content,
        ]);

        return redirect()->route('admin.ai-generated-history.index');
    }
}
