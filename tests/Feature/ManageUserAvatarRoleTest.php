<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});

// ─── show() passes availableRoles ────────────────────────────────────────────

it('includes available roles when showing a user', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create();

    $this->actingAs($superAdmin)
        ->get("/central/admin/show-user/{$target->id}")
        ->assertInertia(
            fn ($page) => $page
                ->component('manageUser/show')
                ->has('user.availableRoles')
        );
});

// ─── updateRole ───────────────────────────────────────────────────────────────

it('allows a super admin to update a user role', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create();
    Role::firstOrCreate(['name' => 'Editor']);
    $target->assignRole('Editor');

    Role::firstOrCreate(['name' => 'Subscriber']);

    $this->actingAs($superAdmin)
        ->put("/users/{$target->id}/role", ['role' => 'Subscriber'])
        ->assertRedirect("/central/admin/show-user/{$target->id}");

    expect($target->fresh()->hasRole('Subscriber'))->toBeTrue();
    expect($target->fresh()->hasRole('Editor'))->toBeFalse();
});

it('forbids a non-super-admin from updating a user role', function () {
    $editor = User::factory()->create();
    $editor->assignRole(Role::firstOrCreate(['name' => 'Editor']));

    $target = User::factory()->create();

    $this->actingAs($editor)
        ->put("/users/{$target->id}/role", ['role' => 'Editor'])
        ->assertForbidden();
});

it('validates that the role exists when updating', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create();

    $this->actingAs($superAdmin)
        ->put("/users/{$target->id}/role", ['role' => 'NonExistentRole'])
        ->assertSessionHasErrors('role');
});

// ─── updateAvatar ─────────────────────────────────────────────────────────────

it('allows a super admin to upload an avatar for a user', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg', 200, 200);

    $this->actingAs($superAdmin)
        ->post("/users/{$target->id}/avatar", ['avatar' => $file])
        ->assertRedirect("/central/admin/show-user/{$target->id}");

    $target->refresh();
    expect($target->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($target->avatar);
});

it('replaces the old avatar when a new one is uploaded by an admin', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create(['avatar' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'old-content');

    $file = UploadedFile::fake()->image('new.jpg', 200, 200);

    $this->actingAs($superAdmin)
        ->post("/users/{$target->id}/avatar", ['avatar' => $file])
        ->assertRedirect("/central/admin/show-user/{$target->id}");

    Storage::disk('public')->assertMissing('avatars/old.jpg');
    $target->refresh();
    expect($target->avatar)->not->toBe('avatars/old.jpg');
});

it('validates the avatar is an image when uploading', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create();
    $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

    $this->actingAs($superAdmin)
        ->post("/users/{$target->id}/avatar", ['avatar' => $file])
        ->assertSessionHasErrors('avatar');
});

it('forbids a non-authenticated user from uploading an avatar', function () {
    $target = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->post("/users/{$target->id}/avatar", ['avatar' => $file])
        ->assertRedirect('/login');
});

// ─── deleteAvatar ─────────────────────────────────────────────────────────────

it('allows a super admin to delete a user avatar', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create(['avatar' => 'avatars/pic.jpg']);
    Storage::disk('public')->put('avatars/pic.jpg', 'content');

    $this->actingAs($superAdmin)
        ->delete("/users/{$target->id}/avatar")
        ->assertRedirect("/central/admin/show-user/{$target->id}");

    $target->refresh();
    expect($target->avatar)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/pic.jpg');
});

it('does nothing gracefully when deleting avatar that does not exist', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::firstOrCreate(['name' => 'Super Admin']));

    $target = User::factory()->create(['avatar' => null]);

    $this->actingAs($superAdmin)
        ->delete("/users/{$target->id}/avatar")
        ->assertRedirect("/central/admin/show-user/{$target->id}");

    expect($target->fresh()->avatar)->toBeNull();
});
