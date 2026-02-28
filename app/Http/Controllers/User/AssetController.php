<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Models\Asset;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AssetService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class AssetController extends Controller
{
    use Uploader;

    public function index()
    {
        $query = Asset::query()
            ->where('user_id', Auth::id());
        PageHeader::set(
            title: 'User Assets',
        )->overviews([
                    [
                        'title' => 'Images',
                        'value' => Number::fileSize($query->clone()->where('file_type', 'image')->sum('file_size')),
                        'icon' => 'bx-image',
                    ],
                    [
                        'title' => 'Videos',
                        'value' => Number::fileSize($query->clone()->where('file_type', 'video')->sum('file_size')),
                        'icon' => 'bx-video',
                    ],
                    [
                        'title' => 'Audios',
                        'value' => Number::fileSize($query->clone()->where('file_type', 'audio')->sum('file_size')),
                        'icon' => 'bx-music',
                    ],
                    [
                        'title' => 'Documents',
                        'value' => Number::fileSize($query->clone()->where('file_type', 'document')->sum('file_size')),
                        'icon' => 'bx-file',
                    ],
                ]);

        $assets = $query->clone()
            ->filterOn(['file_size', 'file_type'])
            ->latest()
            ->paginate(14);

        return Inertia::render('User/Assets', [
            'assets' => $assets,
        ]);
    }

    public function store(Request $request, AssetService $assetService)
    {
        validateUserPlan('storage');

        if (!$request->has('files') || !is_array($request->input('files'))) {
            return back()->with('error', 'No files were uploaded.');
        }

        $asset = $assetService->upload('files');

        if ($asset) {
            return back()->with('success', 'Asset(s) uploaded successfully.');
        }

        return back()->with('error', 'File upload failed.');
    }


    public function destroy(Asset $asset)
    {
        $this->removeFile($asset->path);
        $asset->delete();
        return back();
    }
}
