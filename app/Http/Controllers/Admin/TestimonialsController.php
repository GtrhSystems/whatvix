<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use Throwable;
use App\Models\Post;
use Inertia\Inertia;
use App\Traits\Uploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TestimonialsController extends Controller
{
    use Uploader;

    public function __construct()
    {
        $this->middleware('permission:testimonials');
    }

    public function index()
    {
        PageHeader::set(__('Testimonials'))
            ->addOverview(__('Total Testimonials'), Post::where('type', 'testimonial')->count(), 'bx:grid-alt')
            ->addOverview(__('Active Testimonials'), Post::where('type', 'testimonial')->where('status', 1)->count(), 'bx:check-circle')
            ->addOverview(__('Inactive Testimonials'), Post::where('type', 'testimonial')->where('status', 0)->count(), 'bx:x-circle')
            ->addModal(__('Add New'), 'createModal', 'bx:plus');
        $posts = Post::where('type', 'testimonial')->with('excerpt', 'preview', 'shortDescription')->latest()->paginate(20);
        return Inertia::render('Admin/Testimonial/Index', [
            'posts' => $posts,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reviewer_name' => 'required|max:150',
            'short_description' => 'required|max:150',
            'reviewer_position' => 'required|max:100',
            'star' => 'required|max:100',
            'comment' => 'required|max:500',
            'reviewer_avatar' => 'required|image|max:1024',
        ]);


        DB::beginTransaction();

        $post = new Post;
        $post->title = $request->reviewer_name;
        $post->slug = $request->reviewer_position;
        $post->type = 'testimonial';
        $post->lang = $request->star;
        $post->save();

        $post->excerpt()->create([
            'post_id' => $post->id,
            'key' => 'excerpt',
            'value' => $request->comment,
        ]);

        $preview = $this->saveFile($request, 'reviewer_avatar');

        $post->preview()->create([
            'post_id' => $post->id,
            'key' => 'preview',
            'value' => $preview,
        ]);

        $post->shortDescription()->create([
            'post_id' => $post->id,
            'key' => 'short_description',
            'value' => $request->short_description,
        ]);

        DB::commit();

        return back()->with('success', __('Testimonial added successfully'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|max:150',
            'short_description' => 'required|max:150',
            'slug' => 'required|max:100',
            'lang' => 'required|max:100',
            'excerpt.value' => 'required|max:500',
            'preview.value' => 'nullable|image|max:1024',
        ]);

        $post = Post::with('preview')->findOrFail($id);
        $post->title = $data['title'];
        $post->slug = $data['slug'];
        $post->type = 'testimonial';
        $post->lang = $data['lang'];
        DB::beginTransaction();
        $post->save();

        $post->excerpt()->update([
            'post_id' => $post->id,
            'key' => 'excerpt',
            'value' => $data['excerpt']['value'],
        ]);

        if ($request->hasFile('preview.value')) {
            !empty($post->preview) ? $this->removeFile($post->preview->value) : '';

            $preview = $this->uploadFile('preview.value');

            $post->preview()->update([
                'post_id' => $post->id,
                'key' => 'preview',
                'value' => $preview,
            ]);
        }

        $post->shortDescription()->updateOrCreate(
            [
                'post_id' => $post->id,
                'key' => 'short_description',
            ],
            [
                'value' => $data['short_description'],
            ]
        );

        DB::commit();
        return back()->with('success', __('Testimonial updated successfully'));
    }

    public function destroy($id)
    {
        $post = Post::where('type', 'testimonial')->with('preview')->findOrFail($id);

        if (!empty($post->preview)) {
            $this->removeFile($post->preview->value);
        }

        $post->delete();

        return back()->with('success', __('Testimonial deleted successfully'));
    }
}
