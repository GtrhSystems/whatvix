<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Helpers\PageMeta;
use App\Http\Controllers\Controller;
use App\Models\AiModel;
use Illuminate\Http\Request;

class AiModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set(__('AI Models'))
            ->addModal(__('Add New Model'), 'createModal', 'bx:plus');

        $models = AiModel::query()->filterOn(['provider', 'name'])->paginate();

        $providers = array_keys(config('prism.providers', []));

        return inertia(
            'Admin/AiModels/Index',
            compact('models', 'providers')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required',
            'name' => 'required',
            'code' => 'required',
            'max_token' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        AiModel::create($validated);

        return back()->with('success', __('Model created successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AiModel $aiModel)
    {
        $validated = $request->validate([
            'provider' => 'required',
            'name' => 'required',
            'code' => 'required',
            'max_token' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $aiModel->update($validated);

        return back()->with('success', __('Model updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AiModel $aiModel)
    {
        $aiModel->delete();

        return back()->with('success', __('Model deleted successfully'));
    }
}
