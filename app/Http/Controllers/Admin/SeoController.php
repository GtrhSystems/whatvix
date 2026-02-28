<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use Inertia\Inertia;
use App\Models\Option;
use App\Traits\Uploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{

    use Uploader;

    public function __construct()
    {
        $this->middleware('permission:seo');
    }

    public function index()
    {
        PageHeader::set('Seo Settings');
        $posts = Option::where('key', 'LIKE', '%seo%')->get()->map(function ($option) {
            $data['id'] = $option->id;
            $data['key'] = str_replace('_', ' ', str_replace('seo_', '', $option->key));
            $data['content'] = $option->value ?? [];
            return $data;
        });

        return Inertia::render('Admin/Seo/Index', compact('posts'));
    }


    public function edit($id)
    {
        $seo = Option::where('key', 'LIKE', '%seo%')->findOrFail($id);
        $contents = $seo->value ?? [];

        $segments = request()->segments();
        $buttons = [
            [
                'text' => __('Back'),
                'url' => route('admin.seo.index'),
            ]
        ];

        return Inertia::render('Admin/Seo/Show', [
            'id' => $id,
            'contents' => $contents,
            'buttons' => $buttons,
            'segments' => $segments,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'preview' => ['nullable', 'image', 'max:1024']
        ]);

        $option = Option::where('id', $id)->firstOrNew();
        $meta = $request->except('_method') ?? $option->value ?? [];

        if ($request->hasFile('preview')) {
            $this->removeFile($meta['preview']);
            $meta['preview'] = $this->uploadFile('preview');
        }

        $option->value = $meta;
        $option->save();

        Cache::forget($option->key);

        return to_route('admin.seo.index');
    }
}
