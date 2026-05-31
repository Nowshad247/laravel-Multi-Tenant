<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

it('allows a super admin to access the manage users page', function () {
    $role = Role::firstOrCreate(['name' => 'Super Admin']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/manage-users')
        ->assertOk();
});

it('forbids a non-super-admin from accessing the manage users page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/manage-users')
        ->assertForbidden();
});
