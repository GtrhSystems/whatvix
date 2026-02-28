<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plan;
use App\Models\User;
use App\Helpers\Toastr;
use App\Models\Gateway;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ActivityLog;
use Inertia\Inertia;

class UserController extends Controller
{
    use Uploader;

    public function __construct()
    {
        $this->middleware('permission:users');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::query()->with(['plan'])->where('role', 'user')
            ->filterOn(['name', 'email', 'status'])
            ->paginate();

        $plans = Plan::where('status', 1)->get();
        $gateways = Gateway::where('status', 1)->get();
        $userQuery = User::query()->where('role', 'user');

        PageHeader::set(title: 'Users', overviews: [
            [
                'icon' => "bx:list-ul",
                'title' => 'Total Users',
                'value' => $userQuery->newQuery()->count(),
            ],
            [
                'icon' => "bx:checkbox-checked",
                'title' => 'Active Users',
                'value' => $userQuery->newQuery()->where('status', 1)->count(),
            ],
            [
                'icon' => "bx:credit-card",
                'title' => 'Inactive Users',
                'value' => $userQuery->newQuery()->where('status', '!=', 1)->count(),
            ],
        ]);
        return inertia('Admin/Users/Index', [
            'users' => $users,
            'plans' => $plans,
            'gateways' => $gateways,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        PageHeader::set(title: 'User details');
        $user = User::query()
            ->where('role', 'user')
            ->findOrFail($id);

        $orders = $user->orders()->with('user', 'plan', 'gateway')->latest()->paginate(20);
        $activityLogs = ActivityLog::query()
            ->with([
                'user:id,name,email',
                'creator:id,name,email',
                'workspace:id,name',
            ])
            ->where('user_id', $id)
            ->when(request('search'), function ($query, $search) {
                return match (request('type')) {
                    'description' => $query->where('description', 'like', "%{$search}%"),
                    'creator_email' => $query->whereHas('creator', fn($query) => $query->where('email', 'like', "%{$search}%")),
                    'user_email' => $query->whereHas('user', fn($query) => $query->where('email', 'like', "%{$search}%")),
                    default => $query
                };
            })
            ->latest()
            ->paginate(10, ['*'], 'activity-logs');
        return Inertia::render('Admin/Users/Show', [
            'singleUser' => $user,
            'orders' => $orders,
            'activityLogs' => $activityLogs
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        PageHeader::set(__('Edit User'))->buttons([
            [
                'text' => __('Back'),
                'url' => route('admin.users.index'),
            ]
        ]);


        return inertia('Admin/Users/Edit', [
            'userInfo' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::query()->findOrFail($id);
        $updateData = $request->merge([
            'status' => $request->get('status') ? 1 : 0,
            'email_verified_at' => $request->get('email_verified_at') ? now() : null,
        ])->toArray();

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->get('password'));
        } else {
            unset($updateData['password']);
        }

        // upload avatar
        if ($request->hasFile('avatar')) {
            $updateData['avatar'] = $this->uploadFile('avatar', $user->avatar);
        }

        DB::transaction(function () use ($user, $updateData) {
            $user->update($updateData);
        });


        Toastr::success(__('User information has been successfully updated'));

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function assignPlan(Request $request, User $user)
    {
        $request->validate([
            'user_id' => ['required'],
            'plan_id' => ['required'],
            'will_expire' => ['required'],
        ]);

        $plan = Plan::where('status', 1)->where('price', '>', 0)->findOrFail($request->plan_id);

        $user->plan_id = $plan->id;
        $user->plan_data = $plan->data;
        $user->will_expire = $request->get('will_expire', now()->addDays($plan->days));
        $user->save();

        $plan = Plan::findOrFail($plan->id);
        $users = User::where('plan_id', $plan->id)->get();
        $users->chunk(200)->each(function ($chunk) use ($plan) {
            foreach ($chunk as $user) {
                $user->update([
                    'plan_data' => $plan->data,
                    'plan_id' => $plan->id,
                ]);
            }
        });
        $plan->update([
            'last_synced_at' => now()
        ]);

        Notification::sendFromAdmin(
            $user->id,
            title: __('Plan Updated'),
            message: __('new plan assigned by assigned'),
        );


        Session::flash('success', __('Plan has been assigned successfully'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->removeFile($user->avatar);
        $user->delete();
        Toastr::success('User has been deleted successfully');
        return back();
    }
}
