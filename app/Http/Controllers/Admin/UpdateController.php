<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Traits\Dotenv;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class UpdateController extends Controller
{
    use Dotenv;

    public function __construct()
    {
        $this->middleware('permission:developer-settings');
    }

    public function index()
    {
        PageHeader::set()->title(__('App Update'));
        $updateData = Session::get('update-data');

        $appVersion = env('APP_VERSION');
        $purchaseKey = env('SITE_KEY');

        return Inertia::render('Admin/Update/Index', [
            'version' => $appVersion,
            'purchaseKey' => $purchaseKey,
            'updateData' => $updateData
        ]);
    }

    public function store()
    {
        if (env('SITE_KEY') == null) {
            return back()->with('danger', 'item purchase key is required');
        }
        $body['purchase_key'] = env('SITE_KEY');
        $body['url'] = url('/');
        $body['current_version'] = env('APP_VERSION', 1);

        $response = Http::post('https://devapi.lpress.xyz/api/check-update', $body);

        $body = json_decode($response->body());

        if ($response->status() != 200) {
            return back()->with('danger', $body->message);
        }

        Session::put('update-data', [
            'message' => $body->message,
            'version' => $body->version
        ]);
        return back();
    }

    public function update($version)
    {

        $site_key = env('SITE_KEY');
        $body['purchase_key'] = $site_key;
        $body['url'] = url('/');
        $body['version'] = $version;

        $response = Http::post('https://devapi.lpress.xyz/api/pull-update', $body);
        $response = json_decode($response->body());

        foreach ($response->updates ?? [] as $key => $row) {
            if ($row->type == 'file') {
                $fileData = Http::get($row->file);
                $fileData = $fileData->body();

                File::put(base_path($row->path), $fileData);
            } elseif ($row->type == 'folder') {
                $path = $row->path . '/' . $row->name;

                if (!File::exists(base_path($path))) {
                    File::makeDirectory(base_path($path), 0777, true, true);
                }
            } elseif ($row->type == 'command') {
                Artisan::call($row->command);
            }
        }

        $this->editEnv('APP_VERSION', $version);

        Session::forget('update-data');
        return back()->with('success', 'Successfully updated to ' . $version);
    }
}
