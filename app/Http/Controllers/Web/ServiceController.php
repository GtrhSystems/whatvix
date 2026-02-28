<?php

namespace App\Http\Controllers\Web;

use App\Models\Post;
use App\Models\Service;
use App\Helpers\SeoMeta;
use App\Models\Category;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    public function index()
    {
        SeoMeta::init('seo_services');
        $services = Service::query()
            ->with('category')
            ->where('is_active', 1)
            ->latest()
            ->paginate(10);

        return inertia('Web/Services/Index', [
            'services' => $services,
            'service_page' => get_option('service_page', true),
        ]);
    }

    public function show(Service $service)
    {
        SeoMeta::set($service->meta);
        $categories = Category::where('status', 1)->where('type', 'service')->latest()->get();
        $servicePage = get_option('service_page', true);

        return inertia('Web/Services/Show', [
            'service' => $service,
            'categories' => $categories,
            'service_page' => $servicePage
        ]);
    }

    public function categoryShow($slug)
    {
        SeoMeta::init('seo_services');
        $category = Category::whereSlug($slug)->firstOrFail();
        $services = $category->services()->where('is_active', 1)->latest()->paginate(10);
        return inertia('Web/Services/Index', [
            'services' => $services,
            'category' => $category,
            'service_page' => get_option('service_page', true),
        ]);
    }
}
