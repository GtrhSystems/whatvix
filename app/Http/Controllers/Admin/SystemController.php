<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Toastr;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    public function clearCache()
    {
        Artisan::call('optimize:clear');
        Toastr::success(__('Cache cleared successfully.'));
        return redirect('/');
    }

    public function reset()
    {
        Artisan::call('migrate:fresh --seed');
        Artisan::call('optimize:clear');
        Toastr::success(__('System reset successfully.'));
        return redirect('/');
    }
}
