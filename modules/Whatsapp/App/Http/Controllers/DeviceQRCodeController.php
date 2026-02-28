<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Platform;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Whatsapp\App\Models\PlatformQrCode;

class DeviceQRCodeController extends Controller
{

    public function index(Platform $device)
    {

        PageHeader::set()->title('Devices Qr Code')->buttons([
            [
                'url' => route('user.whatsapp.platforms.index'),
                'text' => 'Back  to Devices'
            ],
            [
                'text' => __('Sync QR Codes'),
                'url' => route('user.whatsapp.qr-codes.index', ['device' => $device, 'sync' => 1]),
            ]
        ])->addModal(__('Create New'), 'qrCodeModal');

        if (request('sync')) {
            $device->syncQrCodes();
            return back();
        }

        $qrCodes = $device->qrCodes()->latest()->paginate();

        return Inertia::render('Platforms/qr-codes/Index', [
            'device' => $device,
            'qrCodes' => $qrCodes,
        ]);
    }
    public function store(Request $request, Platform $device)
    {
        $request->validate([
            'prefilled_message' => ['required', 'string'],
            'generate_qr_image' => ['required', 'string', 'in:SVG,PNG'],
        ]);

        $device->createQrCodes($request->prefilled_message, $request->get('generate_qr_image'));

        return back()->with('success', __('Successfully created'));
    }

    public function destroy(Platform $device, PlatformQrCode $qrCode)
    {
        $device->removeQRCodes($qrCode->code);
        return back()->with('success', __('Deleted successfully'));
    }
}
