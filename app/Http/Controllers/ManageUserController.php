<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManageUserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => optional($user->email_verified_at)->toDateTimeString(),
            'created_at' => $user->created_at->toDateTimeString(),
            'roles' => $user->roles->pluck('name')->all(),
        ]);

        return inertia('manageUser/index', ['users' => $users]);
    }

    public function show(User $user)
    {
        $user->load('roles');

        $payload = array_merge(
            $user->only(['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at']),
            ['roles' => $user->roles->pluck('name')->all(), 'avatar' => $user->avatar ?? null]
        );

        return inertia('manageUser/show', ['user' => $payload]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (array_key_exists('avatar', $data)) {
            $user->avatar = $data['avatar'];
        }

        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'Profile updated.');
    }
}
