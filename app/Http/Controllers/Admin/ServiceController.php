<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Category;
use App\Models\Service;
use Inertia\Inertia;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set(__('Services'))
            ->addOverview(__('Total Services'), Service::count(), 'bx:grid-alt')
            ->addOverview(__('Active Services'), Service::where('is_active', 1)->count(), 'bx:check-circle')
            ->addOverview(__('Inactive Services'), Service::where('is_active', 0)->count(), 'bx:x-circle')
            ->addOverview(__('Featured Services'), Service::where('is_featured', 1)->count(), 'bx:star')
            ->addLink('Add New', route('admin.services.create'), 'bx:plus');

        $services = Service::with('category')
            ->filterOn(['title', 'overview', 'is_active', 'is_featured'])
            ->latest()->paginate();

        return inertia('Admin/Services/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        PageHeader::set(__('Create Service'))->addBackLink(route('admin.services.index'));

        $categories = Category::whereType('service')->get();
        return Inertia::render('Admin/Services/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {
        $newService = $request->validated();
        $newService['meta'] = $request->input('seo');
        Service::create($newService);
        return to_route('admin.services.index')->with('success', 'Saved successfully');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        PageHeader::set(__('Edit Service'))->addBackLink(route('admin.services.index'));
        $categories = Category::whereType('service')->get();
        return inertia('Admin/Services/Edit', [
            'categories' => $categories,
            'service' => $service
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        $newService = $request->validated();
        $newService['meta'] = $request->input('seo');

        $service->update($newService);

        return to_route('admin.services.index')->with('info', 'Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Service::findOrFail($id)->delete();
        return to_route('admin.services.index')->with('danger', 'Deleted successfully');
    }
}
