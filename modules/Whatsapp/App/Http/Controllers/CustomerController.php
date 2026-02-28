<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use App\Rules\Phone;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Models\Customer;
use App\Traits\Uploader;
use App\Helpers\PlanPerks;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Whatsapp\App\Imports\CustomerListImport;

class CustomerController extends Controller
{
    use Uploader;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request('export')) {
            return $this->exportCustomerList();
        }
        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();
        $query = $user->customers()->filterOn(['name', 'uuid'])->whatsapp();
        $customers = $query->latest()
            ->whatsapp()
            ->with(['groups:id,name', 'platform:id,name'])
            ->paginate();

        $groups = $user->groups()
            ->whatsapp()
            ->latest()
            ->get(['id', 'name']);

        $platforms = $user->platforms()->whatsapp()->get(['id', 'name']);

        $dialCodes = json_decode(file_get_contents(database_path('json/country_codes.json')), true);
        $overviews = [
            [
                'icon' => "bx:list-ul",
                'value' => $query->count(),
                'title' => 'Total Contact'
            ],
            [
                'icon' => "bx:checkbox-checked",
                'title' => 'Last 7 Days',
                'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
            ],
            [
                'icon' => "hugeicons:limitation",
                'value' => PlanPerks::planValue('contacts'),
                'title' => 'Max Contacts'
            ],
        ];
        PageHeader::set()
            ->title('Customers')
            ->overviews($overviews)
            ->addModal('Import', 'importModal', 'bx:import')
            ->addLink(__("Export"), route('user.whatsapp.customers.index', ['export' => true]), 'bx:export', type: 'a', target: '_blank')
            ->addLink(__("Add New"), route('user.whatsapp.customers.create'), 'bx:plus');

        return Inertia::render('Customers/Index', compact(
            'customers',
            'groups',
            'dialCodes',
            'platforms'
        ));
    }

    public function create()
    {

        validateWorkspacePlan('contacts');

        PageHeader::set()->title('Create Contact Number')->buttons([
            [
                'text' => __("Back"),
                'url' => route('user.whatsapp.customers.index')
            ],
        ]);

        $dialCodes = file_get_contents(base_path('database/json/country_codes.json'));
        $dialCodes = json_decode($dialCodes, true);
        $dialCodes = array_map(function ($item) {
            return [
                'name' => $item['code'] . ' (' . $item['dial_code'] . ')',
                'id' => $item['dial_code']
            ];
        }, $dialCodes);

        $groups = activeWorkspaceOwner()->groups()->whatsapp()->select('id as value', 'name as label')->latest()->get();

        return Inertia::render('Customers/Create', compact('groups', 'dialCodes'));
    }

    public function store(Request $request)
    {
        validateWorkspacePlan('contacts');

        $validated = $request->validate(
            [
                'name' => 'required|max:200',
                'phone' => ['required', new Phone],
                'dial_code' => ['required'],
                'picture' => 'nullable',
                'group_ids' => 'required|array',
                'groups_ids.*' => 'numeric|exists:groups,id',
            ],
            [],
            [
                'group_ids' => 'groups'
            ]
        );


        $user = activeWorkspaceOwner();

        $modifiedDialCode = str($validated['dial_code'])->remove('+');
        $customer = $user->customers()->create([
            'module' => 'whatsapp',
            'name' => $validated['name'],
            'picture' => $validated['picture'] ?? null,
            'uuid' => "{$modifiedDialCode}{$request->phone}",
            'meta' => [
                'dial_code' => $modifiedDialCode,
                'phone' => $request->phone
            ]
        ]);

        if (isset($validated['group_ids']) && count($validated['group_ids'])) {
            $customer->groups()->sync($validated['group_ids']);
        }

        Toastr::success('Created Successfully');

        return to_route('user.whatsapp.customers.index');
    }

    public function edit(Customer $customer)
    {
        PageHeader::set(
            title: 'Edit customer information',
            buttons: [
                [
                    'text' => 'Back',
                    'url' => route('user.whatsapp.customers.index'),

                ]
            ]
        );
        $groupIds = $customer->groups()->pluck('id');
        $groups = activeWorkspaceOwner()->groups()->whatsapp()->select('id as value', 'name as label')->latest()->get();

        $dialCodes = file_get_contents(base_path('database/json/country_codes.json'));
        $dialCodes = json_decode($dialCodes, true);
        $dialCodes = array_map(function ($item) {
            return [
                'name' => $item['code'] . ' (' . $item['dial_code'] . ')',
                'id' => $item['dial_code']
            ];
        }, $dialCodes);

        $customer->dial_code = str($customer->meta['dial_code'] ?? '')->prepend('+')->toString();
        $customer->phone = $customer->meta['phone'] ?? '';

        return Inertia::render('Customers/Edit', compact('groups', 'dialCodes', 'groupIds', 'customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|max:200',
            'phone' => ['required', new Phone],
            'dial_code' => ['required'],
            'picture' => 'nullable',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'numeric|exists:groups,id',
        ]);

        $modifiedDialCode = str($validated['dial_code'])->remove('+');
        $customer->update([
            'name' => $validated['name'],
            'picture' => $validated['picture'] ?? null,
            'uuid' => "{$modifiedDialCode}{$request->phone}",
            'meta' => [
                'dial_code' => $modifiedDialCode,
                'phone' => $request->phone
            ]
        ]);

        $customer->groups()->sync($validated['group_ids'] ?? []);

        return to_route('user.whatsapp.customers.index')->with('success', 'Updated Successfully ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', __('Customer Deleted Successfully'));
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'group_id' => 'required|numeric|exists:groups,id',
            'csv_file' => ['required', 'file'],
        ]);

        Excel::import(new CustomerListImport('whatsapp'), $request->file('csv_file'));

        return back()->with('success', __('Bulk Import Successfully'));
    }

    private function exportCustomerList()
    {
        return Excel::download(
            new \Modules\Whatsapp\App\Exports\CustomerListExport('whatsapp'),
            'whatsapp-customers-list_' . now() . '.csv',
            \Maatwebsite\Excel\Excel::CSV,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}
