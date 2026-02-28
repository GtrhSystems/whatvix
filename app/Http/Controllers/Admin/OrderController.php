<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Traits\Notifications;
use Inertia\Inertia;
use App\Models\Plan;

class OrderController extends Controller
{
    use Notifications;

    public function __construct()
    {
        $this->middleware('permission:order');
    }

    public function index(Request $request)
    {
        $orders = Order::query()->filterOn(['invoice_no', 'status'])
            ->FilterOnRelation(['user_email'])
            ->with('user', 'plan', 'gateway')->latest()->paginate(20);

        $totalOrders = Order::count();
        $totalPendingOrders = Order::where('status', 2)->count();
        $totalCompleteOrders = Order::where('status', 1)->count();
        $totalDeclinedOrders = Order::where('status', 0)->count();
        $type = $request->type ?? 'email';

        $invoice = get_option('invoice_data');
        $tax = get_option('tax');
        $currency = get_option('base_currency');
        PageHeader::set(__('Orders'))
            ->overviews([
                [
                    'value' => $totalOrders,
                    'title' => __('Total Orders'),
                    'icon' => 'bx:badge-check'
                ],
                [
                    'value' => $totalPendingOrders,
                    'title' => __('Pending Orders'),
                    'icon' => 'bx:badge'
                ],
                [
                    'value' => $totalCompleteOrders,
                    'title' => __('Approved Orders'),
                    'icon' => 'bx:check'
                ],
                [
                    'value' => $totalDeclinedOrders,
                    'title' => __('Rejected Orders'),
                    'icon' => 'bx:x-circle'
                ],
            ])
            ->addModal(
                __('Invoice Settings'),
                'invoiceSettingModal',
            )
            ->addModal(
                __('Plan Currency Settings'),
                'currencySettingModal',
            )
            ->addModal(
                __('Tax Settings'),
                'taxSettingModal',
            )
        ;

        return Inertia::render('Admin/Order/Index', [
            'orders' => $orders,
            'request' => $request,
            'type' => $type,
            'invoice' => $invoice,
            'currency' => $currency,
            'tax' => $tax
        ]);
    }


    public function show(Request $request, $id)
    {
        $segments = request()->segments();
        $buttons = [
            [
                'name' => __('Orders'),
                'url' => '/admin/order'
            ],

        ];
        $order = Order::with('user', 'plan', 'gateway')->findOrFail($id);
        $invoice_data = get_option('invoice_data');
        $meta = json_decode($order->meta ?? '');

        return Inertia::render('Admin/Order/Show', compact('order', 'invoice_data', 'segments', 'buttons', 'meta'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::with('user', 'plan')->findOrFail($id);
        $order->status = $request->status;
        $order->save();
        $plan=Plan::findOrFail($order->plan_id);

        if ($request->assign_order == 'yes' && $order->status == 'approved') {
            $order->user()->update([
                'plan_id' => $order->plan_id,
                'will_expire' => $order->will_expire,
                'plan_data' => $order->plan->data ?? '',
                 'credits' => $order->user->credits + $plan->data['credits']['value'] ?? 0
            ]);
        }


        $status = $order->status == 'pending' ? 'pending' : ($order->status == 'approved' ? 'approved' : 'declined');
        $title = '(' . $order->invoice_no . ') Subscription order is ' . $status;

        $notification['user_id'] = $order->user_id;
        $notification['title'] = $title;
        $notification['url'] = '/user/subscription';

        $this->createNotification($notification);

        return back()->with('success', 'Updated Successfully');
    }
}