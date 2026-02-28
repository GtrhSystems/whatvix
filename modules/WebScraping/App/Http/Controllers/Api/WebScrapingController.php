<?php

namespace Modules\WebScraping\App\Http\Controllers\Api;

use App\Helpers\Toastr;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\WebScraping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WebScrapingController extends Controller
{

    public function index($uuid)
    {
        validateUserPlan('web_scrape', true);

        $record = WebScraping::where('uuid', $uuid)
            ->with('scraped_data')
            ->where('module', 'whatsapp-web')
            ->where('user_id', activeWorkspaceOwnerId())
            ->firstOrFail();
        $record->update(['status' => 'in_progress']);
        $category = Category::where('id', $record->category_id)->first();
        $query = "{$category->title}+in+{$record->parameters['state']}+{$record->parameters['city']}+{$record->parameters['country']}";

        $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json';

        $response = Http::get($url, [
            'query' => $query,
            'pagetoken' => request('next_page_token'),
            'key' => env('GOOGLE_PLACE_API_KEY'),
        ])->throw()->json();
        $filteredData = array_map(function ($result) use ($record) {
            $item = [
                'name' => $result['name'],
                'business_status' => $result['business_status'],
                'formatted_address' => $result['formatted_address'],
                'location' => $result['geometry']['location'],
                'place_id' => $result['place_id'],
                'rating' => $result['rating'],
                'types' => $result['types'],
                'website' => $result['website'] ?? null,
                'icon' => [
                    $result['icon'],
                    $result['icon_background_color'],
                    $result['icon_mask_base_uri']
                ]
            ];
            $record->scraped_data()->updateOrCreate(['unique_id' => $result['place_id']], ['data' => $item]);
            return $item;
        }, $response['results'] ?? []);
        $record->increment('query_count');
        return response()->json([
            'data' => $filteredData,
            'next_page_token' => $response['next_page_token'] ?? null,
        ]);
    }
}
