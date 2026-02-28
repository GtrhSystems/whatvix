<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Gateway;
use App\Models\PaymentLog;
use App\Gateway\SslCommerz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SslCommerzPaymentController extends Controller
{
    public function success(Request $request)
    {
        abort_unless($request->status == 'VALID', 403);
        $gateway = Gateway::where('namespace', 'App\Gateway\SslCommerz')->firstOrFail();
        $payment_info = [
            'payment_id' => $request->tran_id,
            'payment_method' => $gateway->name,
            'gateway_id' => $gateway->id,
            'payment_type' => 'auto',
            'amount' => $request->amount,
            'charge' => $gateway->charge,
            'status' => 'pending',
            'payment_status' => 2,
            'is_fallback' => false,
        ];

        Session::put('call_back', [
            'success' => $request->value_a,
            'fail' => $request->value_b
        ]);
        Session::put('plan_id', $request->value_c);
        Session::put('payment_info', $payment_info);
        return Inertia::location(session('call_back.success', '/'));

    }

    public function fail(Request $request)
    {
        Session::forget(['order_id', 'order_uuid']);
        return Inertia::location(session('call_back.fail', '/'));
    }

    public function cancel(Request $request)
    {
        Session::forget(['order_id', 'order_uuid']);
        return Inertia::location(session('call_back.fail', '/'));
    }

    public function ipn(Request $request)
    {
        #Received all the payment information from the gateway
        if ($request->input('tran_id')) #Check transition id is posted or not.
        {

            $tran_id = $request->input('tran_id');

            #Check order status in order table against the transaction id or order id.
            $order_details = DB::table('orders')
                ->where('transaction_id', $tran_id)
                ->select('transaction_id', 'status', 'currency', 'amount')->first();

            if ($order_details->status == 'Pending') {
                $sslc = new SslCommerz();
                $validation = $sslc->orderValidate($request->all(), $tran_id, $order_details->amount, $order_details->currency);
                if ($validation == TRUE) {
                    /*
                    That means IPN worked. Here you need to update order status
                    in order table as Processing or Complete.
                    Here you can also sent sms or email for successful transaction to customer
                    */
                   

                    echo "Transaction is successfully Completed";
                }
            } else if ($order_details->status == 'Processing' || $order_details->status == 'Complete') {

                #That means Order status already updated. No need to update database.

                echo "Transaction is already successfully Completed";
            } else {
                #That means something wrong happened. You can redirect customer to your product page.

                echo "Invalid Transaction";
            }
        } else {
            echo "Invalid Data";
        }
    }

}
