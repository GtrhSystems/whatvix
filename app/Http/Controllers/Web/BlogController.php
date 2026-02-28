<?php

namespace App\Http\Controllers\Web;

use App\Models\Post;
use Inertia\Inertia;
use App\Models\Category;
use App\Helpers\SeoMeta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::with([
            'preview',
            'shortDescription',
            'categories' => function ($query) {
                $query->select('id', 'title', 'slug');
            }
        ])
            ->when(request('s'), function ($query) {
                $query->where('title', 'like', '%' . request('s') . '%');
            })
            ->withCount('postCategories')
            ->where('status', 1)
            ->where('lang', current_locale())
            ->where('type', 'blog')
            ->latest()
            ->paginate(4);

        $categories = Category::where('type', 'blog_category')->whereHas('postCategories')->withCount('postCategories')->where('status', 1)->where('lang', current_locale())->get();
        $tags = Category::where('type', 'tags')->whereHas('postCategories')->where('status', 1)->where('lang', current_locale())->get();

        $recent_posts = Post::where('type', 'blog')
            ->where('lang', current_locale())
            ->with([
                'shortDescription',
                'preview',
                'categories' => function ($query) {
                    $query->select('title');
                }
            ])
            ->where('status', 1)
            ->latest()
            ->take(4)
            ->get();

        $seo = SeoMeta::init('seo_blogs');
        $blog_page = get_option('blog_page', true);

        return Inertia::render('Web/Blogs/Index', compact('posts', 'categories', 'tags', 'recent_posts', 'blog_page'));
    }


    public function show(Post $blog)
    {

        $blog->load('shortDescription', 'longDescription', 'tags', 'preview', 'banner', 'seo', 'categories');

        $categories = Category::where('type', 'blog_category')->whereHas('postCategories')->withCount('postCategories')->where('status', 1)->where('lang', current_locale())->get();

        $tags = Category::where('type', 'tags')->whereHas('postCategories')->where('status', 1)->where('lang', current_locale())->get();

        $recent_posts = Post::where('type', 'blog')->where('lang', current_locale())->with('preview')->where('status', 1)->latest()
            ->whereNotIn('id', [$blog->id])
            ->take(4)
            ->get();

        $categoryIds = $blog->categories()->pluck('id');

        $related_blogs = Post::where('type', 'blog')->where('lang', current_locale())->with('preview')->where('status', 1)
            ->whereHas('postCategories', function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds);
            })
            ->with('categories:id,title')
            ->inRandomOrder()
            ->take(4)
            ->get();

        $meta = (array) json_decode($blog->seo->value ?? '');
        $seo = SeoMeta::set($meta);

        $nextBlog = Post::with('preview')->where('type', 'blog')->where('id', '>', $blog->id)->where('status', 1)->first();
        $prevBlog = Post::with('preview')->where('type', 'blog')->where('id', '<', $blog->id)->where('status', 1)->first();

        $blog_page = get_option('blog_page', true);

        return Inertia::render('Web/Blogs/Show', compact('blog', 'categories', 'tags', 'recent_posts', 'related_blogs', 'nextBlog', 'prevBlog', 'blog_page'));
    }

    public function category(Request $request, string $slug)
    {
        $category = Category::where('type', 'blog_category')
            ->where('status', 1)
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = Post::where('type', 'blog')
            ->where('lang', current_locale())
            ->whereHas('postCategories', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->with('shortDescription', 'preview', 'categories:id,title,slug')
            ->where('status', 1)
            ->latest()
            ->paginate(4);

        $recent_posts = Post::where('type', 'blog')
            ->where('lang', current_locale())
            ->with('shortDescription', 'preview', 'categories:id,title,slug')
            ->where('status', 1)
            ->latest()
            ->take(4)
            ->get();

        $categories = Category::where('type', 'blog_category')
            ->where('status', 1)
            ->whereHas('postCategories')
            ->withCount('postCategories')
            ->get();

        $tags = Category::where('type', 'tags')
            ->whereHas('postCategories')
            ->where('status', 1)
            ->get();

        $seo = SeoMeta::set(['title' => $category->title]);
        $blog_page = get_option('blog_page', true);

        return Inertia::render('Web/Blogs/Index', compact('posts', 'request', 'recent_posts', 'categories', 'tags', 'blog_page'));
    }

    public function tag(Request $request, $slug)
    {
        $tag = Category::where('type', 'tags')
            ->where('status', 1)
            ->where('slug', $slug)
            ->firstOrFail();

        $tags = Category::where('type', 'tags')
            ->whereHas('postCategories')
            ->where('status', 1)
            ->get();

        $posts = Post::where('type', 'blog')
            ->where('lang', current_locale())
            ->with('shortDescription', 'preview', 'categories:id,title,slug')
            ->whereHas('postCategories', function ($query) use ($tag) {
                $query->where('category_id', $tag->id);
            })
            ->where('status', 1)
            ->latest()
            ->paginate(4);

        $recent_posts = Post::where('type', 'blog')
            ->where('lang', current_locale())
            ->with('shortDescription', 'preview')
            ->where('status', 1)
            ->latest()
            ->take(4)
            ->get();

        $categories = Category::where('type', 'blog_category')
            ->whereHas('postCategories')
            ->withCount('postCategories')
            ->where('status', 1)
            ->where('lang', current_locale())
            ->get();

        $seo = SeoMeta::set(['title' => $tag->title]);

        $blog_page = get_option('blog_page', true);

        return Inertia::render('Web/Blogs/Index', compact('posts', 'recent_posts', 'categories', 'tags', 'blog_page'));
    }
}
