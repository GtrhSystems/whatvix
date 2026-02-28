<?php

namespace Modules\Whatsapp\App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Whatsapp\App\Services\CloudAppService;

class WhatsAppSMSController extends Controller
{

    public function sendMessage(Request $request, CloudAppService $cloudAppService)
    {
        try {
            $response = $cloudAppService->sendMessage($request->all());
            return response()->json(['success' => true, 'data' => $response]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
