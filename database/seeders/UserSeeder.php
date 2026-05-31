<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Editor User',
                'email' => 'editor@example.com',
                'role' => 'Editor',
            ],
            [
                'name' => 'Moderator User',
                'email' => 'moderator@example.com',
                'role' => 'Moderator',
            ],
            [
                'name' => 'Subscriber User',
                'email' => 'subscriber@example.com',
                'role' => 'Subscriber',
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'role' => 'User',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$userData['role']]);
        }
    }
}
