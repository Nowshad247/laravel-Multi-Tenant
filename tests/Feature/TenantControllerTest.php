<?php

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
});

it('shows the tenants page to a Super Admin', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->get(route('tenants.index'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page
            ->component('tenants/index')
            ->has('tenants')
    );
});

it('denies the tenants page to unauthenticated users', function () {
    $this->get(route('tenants.index'))->assertRedirect();
});

it('passes an empty tenants array when no tenants exist', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $response = $this->actingAs($user)->get(route('tenants.index'));

    $response->assertInertia(
        fn ($page) => $page
            ->has('tenants')
            ->where('tenants', [])
    );
});
