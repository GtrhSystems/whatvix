<?php

namespace App\Actions;

use App\Models\Post;
use App\Services\AssetService;
use App\Traits\Uploader;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class Blog
{
    use Uploader;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $assetService = new AssetService();
        $post = new Post();
        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
        $post->type = 'blog';
        $post->featured = isset($request->featured) ? 1 : 0;
        $post->status = isset($request->status) ? 1 : 0;
        $post->lang = $request->language ?? 'en';
        $post->save();

        $categories = array_merge($request->categories ?? [], $request->tags ?? []);
        if (!empty($categories)) {
            $post->categories()->sync($categories);
        }

        $preview = $this->saveFile($request, 'preview');
        $banner = $this->saveFile($request, 'banner');

        $post->meta()->create([
            'key' => 'preview',
            'value' => $preview
        ]);

        $post->meta()->create([
            'key' => 'banner',
            'value' => $banner
        ]);

        $post->meta()->create([
            'key' => 'short_description',
            'value' => $request->short_description
        ]);

        $post->meta()->create([
            'key' => 'main_description',
            'value' => $request->main_description
        ]);

        $seo['title'] = $request->meta_title;
        $seo['description'] = $request->meta_description;
        $seo['tags'] = $request->meta_tags;

        if ($request->hasFile('meta_image')) {
            $metaPreview = $this->saveFile($request, 'meta_image');
            $seo['image'] = $metaPreview;
        }

        $post->meta()->create([
            'key' => 'seo',
            'value' => json_encode($seo)
        ]);

        Artisan::call('cache:clear');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($request, $id)
    {
       
        $assetService = new AssetService();
        $post = Post::where('type', 'blog')->with('preview', 'seo')->findOrFail($id);
        $post->title = $request['title'];
        $post->slug = Str::slug($request['title']);
        $post->type = 'blog';
        $post->featured = request()->blog['featured'] ? 1 : 0;
        $post->status = request()->blog['status'] ? 1 : 0;
        $post->lang = request()->blog['language'] ?? 'en';
        $post->save();
        $categories = array_merge(request()->blog['categories'], request()->blog['tags']);
        $post->categories()->sync($categories ?? []);

        if (request()->hasFile('blog.preview')) {
            $preview = $this->uploadFile('blog.preview', $post->preview->value ?? '');

            $this->unlinkPublicFile($post->preview->value ?? '');

            $post->meta()->where('key', 'preview')->updateOrCreate(
                ['key' => 'preview'],
                ['value' => $preview]
            );
        }

        if (request()->hasFile('blog.banner')) {
            $banner = $this->uploadFile('blog.banner', $post->banner->value ?? '');

            $this->unlinkPublicFile($post->banner->value ?? '');

            $post->meta()->where('key', 'banner')->updateOrCreate(
                ['key' => 'banner'],
                ['value' => $banner]
            );
        }

        if ($post->meta()->where('key', 'short_description')->exists()) {
            $post->shortDescription()->update([
                'key' => 'short_description',
                'value' => $request['short_description']
            ]);
        } else {
            $post->shortDescription()->create([
                'key' => 'short_description',
                'value' => $request['short_description']
            ]);
        }

        if ($post->meta()->where('key', 'main_description')->exists()) {
            $post->longDescription()->update([
                'key' => 'main_description',
                'value' => $request['main_description']
            ]);
        } else {
            $post->longDescription()->create([
                'key' => 'main_description',
                'value' => $request['main_description']
            ]);
        }

        $seo['title'] = $request['meta_title'];
        $seo['description'] = $request['meta_description'];
        $seo['tags'] = $request['meta_tags'];


        if (request()->hasFile('blog.meta_image')) {
            $metaPreview = $this->saveFile(request(), 'blog.meta_image');

            $metaSeo = json_decode($post->seo->value ?? '');
            if (isset($metaSeo->image)) {
                if (!empty($metaSeo->image)) {
                    $this->removeFile($metaSeo->image);
                }
            }

            $seo['image'] = $metaPreview;
        }

        if ($post->meta()->where('key', 'seo')->exists()) {
            $post->seo()->update([
                'key' => 'seo',
                'value' => json_encode($seo)
            ]);
        } else {
            $post->seo()->create([
                'key' => 'seo',
                'value' => json_encode($seo)
            ]);
        }

        Artisan::call('cache:clear');
    }
}
