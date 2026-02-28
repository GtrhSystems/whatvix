<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\AiModel;
use App\Models\AiTemplate;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiTemplate\StoreAiTemplateRequest;

class AiTemplateController extends Controller
{

    public function index()
    {

        PageHeader::set(__('AI Templates'))
            ->addOverview(
                __('Total Templates'),
                AiTemplate::count(),
                'bx:grid-alt'
            )
            ->addOverview(
                __('Active Templates'),
                AiTemplate::where('status', 'active')->count(),
                'bx:check-circle'
            )
            ->addOverview(
                __('Inactive Templates'),
                AiTemplate::where('status', 'inactive')->count(),
                'bx:x-circle'
            )
            ->addLink(__('Add New'), route('admin.ai-templates.create'))
        ;

        $templates = AiTemplate::latest()->paginate();

        return Inertia::render('Admin/AiTemplates/Index', [
            'templates' => $templates
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        PageHeader::set('Create AI Template')->addBackLink(route('admin.ai-templates.index'));
        $models = AiModel::query()->active()->get(['id', 'name', 'provider', 'code']);
        $aiProviders = AiModel::distinct()->pluck('provider')->toArray();
        return Inertia::render('Admin/AiTemplates/Create', [
            'aiProviders' => $aiProviders,
            'aiModels' => $models,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAiTemplateRequest $request)
    {
        AiTemplate::create($request->validated());
        return to_route('admin.ai-templates.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AiTemplate $aiTemplate)
    {
        PageHeader::set('Edit AI Template')->addBackLink(route('admin.ai-templates.index'));
        $models = AiModel::query()->active()->get(['id', 'name', 'provider', 'code']);
        $aiProviders = AiModel::distinct()->pluck('provider')->toArray();
        return Inertia::render('Admin/AiTemplates/Create', [
            'template' => $aiTemplate,
            'aiProviders' => $aiProviders,
            'aiModels' => $models
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAiTemplateRequest $request, AiTemplate $aiTemplate)
    {

        if (env('DEMO_MODE')) {
            return back()->with('danger', __('Permission disabled for demo mode..!'));
        }

        $aiTemplate->update($request->validated());
        return to_route('admin.ai-templates.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AiTemplate $aiTemplate)
    {
        if (env('DEMO_MODE')) {
            return back()->with('danger', __('Permission disabled for demo mode..!'));
        }

        $aiTemplate->delete();
        return to_route('admin.ai-templates.index')->with('danger', 'Deleted Successfully');
    }
}
