<?php

namespace App\Http\Controllers\Admin;

use App\Models\CreditLog;
use Inertia\Inertia;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\AiGenerate;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class DashboardController extends Controller
{

    public function __invoke(WhatsAppWebService $whatsAppWebService)
    {
        PageHeader::set(
            title: __("Dashboard")
        );

        $recentCreditLogs = CreditLog::query()
            ->with(['user:id,name,created_at,role', 'gateway'])
            ->take(6)->get();

        $isWaServerActive = true;
        try {
            $whatsAppWebService->sessionList();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $isWaServerActive = false;
        }
        $wa_Server_url = env('WHATSAPP_WEB_API_BASE_URL');
        return Inertia::render('Admin/Dashboard', [
            'recentCreditLogs' => $recentCreditLogs ?? [],
            'isWaServerActive' => $isWaServerActive,
            'wa_Server_url' => $wa_Server_url

        ]);
    }
}
