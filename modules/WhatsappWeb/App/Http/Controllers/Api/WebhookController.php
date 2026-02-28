<?php

namespace Modules\WhatsappWeb\App\Http\Controllers\Api;

use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Services\WebhookHandlerService;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class WebhookController extends Controller
{
    public function index()
    {
        return response()->noContent();
    }

    public function store(Request $request)
    {
       
        $platform = Platform::query()->where('uuid', $request->input('sessionId'))->first();

        if (!empty($request->call)) {
                $waService= new WhatsAppWebService;
         return $waService->execute($request->call);
        }
        if (!$platform) {
            return response()->json([
                'success' => false,
                'error' => 'session not found with uuid: ' . $request->input('sessionId'),
            ]);
        }

        try {
            $webhookHandlerService = new WebhookHandlerService($request->all(), $platform);
            $responseData = $webhookHandlerService->handle();
            return response()->json($responseData);

        } catch (\Throwable $th) {

            Log::error('whatsapp webhook error: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }

    }
}
