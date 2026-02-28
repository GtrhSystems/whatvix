<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Page;
use App\Helpers\PageHeader;
use App\Models\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Traits\Uploader;

class GatewayController extends Controller
{
    use Uploader;

    public function __construct()
    {
        $this->middleware('permission:gateways');
    }

    public function index()
    {
        $gateways = Gateway::query()->get();
        PageHeader::set(
            title: 'Payment Gateways',
            overviews: [
                [
                    'icon' => "bx:bar-chart",
                    'title' => 'Total Gateways',
                    'value' => $gateways->count(),
                ],
                [
                    'icon' => "bx:check-circle",
                    'title' => 'Active Gateways',
                    'value' => $gateways->where('status', 1)->count(),
                ],
                [
                    'icon' => "bx:x-circle",
                    'title' => 'Inactive Gateways',
                    'value' => $gateways->where('status', 0)->count(),
                ],
            ]
        )->addLink(__('Create A Manual Gateway'), route('admin.gateways.create'), 'bx:plus');

        return Inertia::render('Admin/Gateway/Index', [
            'gateways' => $gateways,
        ]);
    }

    public function create()
    {
        PageHeader::set(__('Create Payment Gateways'))->addBackLink(route('admin.gateways.index'));
        return Inertia::render('Admin/Gateway/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:gateways,name',
            'comment' => 'max:1000',
            'logo' => 'nullable|image|max:1024',
            'charge' => 'required',
            'currency' => 'required',
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['required', 'numeric', 'min:0', 'gte:min_amount'],
        ]);

        $gateway = new Gateway();

        if ($request->hasFile('logo')) {
            $gateway->logo = $this->saveFile($request, 'logo');
        }

        $gateway->currency = $request->currency;
        $gateway->name = $request->name;
        $gateway->charge = $request->charge;
        $gateway->multiply = $request->multiply ?? 0;
        $gateway->namespace = 'App\Gateway\CustomGateway';
        $gateway->is_auto = 0;
        $gateway->image_accept = $request->image_accept;
        $gateway->status = $request->status;
        $gateway->comment = $request->comment;
        $gateway->save();

        return to_route('admin.gateways.index');
    }

    public function edit($id)
    {
        PageHeader::set(__('Edit Payment Gateways'))->addBackLink(route('admin.gateways.index'));

        $segments = request()->segments();
        $buttons = [
            [
                'text' => __('Back'),
                'url' => route('admin.gateways.index'),
            ]
        ];

        $gateway = Gateway::findOrFail($id);
        if ($gateway->is_auto == 1) {
            $credentials = json_decode($gateway->data ?? '');
        } else {
            $credentials = [];
        }


        return Inertia::render('Admin/Gateway/Edit', compact('gateway', 'credentials'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|unique:gateways,name,' . $id,
            'logo' => 'nullable|image|max:1000',
            'charge' => 'required',
            'namespace' => 'nullable',
            'currency' => 'required',
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['required', 'numeric', 'min:0', 'gte:min_amount'],
        ]);

        $gateway = Gateway::findOrFail($id);

        if ($gateway->is_auto == 0) {
            $request->validate([
                'comment' => 'required',
            ]);
        } else {
            $gateway->data = $request->credentials ? json_encode($request->credentials) : '';
        }
        if ($request->hasFile('logo')) {
            $this->removeFile($gateway->logo);
            $gateway->logo = $this->saveFile($request, 'logo');
        }

        $gateway->comment = $request->comment;
        $gateway->name = $request->name;
        $gateway->charge = $request->charge;
        $gateway->multiply = $request->multiply ?? 0;
        $gateway->currency = $request->currency;
        $gateway->test_mode = $request->test_mode ?? 0;
        $gateway->log_enabled = $request->log_enabled ?? 0;
        $gateway->status = $request->status;
        $gateway->min_amount = $request->min_amount;
        $gateway->max_amount = $request->max_amount;
        $gateway->save();

        return to_route('admin.gateways.index');
    }
}
