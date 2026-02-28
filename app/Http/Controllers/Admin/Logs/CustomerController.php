<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\Customer;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    use Uploader;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Customer::query();


        $overviews = [
            [
                'icon' => "bx:list-ul",
                'value' => $query->clone()->count(),
                'title' => 'Total Audiences'
            ],
            [
                'title' => 'Last 7 Days Audiences',
                'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                'icon' => "bx:history",
            ],
            [
                'title' => 'Last 30 Days Audiences',
                'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                'icon' => "bx:calendar",
            ],
        ];

        $customers = $query->with(['groups:id,name', 'owner:id,name'])->latest()->paginate();

        PageHeader::set()
            ->title('Customers')
            ->overviews($overviews);

        return Inertia::render('Admin/Logs/Customers/Index', compact(
            'customers',
        ));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', __('Customer Deleted Successfully'));
    }
}
