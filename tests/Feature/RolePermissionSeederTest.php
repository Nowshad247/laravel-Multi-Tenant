<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('seeds roles, permissions, and users', function () {
    $this->artisan('db:seed', ['--class' => 'DatabaseSeeder'])->assertSuccessful();

    expect(Permission::count())->toBe(20);
    expect(Role::count())->toBe(5);

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
    expect(Role::where('name', 'Editor')->exists())->toBeTrue();
    expect(Role::where('name', 'Moderator')->exists())->toBeTrue();
    expect(Role::where('name', 'Subscriber')->exists())->toBeTrue();
    expect(Role::where('name', 'User')->exists())->toBeTrue();

    expect(User::where('email', 'superadmin@example.com')->first()->hasRole('Super Admin'))->toBeTrue();
    expect(User::where('email', 'editor@example.com')->first()->hasRole('Editor'))->toBeTrue();
    expect(User::where('email', 'moderator@example.com')->first()->hasRole('Moderator'))->toBeTrue();
    expect(User::where('email', 'subscriber@example.com')->first()->hasRole('Subscriber'))->toBeTrue();
    expect(User::where('email', 'user@example.com')->first()->hasRole('User'))->toBeTrue();
});
