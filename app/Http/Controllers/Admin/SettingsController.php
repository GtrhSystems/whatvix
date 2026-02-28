<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Actions\OptionUpdate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    use Uploader;


    public function __construct()
    {
        $this->middleware('permission:page-settings');
    }
    public function index()
    {
        Cache::flush();
        PageHeader::set('Page Settings');

        $primary_data = get_option_with_locale('primary_data', includeDefault: true);
        $home_page = get_option_with_locale('home_page', includeDefault: true);
        $about_page = get_option_with_locale('about_page', includeDefault: true);
        $auth_pages = get_option_with_locale('auth_pages', includeDefault: true);
        $contact_page = get_option_with_locale('contact_page', includeDefault: true);
        $service_page = get_option_with_locale('service_page', includeDefault: true);
        $team_page = get_option_with_locale('team_page', includeDefault: true);
        $blog_page = get_option_with_locale('blog_page', includeDefault: true);
        $pricing_page = get_option_with_locale('pricing_page', includeDefault: true);

        return Inertia::render('Admin/PageSetting/Index', [
            'primary_data' => $primary_data,
            'auth_pages' => $auth_pages,
            'home_page' => $home_page,
            'about_page' => $about_page,
            'blog_page' => $blog_page,
            'contact_page' => $contact_page,
            'service_page' => $service_page,
            'team_page' => $team_page,
            'pricing_page' => $pricing_page
        ]);
    }

    public function update($id)
    {
        $optionUpdate = new OptionUpdate();
        $optionUpdate->update($id);
        Cache::flush();
        return back()->with('success', __('Updated Successfully'));
    }
}
