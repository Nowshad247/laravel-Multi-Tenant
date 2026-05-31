<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view dashboard',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'moderate content',
            'view reports',
            'manage users',
            'create users',
            'edit users',
            'delete users',
            'manage roles',
            'manage permissions',
            'manage settings',
            'manage subscriptions',
            'update profile',
            'manage appearance',
            'view subscribers',
            'manage comments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }
}
