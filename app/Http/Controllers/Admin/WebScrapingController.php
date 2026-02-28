<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\WebScrapedData;
use App\Models\WebScraping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WebScrapingController extends Controller
{
    public function index()
    {
        $query = WebScraping::query();
        PageHeader::set(
            title: 'Web Scraping',
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'title' => 'Total Dataset',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:checkbox-checked",
                    'title' => 'Completed Dataset',
                    'value' => $query->clone()->where('status', 'completed')->count(),
                ],
                [
                    'icon' => "solar:stopwatch-linear",
                    'title' => 'Pending Dataset',
                    'value' => $query->clone()->where('status', 'pending')->count(),
                ],
            ]
        );

        $scrapingRecords = WebScraping::query()
            ->with('category')
            ->latest()->paginate();


        return Inertia::render('Admin/WebScraping/Index', ['scrapingRecords' => $scrapingRecords]);
    }
    public function category()
    {

        $query = Category::query()->where('type', 'web_scraping');
        $categories = Category::whereType('web_scraping')->latest()->paginate(10);
        PageHeader::set(__('Categories'))
            ->overviews([
                [
                    'title' => 'Total',
                    'value' => $query->clone()->count(),
                    'icon' => 'bx:grid-alt',
                ],
                [
                    'title' => 'Active',
                    'value' => $query->clone()->where('status', 1)->count(),
                    'icon' => 'bx:grid-alt',
                ],
                [
                    'title' => 'Inactive',
                    'value' => $query->clone()->where('status', 0)->count(),
                    'icon' => 'bx:grid-alt',
                ],
            ])
            ->addModal('Add New', 'createModal', 'bx-plus');
        return Inertia::render('Admin/WebScraping/Category/Index', [
            'categories' => $categories
        ]);
    }
    public function show($uuid)
    {
        PageHeader::set(
            title: 'Web Scraping',
        );
        $record = WebScraping::where('uuid', $uuid)
            ->where('module', 'whatsapp-web')
            ->firstOrFail();

        $scraped_data = WebScrapedData::where('web_scraping_id', $record->id)
            ->paginate();

        return Inertia::render('Admin/WebScraping/Show', [
            'record' => $record,
            'scraped_data' => $scraped_data
        ]);
    }
    public function destroy($id)
    {
        $record = WebScraping::findOrFail($id);
        $record->delete();
        return back()->with('danger', 'Deleted Successfully');
    }
    public function destroy_data($id)
    {
        $scraped_data = WebScrapedData::where('id', $id)
            ->firstOrFail();

        $scraped_data->delete();
        return back()->with('danger', 'Deleted Successfully');
    }
}
