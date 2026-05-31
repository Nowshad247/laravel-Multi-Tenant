<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::with('permissions')->get()->map(fn (Role $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
        ]);

        $permissions = Permission::orderBy('name')->pluck('name')->all();

        return Inertia::render('roles/index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('users.permissions');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('users.permissions')->with('success', "Role \"{$data['name']}\" created.");
    }

    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('users.permissions');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Super Admin') {
            return redirect()->route('users.permissions')->with('error', 'The Super Admin role cannot be modified.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->name = $data['name'];
        $role->save();
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('users.permissions')->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Super Admin') {
            return redirect()->route('users.permissions')->with('error', 'The Super Admin role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('users.permissions')->with('error', "Cannot delete role \"{$role->name}\" while users are assigned to it.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('users.permissions')->with('success', "Role \"{$roleName}\" deleted.");
    }
}
