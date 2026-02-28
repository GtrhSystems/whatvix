<?php

namespace App\Http\Controllers;

use App\Models\CardOrder;
use App\Models\Deposit;
use App\Models\Order;
use App\Models\TopUp;
use Illuminate\Http\Request;

class CoingateCallbackController extends Controller
{

    public function orderCallback(Request $request, string $oderId)
    {
        $order = Order::find($oderId);
        $order->update([
            'payment_status' => $request->status
        ]);

        return response()->json(['success' => true]);
    }
    public function cardPayCallback(Request $request, string $oderId)
    {
        $order = CardOrder::find($oderId);
        $order->update([
            'payment_status' => $request->status
        ]);

        return response()->json(['success' => true]);
    }
    public function cardDepositCallback(Request $request, string $oderId)
    {
        $deposit = Deposit::find($oderId);
        $deposit->update([
            'payment_status' => $request->status
        ]);

        return response()->json(['success' => true]);
    }
    public function topUpCallback(Request $request, string $oderId)
    {
        $topUp = TopUp::find($oderId);
        $topUp->update([
            'payment_status' => $request->status
        ]);

        return response()->json(['success' => true]);
    }
}
