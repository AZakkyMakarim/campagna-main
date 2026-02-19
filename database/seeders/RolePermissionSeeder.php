<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'core' => [
                'business-profile',
                'outlet',
                'order-type',
                'user-role',
                'payment',
                'printer-struck',
                'settings',
            ],
        ];

        // CREATE PERMISSIONS
        foreach ($permissions as $module => $menus) {
            foreach ($menus as $menu) {
                Permission::firstOrCreate([
                    'name' => "{$module}.{$menu}",
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
