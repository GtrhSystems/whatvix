<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $super = User::create([
            'name' => 'Admin',
            'role' => 'admin',
            'avatar' => null,
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);

       
        $roleSuperAdmin = Role::create(['name' => 'superadmin']);
        $permissions = [
            [
                'group_name' => 'Saas',
                'permissions' => [
                    'users',
                    'locations',
                    'order',
                    'plan',
                    'gateways',
                    'payout-methods',
                    'payouts',
                    'cron-job',
                    'support',
                    'notification',
                ]
            ],
            [
                'group_name' => 'Appearance',
                'permissions' => [
                    'services',
                    'projects',
                    'jobs',
                    'integrations',
                    'team',
                    'partner',
                    'blog',
                    'faq',
                    'testimonials',
                    'language',
                    'menu',
                    'page',
                    'seo',
                ]
            ],
            [
                'group_name' => 'Site Settings',
                'permissions' => [
                    'page-settings',
                    'admin-and-roles',
                    'developer-settings',
                ]
            ]
        ];

        foreach ($permissions as $key => $row) {
            foreach ($row['permissions'] as $per) {
                $permission = Permission::create([
                    'name' => $per,
                    'group_name' => $row['group_name']
                ]);
                $roleSuperAdmin->givePermissionTo($permission);
                $permission->assignRole($roleSuperAdmin);
                $super->assignRole($roleSuperAdmin);
            }
        }
    }
}
