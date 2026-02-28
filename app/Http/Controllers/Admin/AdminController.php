<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Helpers\PageHeader;
use App\Models\Notification;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:admin-and-roles');
    }

    public function index()
    {
        PageHeader::set()
            ->title(__('Admins'))
            ->addOverview(__('Total Admins'), User::where('role', 'admin')->where('id', '!=', 1)->count(), 'bx:grid-alt')
            ->addOverview(__('Active Admins'), User::where('role', 'admin')->where('status', 1)->where('id', '!=', 1)->count(), 'bx:check-circle')
            ->addOverview(__('Inactive Admins'), User::where('role', 'admin')->where('status', 0)->where('id', '!=', 1)->count(), 'bx:x-circle')
            ->addLink(__('Add New'), route('admin.admin.create'), 'bx:plus');

        $users = User::where('role', 'admin')->with('roles')->where('id', '!=', 1)->latest()->get();

        return Inertia::render('Admin/Users/Admin/Index', compact('users'));
    }

    public function create()
    {
        PageHeader::set()
            ->title(__(key: 'Create Admin'))
            ->addBackLink(route('admin.admin.index'));

        $roles = Role::all();
        return Inertia::render('Admin/Users/Admin/Create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'roles' => 'required',
            'email' => 'required|max:100|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = 'admin';
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->roles) {
            $user->assignRole($request->roles);
        }
        Toastr::success('Created Successfully');
        return to_route('admin.admin.index');
    }

    public function edit($id)
    {

        PageHeader::set()
            ->title(__('Edit Admin'))
            ->addBackLink(route('admin.admin.index'));

        $user = User::with('roles')->findOrFail($id);
        $roles = Role::all();
        return Inertia::render('Admin/Users/Admin/Edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $request->validate([
            'name' => 'required|string|max:20',
            'email' => 'required|max:100|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->roles()->detach();
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        Toastr::success('Updated Successfully');

        return to_route('admin.admin.index');
    }

    public function destroy($id)
    {

        User::destroy($id);

        Toastr::danger('Deleted Successfully');

        return back();
    }

    public function adminNotificationsRead(Notification $notification)
    {
        if (!$notification->seen) {
            $notification->seen = 1;
            $notification->save();
        }

        return response()->json([
            'success' => true,
            'redirect_to' => $notification->url ?? false,
        ]);
    }

    public function adminNotificationsClear()
    {
        Notification::query()->where('for_admin', 1)->update(['seen' => 1]);
        Toastr::success('All Notifications Marked As Read');
        return back();
    }
}
