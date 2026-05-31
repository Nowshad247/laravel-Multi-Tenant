<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('allows an authenticated user to upload an avatar', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

    $this->actingAs($user)
        ->post('/settings/avatar', ['avatar' => $file])
        ->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
});

it('replaces an old avatar when a new one is uploaded', function () {
    $user = User::factory()->create(['avatar' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'fake-content');

    $file = UploadedFile::fake()->image('new.jpg', 200, 200);

    $this->actingAs($user)
        ->post('/settings/avatar', ['avatar' => $file])
        ->assertRedirect('/settings/profile');

    Storage::disk('public')->assertMissing('avatars/old.jpg');

    $user->refresh();
    expect($user->avatar)->not->toBe('avatars/old.jpg');
});

it('validates that the uploaded file is an image', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs($user)
        ->post('/settings/avatar', ['avatar' => $file])
        ->assertSessionHasErrors('avatar');
});

it('validates the avatar file size limit', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('large.jpg')->size(3000);

    $this->actingAs($user)
        ->post('/settings/avatar', ['avatar' => $file])
        ->assertSessionHasErrors('avatar');
});

it('requires an authenticated user to upload an avatar', function () {
    $file = UploadedFile::fake()->image('avatar.jpg');

    $this->post('/settings/avatar', ['avatar' => $file])
        ->assertRedirect('/login');
});

it('allows an authenticated user to delete their avatar', function () {
    $user = User::factory()->create(['avatar' => 'avatars/photo.jpg']);
    Storage::disk('public')->put('avatars/photo.jpg', 'fake-content');

    $this->actingAs($user)
        ->delete('/settings/avatar')
        ->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->avatar)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/photo.jpg');
});

it('deleting avatar when none is set does nothing', function () {
    $user = User::factory()->create(['avatar' => null]);

    $this->actingAs($user)
        ->delete('/settings/avatar')
        ->assertRedirect('/settings/profile');

    expect($user->fresh()->avatar)->toBeNull();
});

it('requires an authenticated user to delete an avatar', function () {
    $this->delete('/settings/avatar')->assertRedirect('/login');
});
