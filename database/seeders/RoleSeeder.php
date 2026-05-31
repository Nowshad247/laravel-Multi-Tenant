<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::pluck('name')->all();

        $roles = [
            'Super Admin' => $allPermissions,
            'Editor' => [
                'view dashboard',
                'view content',
                'create content',
                'edit content',
                'delete content',
                'publish content',
                'view reports',
                'update profile',
            ],
            'Moderator' => [
                'view dashboard',
                'view content',
                'moderate content',
                'view reports',
                'manage comments',
                'update profile',
            ],
            'Subscriber' => [
                'view content',
                'view dashboard',
                'manage subscriptions',
                'update profile',
            ],
            'User' => [
                'view content',
                'update profile',
            ],
        ];

        foreach ($roles as $role => $permissions) {
            $roleModel = Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );

            $roleModel->syncPermissions($permissions);
        }
    }
}
