<?php

namespace Modules\Whatsapp\App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use libphonenumber\PhoneNumberUtil;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerListImport implements ToCollection, WithHeadingRow
{

    public function __construct(public $moduleName) 
    {
        
    }

    public function collection(Collection $rows)
    {
        $customerIds = [];
        foreach ($rows as $key => $row) {
            // exact
            $name = $row['name'];
            $picture = $row['picture'] ?? 'https://ui-avatars.com/api/?name=' . $name;

            // map data
            $phone = $row['phone'];
            $phoneUtil = PhoneNumberUtil::getInstance();
            $formattedNumber = $phoneUtil->parse($phone, "BD");

            $customer = Customer::updateOrCreate([
                'module' => $this->moduleName,
                'owner_id' => activeWorkspaceOwnerId(),
                'platform_id' => request('platform_id'),
                'uuid' => $phone
            ], [
                'name' => $name,
                'picture' => $picture,
                'meta' => [
                    'dial_code' => $formattedNumber->getCountryCode(),
                    'phone' => $formattedNumber->getNationalNumber(),
                ]
            ]);

            $customerIds[] = $customer->id;
        }

        $groupIds = request('group_ids', []);
        foreach ($groupIds as $groupId) {
            $group = activeWorkspaceOwner()->groups()->module($this->moduleName)->find($groupId);
            if (!$group) {
                continue;
            }
            $group->customers()->attach($customerIds);
        }
    }
}