<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin-and-roles');
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        PageHeader::set()
            ->title(__('Roles'))
            ->addLink(__('Add New'), route('admin.role.create'), 'bx:plus');

        $roles = Role::with('permissions')->where('id', '!=', 1)->get();
        return Inertia::render('Admin/Role/Index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions_groups = User::getPermissionGroup()->map(function ($q) {
            $data['group_name'] = $q->group_name;
            $data['permissions'] = User::getPermissionsByGroupName($q->group_name);

            return $data;
        });

        PageHeader::set()
            ->title(__('Create Role'))
            ->addBackLink(route('admin.role.index'));


        return Inertia::render('Admin/Role/Create', compact(
            'permissions_groups',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:100',
        ]);
        $role = Role::create(['name' => $request->name]);
        $permissions = $request->input('permissions');
        if (!empty($permissions)) {

            $role->syncPermissions($permissions);
        }

        return to_route('admin.role.index')->with('success', 'Created Successfully');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        $permissions_groups = User::getPermissionGroup()->map(function ($q) {
            $data['group_name'] = $q->group_name;
            $data['permissions'] = User::getPermissionsByGroupName($q->group_name);

            return $data;
        });

        PageHeader::set()
            ->title(__('Edit Role'))
            ->addBackLink(route('admin.role.index'));

        return Inertia::render('Admin/Role/Edit', compact(
            'permissions_groups',
            'role'
        ));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|max:100|unique:roles,name,' . $id,
        ]);

        $role->update(['name' => $request->name]);

        $permissions = $request->input('permissions');
        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return to_route('admin.role.index')->with('success', 'Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {

        Role::where('id', '!=', 1)->where('id', $id)->delete();
        return back()->with('danger', 'Deleted Successfully');
    }
}
