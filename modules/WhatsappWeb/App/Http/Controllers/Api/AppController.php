<?php

namespace Modules\WhatsappWeb\App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Models\WhatsappWebApp;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{

    public function sendMessage(Request $request, WhatsAppWebService $whatsAppWebService)
    {
        $request->validate([
            'app_key' => ['required', 'exists:whatsapp_web_apps,key'],
            'auth_key' => ['required', 'exists:users,authKey'],
            'to' => ['required', 'string'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $appUser = User::query()
            ->where(
                'authKey',
                $request->get('auth_key')
            )
            ->first();

        $app = WhatsappWebApp::query()
            ->where('user_id', $appUser?->id)
            ->where(
                'key',
                $request->get('app_key')
            )
            ->first();

        if (!$app || !$appUser) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication failed'
            ], 401);
        }

        $platformUuid = $app->platform?->uuid;

        if (!$platformUuid) {
            return response()->json([
                'success' => false,
                'error' => 'Platform not found'
            ], 404);
        }

        $jid = $request->get('to') . '@s.whatsapp.net';

        $message = [
            'text' => $request->get('message')
        ];

        DB::beginTransaction();
        try {

            $res = $whatsAppWebService->sendMessage(
                $platformUuid,
                $jid,
                $message,
            )->throw();

            $app->logs()->create([
                'owner_id' => $appUser->id,
                'platform_id' => $app->platform_id,
                'to' => $request->get('to'),
                'status_code' => $res->status(),
                'request' => [
                    'sessionId' => $platformUuid,
                    'jid' => '',
                    'message' => $message
                ],
                'response' => $res->json()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $res->json()
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
