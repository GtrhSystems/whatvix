<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use App\Helpers\PageHeader;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\StorePlanRequest;
use App\Http\Requests\Plan\UpdatePlanRequest;

class PlanController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:plan');
    }


    public function index()
    {
        PageHeader::set(__('Plans'))->addLink(__('Create Plan'), route('admin.plan.create'), 'bx:plus');
        $plans = Plan::latest()->withCount('activeuser')->latest()->get();
        return Inertia::render('Admin/Plan/Index', [
            'plans' => $plans,
        ]);
    }


    public function create()
    {
        PageHeader::set(__('Create Plan'))->addBackLink(route('admin.plan.index'));

        $modules = collect(Module::allEnabled())
            ->filter(fn($module) => $module->get('accessible', true))
            ->map(fn($module) => str($module->getName())->headline())
            ->values()
            ->toArray();
        return Inertia::render('Admin/Plan/Create', [
            'modules' => $modules
        ]);
    }

    public function store(StorePlanRequest $request)
    {
        $validated = $request->validated();

        $validated['data'] = $validated['plan_data'];
        unset($validated['plan_data']);

        Plan::create($validated);

        return redirect()->route('admin.plan.index');
    }

    public function edit(Plan $plan)
    {
        PageHeader::set(__('Edit Plan'))->addBackLink(route('admin.plan.index'));
        $modules = collect(Module::allEnabled())
            ->filter(fn($module) => $module->get('accessible', true))
            ->map(fn($module) => str($module->getName())->headline())
            ->values()
            ->toArray();
        return Inertia::render('Admin/Plan/Edit', [
            'plan' => $plan,
            'modules' => $modules
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $validated = $request->validated();

        $validated['data'] = $validated['plan_data'];
        unset($validated['plan_data']);
        $plan->update($validated);

        return redirect()->route('admin.plan.index');
    }

    public function destroy($id)
    {
        $plan = Plan::withCount('activeuser')->findorFail($id);
        if ($plan->activeuser_count != 0) {
            return response()->json([
                'message' => __('You cant delete this plan because this plan already useing some users'),
            ], 403);
        }
        $plan->delete();

        return back()->with('danger', 'Plan deleted successfully');
    }


    public function syncUserPlan($id)
    {
        $plan = Plan::findOrFail($id);

        if ($plan->last_synced_at && $plan->last_synced_at >= $plan->updated_at) {
            return back()->with('danger', 'No changes made after last sync');
        }
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

        return to_route('admin.plan.index')->with('success', 'Plan synced successfully');
    }
}
