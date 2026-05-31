<?php

use App\Actions\Fortify\CreateNewUser;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});

it('assigns the Subscriber role to a newly registered user', function () {
    Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);

    $action = new CreateNewUser;
    $user = $action->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->hasRole('Subscriber'))->toBeTrue();
});

it('creates the Subscriber role if it does not exist yet', function () {
    Role::where('name', 'Subscriber')->delete();

    $action = new CreateNewUser;
    $user = $action->create([
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->hasRole('Subscriber'))->toBeTrue();
    expect(Role::where('name', 'Subscriber')->exists())->toBeTrue();
});

it('does not assign any other role on registration', function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);

    $action = new CreateNewUser;
    $user = $action->create([
        'name' => 'Regular User',
        'email' => 'regular@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect($user->hasRole('Super Admin'))->toBeFalse();
    expect($user->getRoleNames()->count())->toBe(1);
});
