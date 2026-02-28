<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Group;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = activeWorkspaceOwner();
        $query = $user->groups()->filterOn(['name'])->whatsappWeb();

        $groups = $query->withCount('customers')->latest()->paginate();

        PageHeader::set()
            ->title('Groups')
            ->addModal('Add New', 'groupCreate');



        return Inertia::render('Groups/Index', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:200',
        ]);
        $user = activeWorkspaceOwner();

        $user->groups()->create([
            'module' => 'whatsapp-web',
            'name' => $validated['name'],
        ]);

        return back()->with('success', __('Group Created Successfully'));
    }

    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:200',
        ]);

        $group->update($validated);

        return back()->with('success', __('Group Updated Successfully'));
    }

    public function updateCustomers(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        $validated = $request->validate([
            'customers' => 'required|array',
            'customers.*' => 'numeric|exists:customers,id',
            'should_delete' => 'boolean',
        ]);
        if ($request->filled('should_delete') && $request->should_delete) {
            $group->customers()->whereNotIn('id', $validated['customers'])->delete();
        }
        $group->customers()->sync($validated['customers']);

        return back()->with('success', __('Group Updated Successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        $group->delete();
        return back()->with('success', __('Group Deleted Successfully'));
    }
}
