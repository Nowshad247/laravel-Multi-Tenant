<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

it('allows a manager to update a user avatar', function () {
    $role = Role::firstOrCreate(['name' => 'Super Admin']);

    $user = User::factory()->create();
    $user->assignRole($role);

    $response = $this
        ->actingAs($user)
        ->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'avatar' => 'https://example.com/avatar.png',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('users.show', $user));

    $user->refresh();

    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->avatar)->toBe('https://example.com/avatar.png');
});
