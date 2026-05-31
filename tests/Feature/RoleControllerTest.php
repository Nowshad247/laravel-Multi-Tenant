<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});

// ─── Authorization ────────────────────────────────────────────────────────────

it('forbids a guest from accessing the roles page', function () {
    $this->get('/userspermissions')->assertRedirect('/login');
});

it('forbids a non-super-admin from accessing the roles page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/userspermissions')->assertForbidden();
});

it('allows a super admin to access the roles page', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $this->actingAs($superAdmin)->get('/userspermissions')->assertOk();
});

// ─── Index ────────────────────────────────────────────────────────────────────

it('returns roles with their permissions on the index page', function () {
    $superAdmin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'Super Admin']);
    $superAdmin->assignRole($role);

    $permission = Permission::firstOrCreate(['name' => 'view dashboard']);
    $role->givePermissionTo($permission);

    $this->actingAs($superAdmin)
        ->get('/userspermissions')
        ->assertInertia(
            fn ($page) => $page
                ->component('roles/index')
                ->has('roles')
                ->has('permissions'),
        );
});

// ─── Store ────────────────────────────────────────────────────────────────────

it('allows a super admin to create a role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $this->actingAs($superAdmin)
        ->post('/role/store', ['name' => 'Test Role', 'permissions' => []])
        ->assertRedirect('/userspermissions');

    expect(Role::where('name', 'Test Role')->exists())->toBeTrue();
});

it('creates a role with permissions', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));
    $permission = Permission::firstOrCreate(['name' => 'view dashboard']);

    $this->actingAs($superAdmin)
        ->post('/role/store', ['name' => 'Content Team', 'permissions' => ['view dashboard']])
        ->assertRedirect('/userspermissions');

    $role = Role::where('name', 'Content Team')->first();
    expect($role)->not->toBeNull();
    expect($role->hasPermissionTo($permission))->toBeTrue();
});

it('validates that role name is required when creating', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $this->actingAs($superAdmin)
        ->post('/role/store', ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('validates that role name is unique when creating', function () {
    Role::firstOrCreate(['name' => 'Duplicate Role']);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $this->actingAs($superAdmin)
        ->post('/role/store', ['name' => 'Duplicate Role'])
        ->assertSessionHasErrors('name');
});

// ─── Update ───────────────────────────────────────────────────────────────────

it('allows a super admin to update a role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $role = Role::firstOrCreate(['name' => 'Old Name']);

    $this->actingAs($superAdmin)
        ->put("/role/update/{$role->id}", ['name' => 'New Name', 'permissions' => []])
        ->assertRedirect('/userspermissions');

    expect(Role::where('name', 'New Name')->exists())->toBeTrue();
});

it('syncs permissions when updating a role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $role = Role::firstOrCreate(['name' => 'Updatable Role']);
    $perm = Permission::firstOrCreate(['name' => 'view dashboard']);

    $this->actingAs($superAdmin)
        ->put("/role/update/{$role->id}", ['name' => 'Updatable Role', 'permissions' => ['view dashboard']])
        ->assertRedirect('/userspermissions');

    expect($role->fresh()->hasPermissionTo($perm))->toBeTrue();
});

it('prevents updating the Super Admin role', function () {
    $superAdmin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'Super Admin']);
    $superAdmin->assignRole($role);

    $this->actingAs($superAdmin)
        ->put("/role/update/{$role->id}", ['name' => 'Hacked', 'permissions' => []])
        ->assertRedirect('/userspermissions');

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
    expect(Role::where('name', 'Hacked')->exists())->toBeFalse();
});

// ─── Destroy ──────────────────────────────────────────────────────────────────

it('allows a super admin to delete a role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $role = Role::firstOrCreate(['name' => 'Deletable Role']);

    $this->actingAs($superAdmin)
        ->delete("/role/delete/{$role->id}")
        ->assertRedirect('/userspermissions');

    expect(Role::where('name', 'Deletable Role')->exists())->toBeFalse();
});

it('prevents deleting the Super Admin role', function () {
    $superAdmin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'Super Admin']);
    $superAdmin->assignRole($role);

    $this->actingAs($superAdmin)
        ->delete("/role/delete/{$role->id}")
        ->assertRedirect('/userspermissions');

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
});

it('prevents deleting a role that has users assigned', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $role = Role::firstOrCreate(['name' => 'Occupied Role']);
    $assignedUser = User::factory()->create();
    $assignedUser->assignRole($role);

    $this->actingAs($superAdmin)
        ->delete("/role/delete/{$role->id}")
        ->assertRedirect('/userspermissions');

    expect(Role::where('name', 'Occupied Role')->exists())->toBeTrue();
});
