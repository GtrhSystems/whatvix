<?php

namespace App\Http\Controllers\Web;

use App\Models\Plan;
use App\Models\Post;
use Inertia\Inertia;
use App\Helpers\SeoMeta;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebPageController extends Controller
{

    public function home()
    {
        SeoMeta::init('seo_home');
        $testimonials = Post::where('type', 'testimonial')
            ->with('excerpt', 'preview', 'shortDescription')
            ->latest()
            ->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->title,
                    'short_note' => $item->shortDescription?->value ?? '',
                    'designation' => $item->slug,
                    'image' => $item->preview?->value,
                    'ratting' => intval($item->lang ?? 0),
                    'description' => $item->excerpt?->value
                ];
            });

        $partnerLogos = Category::whereType('partner')->where('status', 1)->latest()->pluck('preview');
        $faqs = Post::where('type', 'faq')->with(['excerpt', 'faq_categories:id,title'])
            ->latest()
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'question' => $faq->title,
                    'answer' => $faq->excerpt?->value
                ];
            });

   $homePageData = get_option_with_locale('home_page');

        return Inertia::render('Web/Home', [
            'home' => $homePageData,
            'testimonials' => $testimonials,
            'partner_logos' => $partnerLogos,
            'faqs' => $faqs
        ]);
    }

    public function about()
    {
        SeoMeta::init('seo_about');
        $about = get_option('about_page', true);

        $testimonials = Post::where('type', 'testimonial')
            ->with('excerpt', 'preview')
            ->latest()
            ->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->title,
                    'designation' => $item->slug,
                    'image' => $item->preview?->value,
                    'ratting' => intval($item->lang ?? 0),
                    'description' => $item->excerpt?->value
                ];
            });

        $blogIds = [];
        try {
            $blogIds = explode(',', data_get($about, 'blog_section_one.blog_ids', []));
        } catch (\Throwable $th) {
        }

        $featured_blogs = Post::where('type', 'blog')
            ->whereIn('id', $blogIds)
            ->where('status', 1)
            ->with('preview')
            ->limit(2)
            ->get()
            ->map(function ($item) {
                $item->url = route('blogs.show', $item->slug);
                return $item;
            });

        return Inertia::render('Web/About', [
            'about_page' => $about,
            'testimonials' => $testimonials,
            'featured_blogs' => $featured_blogs
        ]);
    }

    public function team()
    {
        SeoMeta::init('seo_team');

        $teams = Post::where('type', 'team')->with('preview', 'excerpt')->where('status', 1)->latest()->get()->map(function ($team) {
            return [
                'id' => $team->id,
                'name' => $team->title,
                'image' => $team->preview?->value,
                'socials' => json_decode($team->excerpt?->value ?? '[]'),
                'designation' => $team->slug
            ];
        });

        return Inertia::render('Web/Team', [
            'teams' => $teams,
            'team_page' => get_option('team_page', true)
        ]);
    }

    public function pricing()
    {
        SeoMeta::init('seo_pricing');

        $plans = Plan::query()
            ->where('status', 1)
            ->where('is_featured', 0)
            ->orderBy('price', 'asc')
            ->get();

        $featured_plans = Plan::query()
            ->where('status', 1)
            ->where('is_featured', 1)
            ->orderBy('price', 'asc')
            ->get();

        $planSettings = get_option('plan');
        $planByDays = $plans->map(function ($plan) {
            return [
                'days' => $plan->days,
                'duration' => $plan->days == 30 ? 'monthly' : ($plan->days == 365 ? 'yearly' : 'lifetime')
            ];
        })
            ->sortBy('days')
            ->unique()
            ->values();

        return Inertia::render('Web/Pricing', [
            'plans' => $plans,
            'featured_plans' => $featured_plans,
            'planSettings' => $planSettings,
            'planByDays' => $planByDays,
            'pricing_page' => get_option('pricing_page', true)
        ]);
    }

    public function faq()
    {
        SeoMeta::init('seo_faq');

        $slug = request('category');
        $category = null;
        if ($slug) {
            $category = Category::whereType('faq_category')->where('slug', $slug)->where('status', 1)->first(['id', 'title', 'slug']);
            $faqs = $category->faqs()->with(['excerpt'])->where('status', 1)->get();
            $category = $category->slug;
        } else {
            $faqs = Post::where('type', 'faq')
                ->with(['excerpt'])
                ->where('status', 1)
                ->get();
        }


        $faqs = $faqs->map(function ($faq) {
            return [
                'id' => $faq->id,
                'question' => $faq->title,
                'answer' => $faq->excerpt?->value
            ];
        });

        $categories = Category::whereType('faq_category')->where('status', 1)->get(['id', 'title', 'slug']);

        return Inertia::render('Web/Faq', compact('category', 'faqs', 'categories'));
    }

    public function page($slug)
    {
        $page = Post::with('description', 'seo')->where(['slug' => $slug, 'status' => 1])->firstOrFail();

        $seo = (array) json_decode($page->seo?->value ?? []);

        SeoMeta::set($seo);
        $about_page = get_option('about_page', true);
        return Inertia::render('Web/CustomPage', compact('page', 'about_page'));
    }

    public function callAction($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        abort(404);
    }
}
