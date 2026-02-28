<?php

namespace Modules\Whatsapp\App\Http\Controllers;

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
        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();
        $query = $user->groups()->filterOn(['name'])->whatsapp();

        $groups = $query->withCount('customers')->latest()->paginate();

        $overviews = [
            [
                'icon' => "bx:list-ul",
                'value' => $query->count(),
                'title' => 'Total Groups'
            ],
            [
                'icon' => "bx:checkbox-checked",
                'value' => 'Unlimited',
                'title' => 'Group Limit'
            ],
        ];
        PageHeader::set()->title('Groups')
            ->addModal('Add New', 'groupCreate', 'bx:plus')->overviews($overviews);

        return Inertia::render('Groups/Index', compact('groups', 'overviews'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:200',
        ]);

        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();

        $user->groups()->create([
            'module' => 'whatsapp',
            'name' => $validated['name'],
        ]);

        return back()->with('success', __('Group Created Successfully'));
    }

    public function update(Request $request, $id)
    {
        $group = Group::find($id);

        $validated = $request->validate([
            'name' => 'required|max:200',
        ]);

        $group->update($validated);

        return back()->with('success', __('Group Updated Successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        abort_if($group->owner_id !== activeWorkspaceOwnerId(), 403);

        $group->delete();

        return back()->with('success', __('Group Deleted Successfully'));
    }
}
