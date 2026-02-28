<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Post;
use Inertia\Inertia;
use App\Actions\Blog;
use App\Helpers\Toastr;
use App\Models\Category;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\AssetService;
use Illuminate\Support\Facades\Artisan;

class BlogController extends Controller
{
    use Uploader;

    public function __construct()
    {
        $this->middleware('permission:blog');
    }


    public function index(Request $request)
    {
        PageHeader::set()
            ->title('Blog Posts')
            ->addLink(__('Add New'), route('admin.blog.create'), 'bx:plus')
            ->overviews([
                [
                    'value' => Post::query()->where('type', 'blog')->count(),
                    'title' => __('Total Posts'),
                    'icon' => 'bx:bar-chart',
                ],
                [
                    'value' => Post::query()->where('type', 'blog')->where('status', 1)->count(),
                    'title' => __('Active Posts'),
                    'icon' => 'bx:check-circle',
                ],
                [
                    'value' => Post::query()->where('type', 'blog')->where('status', 0)->count(),
                    'title' => __('Inactive Posts'),
                    'icon' => 'bx:x-circle',
                ],
            ]);

        $posts = Post::query()
            ->with([
                'preview',
                'banner'
            ])
            ->filterOn(['title'])
            ->where('type', 'blog')
            ->latest()
            ->paginate(20);

        return Inertia::render(
            'Admin/Blog/Index',
            [
                'posts' => $posts,
                'request' => request()->all(),
            ]
        );
    }


    public function create()
    {
        PageHeader::set()
            ->title('Create Blog Post')
            ->addBackLink(route('admin.blog.index'));

        $tags = Category::whereType('tags')->pluck('title', 'id');
        $categories = Category::whereType('blog_category')->where('status', 1)->pluck('title', 'id');
        $languages = get_option('languages');

        return Inertia::render('Admin/Blog/Create', [
            'tags' => $tags,
            'categories' => $categories,
            'languages' => $languages,
        ]);
    }


    public function store(Request $request, Blog $blog)
    {
        DB::beginTransaction();
        $blog->create($request);
        DB::commit();
        return to_route('admin.blog.index')->with('success', __('Blog post created successfully'));
    }


    public function edit($id)
    {
        PageHeader::set(__('Edit Blog Post'))->addBackLink(route('admin.blog.index'));

        $info = Post::where('type', 'blog')->with('postCategories', 'preview', 'banner', 'seo', 'shortDescription', 'longDescription', 'seo')->findOrFail($id);
        $tags = Category::whereType('tags')->get(['title', 'id']);
        $categories = Category::whereType('blog_category')->get(['title', 'id']);

        $tagsArr = [];
        $cats = [];

        foreach ($info->postCategories as $key => $cat) {
            $category_id = $cat->category_id;

            $tagExists = $tags->where('id', $category_id)->first();

            $categoryExists = $categories->where('id', $category_id)->first();

            if ($tagExists) {
                array_push($tagsArr, $category_id);
            }

            if ($categoryExists) {
                array_push($cats, $category_id);
            }
        }


        $seo = json_decode($info->seo->value ?? '');
        $languages = get_option('languages');

        return Inertia::render('Admin/Blog/Edit', [
            'info' => $info,
            'tags' => $tags,
            'categories' => $categories,
            'cats' => $cats,
            'seo' => $seo,
            'languages' => $languages,
            'tagsArr' => $tagsArr,
        ]);
    }

    public function update($id, Blog $blog)
    {
        $validated = request()->validate([
            'blog.title' => ['required', 'string', 'max:150'],
            'blog.meta_image' => ['nullable', 'image', 'max:1024'],
            'blog.short_description' => ['required', 'max:500'],
            'blog.main_description' => ['required'],
            'blog.meta_title' => ['required', 'max:200'],
            'blog.meta_description' => ['max:1000'],
            'blog.meta_tags' => ['max:200'],
            'blog.categories' => ['required'],
            'blog.tags' => ['required'],
        ]);

        DB::beginTransaction();
        $blog->update($validated['blog'], $id);
        DB::commit();

        return to_route('admin.blog.index')->with('success', __('Blog post updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy(Post $blog, AssetService $assetService)
    {
        $assetService->delete($blog->preview, $blog->banner);
    
        if (!empty($blog->seo)) {
            $seo = json_decode($blog->seo->value);
            if (!empty($seo->image ?? '')) {
                $this->removeFile($seo->image);
            }
        }

        $blog->delete();

        Artisan::call('cache:clear');

        return back()->with(__('Blog Post Deleted Successfully'));
    }

    public function massDestroy(Request $request)
    {
        $request->validate([
            'id' => ['required', 'array']
        ]);

        Post::whereIn('id', $request->input('id'))->delete();

        return response()->json([
            'message' => __('Blog Posts Deleted Successfully'),
            'redirect' => route('admin.blog.index')
        ]);
    }
}
