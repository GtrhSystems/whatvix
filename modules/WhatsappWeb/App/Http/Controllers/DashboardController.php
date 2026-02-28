<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Message;
use App\Models\Campaign;
use App\Helpers\PageHeader;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Whatsapp\App\Models\CloudApp;

class DashboardController extends Controller
{
    public function __invoke()
    {
        PageHeader::set('Whatsapp overviews');
        return Inertia::render('Index');
    }
}
