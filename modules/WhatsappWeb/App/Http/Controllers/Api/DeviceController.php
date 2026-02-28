<?php

namespace Modules\WhatsappWeb\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class DeviceController extends Controller
{
    public function connection(WhatsAppWebService $whatsappService)
    {
        $sessionID = request('uuid');
        $whatsappSessionQr = $whatsappService->addSession($sessionID);

        $data = array_merge($whatsappSessionQr, ['sessionID' => $sessionID]);
        return response()->json($data);
    }
    public function code(WhatsAppWebService $whatsappService)
    {
        $platform = Platform::where('uuid', request('uuid'))
            ->where('owner_id', activeWorkspaceOwnerId())
            ->firstOrFail();
        $sessionID = request('uuid');

        $whatsappSessionQr = $whatsappService
            ->addSession($sessionID, [
                'authType' => 'code',
                'phoneNumber' => $platform->meta['phone_number'],
            ]);

        $data = array_merge($whatsappSessionQr, ['sessionID' => $sessionID]);
        return response()->json($data);
    }

    public function checkStatus(WhatsAppWebService $whatsappService)
    {
        $uuid = request('uuid');
        $response = $whatsappService->getSessionsStatus($uuid);
        $data = $response->json();

        $device = Platform::query()
            ->where('uuid', $uuid)
            ->where('owner_id', activeWorkspaceOwnerId())
            ->firstOrFail();

        if (!$response->successful() || empty($data) || !isset($data['status'])) {
            $device->status = 'inactive';
            $device->save();
            return response()->json(['status' => false, 'data' => $data]);
        }

        $whatsappStatus = $data['status'];

        if ($whatsappStatus !== null) {
            $device->status = $whatsappStatus;
            $device->save();
            return response()->json(['status' => $device->status, 'data' => $data]);
        }

        return response()->json(['status' => false, 'data' => $data, 'message' => 'Unknown WhatsApp status']);
    }
    public function check_verification($uuid, WhatsAppWebService $whatsAppWebService)
    {
        $device = Platform::query()->whatsappWeb()
            ->where('owner_id', activeWorkspaceOwnerId())
            ->where('uuid', $uuid)->first();

        $res = $whatsAppWebService->checkNumber($device->uuid, $device->meta['phone_number']);

        if ($res->successful()) {
            $isNumberExists = $res->json('exists', false);
            if ($isNumberExists) {
                $meta = $device->meta;
                $meta['verified'] = true;
                $device->update(['meta' => $meta]);
            }
            ;
            return response()->json(['exists' => $isNumberExists, 'data' => $res->json()]);
        } else {
            return response()->json(['data' => $res->json()]);
        }
    }
    public function destroy($uuid, WhatsAppWebService $whatsappService)
    {
        $platform = Platform::where('uuid', request('uuid'))
            ->where('owner_id', activeWorkspaceOwnerId())
            ->firstOrFail();
        $platform->status = 'inactive';
        $platform->save();

        $whatsappService->deleteSession($uuid);
        return response()->json(['status' => true]);
    }
}
