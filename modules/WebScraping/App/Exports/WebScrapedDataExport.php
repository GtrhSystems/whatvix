<?php

namespace Modules\WebScraping\App\Exports;

use App\Models\WebScrapedData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WebScrapedDataExport implements FromCollection, WithHeadings, WithMapping
{
    protected $webScrapingId;

    public function __construct($webScrapingId)
    {
        $this->webScrapingId = $webScrapingId;
    }

    public function collection()
    {
        return WebScrapedData::where('web_scraping_id', $this->webScrapingId)->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone Number',
            'Address',
            'Rating',
            'Types',
        ];
    }

    public function map($row): array
    {
        $data = $row->data;
        return [
            $data['name'],
            $data['phone_number'] ?? 'N/A',
            $data['formatted_address'],
            $data['rating'],
            implode(', ', $data['types'] ?? []),
        ];
    }
}
