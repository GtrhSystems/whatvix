<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerListExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        public $moduleName
    ) {
    }

    protected $columns = [
        'name',
        'phone',
        'picture',
    ];

    public function query()
    {
        return activeWorkspaceOwner()->customers()->where('module', $this->moduleName);
    }

    public function headings(): array
    {
        return $this->columns;
    }

    /**
     * @param Customer $customer
     */
    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->uuid,
            $customer->picture,
        ];
    }
}