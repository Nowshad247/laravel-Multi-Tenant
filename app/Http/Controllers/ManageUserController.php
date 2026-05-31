<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class ManageUserController extends Controller
{
    public function index(): Response
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

    public function show(User $user): Response
    {
        $user->load('roles');

        $payload = array_merge(
            $user->only(['id', 'name', 'email', 'email_verified_at', 'created_at', 'updated_at']),
            [
                'roles' => $user->roles->pluck('name')->all(),
                'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                'availableRoles' => Role::orderBy('name')->pluck('name')->all(),
            ]
        );

        return inertia('manageUser/show', ['user' => $payload]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'Profile updated.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('users.show', $user->id)->with('success', 'Role updated.');
    }

    public function updateAvatar(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return redirect()->route('users.show', $user->id)->with('success', 'Avatar updated.');
    }

    public function deleteAvatar(User $user): RedirectResponse
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return redirect()->route('users.show', $user->id)->with('success', 'Avatar removed.');
    }
}
