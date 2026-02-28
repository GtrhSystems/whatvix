<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use App\Rules\Phone;
use Inertia\Inertia;
use App\Models\Group;
use App\Helpers\Toastr;
use App\Models\Customer;
use App\Models\Platform;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use App\Models\WebScraping;
use Illuminate\Http\Request;
use App\Models\WebScrapedData;
use App\Services\AssetService;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomerListExport;
use App\Helpers\PlanPerks;
use libphonenumber\PhoneNumberUtil;
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
        $query = $user->customers()->filterOn(['name', 'uuid'])->whatsappWeb();

        $customers = $query
            ->whatsappWeb()
            ->latest()
            ->with('groups')
            ->paginate();

        $groups = $user->groups()
            ->whatsappWeb()
            ->select('id as value', 'name as label')
            ->latest()
            ->get();

        $overviews = [
            [
                'icon' => "bx:list-ul",
                'title' => 'Total Contact',
                'value' => $query->clone()->count(),
            ],
            [
                'icon' => "bx:checkbox-checked",
                'title' => 'Last 7 Days',
                'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
            ],
            [
                'icon' => "hugeicons:limitation",
                'title' => 'Max Contact',
                'value' => PlanPerks::planValue('contacts'),
            ],
        ];

        PageHeader::set()
            ->title('Customers')
            ->overviews($overviews)
            ->addModal('Import From Device', 'importFromDeviceModal', 'bx:mobile')
            ->addModal('Import CSV', 'importModal', 'bx:file')
            ->addModal('Import ScrapeData', 'importFromScrapeDataModal', 'bx:globe')
            ->addLink(
                __("Export CSV"),
                route('user.whatsapp-web.customers.index', ['export' => true]),
                'bx-download',
                '_blank'
            )
            ->addLink(__("Add New"), route('user.whatsapp-web.customers.create'), 'bx-plus');

        $platforms = $user->platforms()->whatsappWeb()->select(['id as value', 'name as label'])->get();
        $scraped_record = WebScraping::where('user_id', $user->id)
            ->select(['id as value', 'title as label'])
            ->where('module', 'whatsapp-web')
            ->get();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'groups' => $groups,
            'platforms' => $platforms,
            'scraped_record' => $scraped_record
        ]);
    }

    public function create()
    {

        PageHeader::set()->title('Create Contact Number')
            ->addBackLink(route('user.whatsapp-web.customers.index'));

        $groups = activeWorkspaceOwner()
            ->groups()
            ->whatsappWeb()
            ->select('id as value', 'name as label')
            ->latest()
            ->get();

        return Inertia::render('Customers/Create', compact('groups'));
    }

    public function store(Request $request, AssetService $assetService)
    {
        $validated = $request->validate(
            [
                'name' => 'required|max:200',
                'phone' => ['required', new Phone],
                'picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'group_ids' => 'required|array',
                'groups_ids.*' => 'numeric|exists:groups,id',
            ],
            [],
            [
                'group_ids' => 'groups'
            ]
        );


        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();
        if ($request->hasFile('picture')) {
            $picture = $assetService->upload('image');
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        $formattedNumber = $phoneUtil->parse($request->phone, "BD");

        $customer = $user->customers()->create([
            'module' => 'whatsapp-web',
            'name' => $validated['name'],
            'picture' => $picture?->path ?? null,
            'uuid' => str($request->phone)->remove('+'),
            'meta' => [
                'dial_code' => $formattedNumber->getCountryCode(),
                'phone' => $formattedNumber->getNationalNumber(),
            ]

        ]);

        if (isset($validated['group_ids']) && count($validated['group_ids'])) {
            $customer->groups()->sync($validated['group_ids']);
        }

        Toastr::success('Created Successfully');

        return to_route('user.whatsapp-web.customers.index');
    }

    public function edit(Customer $customer)
    {
        PageHeader::set(
            title: 'Edit customer information',
            buttons: [
                [
                    'text' => 'Back',
                    'url' => route('user.whatsapp-web.customers.index'),

                ]
            ]
        );
        $groupIds = $customer->groups()->pluck('id');
        $groups = Group::select('id as value', 'name as label')->latest()->get();

        return Inertia::render(
            'Customers/Edit',
            compact('groups', 'groupIds', 'customer')
        );
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|max:200',
            'phone' => ['required', new Phone],
            'group_ids' => 'nullable|array',
            'groups_ids.*' => 'numeric|exists:groups,id',
        ]);


        if ($request->hasFile('picture')) {

            $request->validate([
                'picture' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $validated['picture'] = $this->saveFile($request, 'picture');

            if ($customer->picture) {
                $this->removeFile($customer->picture);
            }
        }

        $customer->update([
            'name' => $validated['name'],
            'picture' => $this->uploadFile('picture', $customer->picture),
            'uuid' => str($request->phone)->remove('+'),
        ]);

        if (isset($validated['group_ids']) && count($validated['group_ids'])) {
            $customer->groups()->sync($validated['group_ids']);
        }

        return to_route('user.whatsapp-web.customers.index')->with('success', 'Updated Successfully ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('danger', __('Customer Deleted Successfully'));
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'group_ids' => ['required', 'array'],
            'group_ids.*' => ['numeric', 'exists:groups,id'],
            'csv_file' => ['required', 'file'],
        ]);

        Excel::import(
            new CustomerListImport('whatsapp-web'),
            $request->file('csv_file')
        );

        return back()->with('success', __('Bulk Import Successfully'));
    }
    public function importFromDevice(Request $request)
    {
        $request->validate([
            'platform_ids' => ['required', 'array'],
            'platform_ids.*' => ['required', 'numeric', 'exists:platforms,id', 'distinct'],
            'group_ids' => ['required', 'array'],
            'group_ids.*' => ['required', 'numeric', 'exists:groups,id', 'distinct'],
        ]);

        $total = 0;

        foreach ($request->platform_ids as $platformId) {
            $platform = Platform::findOrFail($platformId);
            $platformContacts = DB::table('contact')
                ->where('sessionId', $platform->uuid)
                ->get();

            foreach ($platformContacts as $contact) {
                $name = $contact->name ?? $contact->notify ?? $contact->verifiedName ?? 'Unknown';
                $picture = $contact->imgUrl;
                $phone = str($contact->id)->before('@')->toString();

                $isPhone = str($contact->id)->contains('@s.whatsapp.net');
                $isValidPhone = $isPhone && str($phone)->length() >= 8;

                if (!$isValidPhone) {
                    continue;
                }

                $phoneUtil = PhoneNumberUtil::getInstance();
                $formattedNumber = $phoneUtil->parse($phone, "BD");

                $customer = $platform->customers()->updateOrCreate(
                    [
                        'module' => 'whatsapp-web',
                        'owner_id' => $platform->owner_id,
                        'uuid' => $phone
                    ],
                    [
                        'name' => $name,
                        'picture' => $picture,
                        'meta' => [
                            'dial_code' => $formattedNumber->getCountryCode(),
                            'phone' => $formattedNumber->getNationalNumber(),
                        ]
                    ]
                );
                $customer->groups()->sync($request->group_ids);
                if ($customer->wasRecentlyCreated) {
                    $total++;
                }
            }
        }

        return back()->with('success', $total . __(' Customers Imported Successfully'));
    }
    public function importFromScrapeData(Request $request)
    {
        $request->validate([
            'scraped_record_ids' => ['required', 'array'],
            'scraped_record_ids.*' => ['numeric', 'exists:web_scrapings,id', 'distinct'],
            'group_ids' => ['required', 'array'],
            'group_ids.*' => ['required', 'numeric', 'exists:groups,id', 'distinct', 'min:1'],
        ]);

        $total = 0;


        $records = WebScraping::whereIn('id', $request->scraped_record_ids)->get();
        $scraped_data = WebScrapedData::whereIn('web_scraping_id', $records->pluck('id'))->get();
        foreach ($scraped_data as $data) {
            $contact = $data->data;

            if (isset($contact['phone_number'])) {

                $phoneUtil = PhoneNumberUtil::getInstance();
                $formattedNumber = $phoneUtil->parse($contact['phone_number'], "BD");

                $customer = Customer::updateOrCreate(
                    [
                        'module' => 'whatsapp-web',
                        'owner_id' => $data->web_scraping->user_id,
                        'uuid' => str_replace([' ', '-', '+'], '', $contact['phone_number'])
                    ],
                    [
                        'name' => $contact['name'],
                        'picture' => $contact['icon'][0] ?? null,
                        'module' => 'whatsapp-web',
                        'meta' => [
                            'dial_code' => $formattedNumber->getCountryCode(),
                            'phone' => $formattedNumber->getNationalNumber(),
                        ]
                    ]
                );
                $customer->groups()->sync($request->group_ids);
                if ($customer->wasRecentlyCreated) {
                    $total++;
                }
            }
        }


        return back()->with('success', $total . __(' Contacts Imported Successfully'));
    }

    private function exportCustomerList()
    {
        return Excel::download(
            new CustomerListExport('whatsapp-web'),
            'whatsapp-web-customers-list_' . now() . '.csv',
            \Maatwebsite\Excel\Excel::CSV,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}