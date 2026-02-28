<?php

namespace App\Http\Controllers\Api;

use App\Models\Asset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AssetController extends Controller
{

    public function index()
    {
        $query = Asset::query()
            ->where('user_id', request()->user()->id)
            ->where('type', 'uploaded')
            ->where('file_type', request('file_type'))
            ->whereNull('original_asset_id');
        if (request()->filled('newest')) {
            $assets = $query
                ->where('id', '>', request()->newest)
                ->latest()
                ->get();
            return response()->json($assets);
        }
        $assets = $query
            ->latest()
            ->paginate(16);

        return response()->json($assets);
    }
}
