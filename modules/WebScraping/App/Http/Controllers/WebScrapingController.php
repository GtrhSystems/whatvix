<?php

namespace Modules\WebScraping\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Category;
use App\Helpers\PageHeader;
use App\Models\WebScraping;
use Illuminate\Http\Request;
use App\Models\WebScrapedData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Modules\WebScraping\App\Exports\WebScrapedDataExport;

class WebScrapingController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $query = WebScraping::where('user_id', $user->id);
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
                    'title' => 'Completed Jobs',
                    'value' => $query->clone()->where('status', 'completed')->count(),
                ],
                [
                    'icon' => "solar:stopwatch-linear",
                    'title' => 'Pending Jobs',
                    'value' => $query->clone()->where('status', 'pending')->count(),
                ],
                [
                    'icon' => "bx:globe",
                    'title' => 'Total Queries',
                    'value' => $query->clone()->sum('query_count'),
                ],
            ]
        )->addLink(
                'Add New',
                route('user.web-scraping.scrape.create'),
                'bx:plus'
            );

        $scrapingRecords = WebScraping::query()->where('user_id', $user->id)
            ->with('category')
            ->where('module', 'whatsapp-web')
            ->latest()
            ->paginate();


        return Inertia::render('WebScraping/Index', ['scrapingRecords' => $scrapingRecords]);
    }

    public function create()
    {
        validateUserPlan('web_scrape');
        PageHeader::set(
            title: 'Web Scraping',
        );
        $categories = Category::query()->where('type', 'web_scraping')
            ->where('status', 1)->get();
        return Inertia::render('WebScraping/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        validateUserPlan('web_scrape');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:google_places',
            'category_id' => 'required|numeric',
            'parameters' => 'required|array',
            'parameters.city' => 'required|string',
            'parameters.state' => 'required|string',
            'parameters.country' => 'required|string',
        ]);

        $record = WebScraping::create($validated + ['user_id' => Auth::id(), 'module' => 'whatsapp-web']);

        return to_route('user.web-scraping.scrape.show', $record->uuid);
    }

    public function show($uuid)
    {
        PageHeader::set(
            title: 'Web Scraping',
        );
        $record = WebScraping::where('uuid', $uuid)
            ->where('module', 'whatsapp-web')
            ->where('user_id', activeWorkspaceOwnerId())
            ->firstOrFail();

        $scraped_data = WebScrapedData::where('web_scraping_id', $record->id)
            ->paginate();

        return Inertia::render('WebScraping/Show', [
            'record' => $record,
            'scraped_data' => $scraped_data
        ]);
    }


    public function store_data($uuid)
    {
       

        $record = WebScraping::where('uuid', $uuid)
            ->where('module', 'whatsapp-web')
            ->where('user_id', activeWorkspaceOwnerId())
            ->with('scraped_data')
            ->firstOrFail();

        $items_to_update = $record->scraped_data->filter(function ($item) {
            return !isset($item->data['phone_number']) || $item->data['phone_number'] === null;
        });

        foreach ($items_to_update as $item) {
            $placeDetails = $this->makeGoogleMapsRequest('details/json', [
                'place_id' => $item->unique_id,
                'fields' => 'name,international_phone_number',
            ]);

            if (isset($placeDetails['result']['international_phone_number'])) {
                $data = $item->data;
                $data['phone_number'] = $placeDetails['result']['international_phone_number'];
                $item->update(['data' => $data]);
            }

            usleep(200000);
        }

        $record->update(['status' => 'completed']);
        return back();
    }

    private function makeGoogleMapsRequest($endpoint, $params)
    {
        $url = config('webscraping.google_maps_place_api_url', 'https://maps.googleapis.com/maps/api/place/') . $endpoint;
        $params['key'] = env('GOOGLE_PLACE_API_KEY');
        return Http::get($url, $params)->throw()->json();
    }
    public function destroy($id)
    {
        $record = WebScraping::where('user_id', activeWorkspaceOwnerId())->findOrFail($id);
        $record->delete();
        return back()->with('danger', 'Deleted Successfully');
    }
    public function destroy_data($id)
    {
        $scraped_data = WebScrapedData::where('id', $id)
            ->whereHas('web_scraping', function ($query) {
                $query->where('user_id', activeWorkspaceOwnerId());
            })
            ->firstOrFail();

        $scraped_data->delete();
        return back()->with('danger', 'Deleted Successfully');
    }

    public function export_data($id)
    {
        $record = WebScraping::where('id', $id)
            ->where('user_id', activeWorkspaceOwnerId())
            ->firstOrFail();

        $fileName = 'scraped_data_' . now() . '.csv';

        return Excel::download(
            new WebScrapedDataExport($record->id),
            $fileName,
            \Maatwebsite\Excel\Excel::CSV,
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}
