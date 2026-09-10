<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $query = Role::query();
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('slug', '!=', 'superadmin');
        }

        $roles = $query->withCount('permissions', 'users')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $authUser = auth()->user();
        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            abort(403, 'Only Admin role can perform this action.');
        }

        $permissionsByModule = Permission::all()->groupBy('module');
        return view('roles.create', compact('permissionsByModule'));
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            abort(403, 'Only Admin role can perform this action.');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully with assigned permissions.');
    }

    public function edit(Role $role)
    {
        $authUser = auth()->user();
        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            abort(403, 'Only Admin role can perform this action.');
        }

        if (!$authUser->isSuperAdmin() && $role->slug === 'superadmin') {
            abort(403, 'Unauthorized access to Superadmin role.');
        }

        $permissionsByModule = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissionsByModule', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $authUser = auth()->user();
        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            abort(403, 'Only Admin role can perform this action.');
        }

        if (!$authUser->isSuperAdmin() && $role->slug === 'superadmin') {
            abort(403, 'Unauthorized access to Superadmin role.');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(Role $role)
    {
        $authUser = auth()->user();
        if (!$authUser->isAdmin() && !$authUser->isSuperAdmin()) {
            abort(403, 'Only Admin role can perform this action.');
        }

        if (!$authUser->isSuperAdmin() && $role->slug === 'superadmin') {
            abort(403, 'Unauthorized access to Superadmin role.');
        }

        if ($role->slug === 'admin' || $role->slug === 'superadmin') {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
