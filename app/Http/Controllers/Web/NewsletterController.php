<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Helpers\SeoMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Newsletter\Facades\Newsletter;

class NewsletterController extends Controller
{
    public function subscribed()
    {
        SeoMeta::set('title', __('Subscribed Successfully'));
        return Inertia::render('Web/Subscribed');
    }

    public function subscribe(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        //   Newsletter::subscribe($request->email);
        return back()->with('success', __('Subscribed Successfully'));
    }
}
