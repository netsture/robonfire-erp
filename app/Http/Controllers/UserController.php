<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $query = User::with('roles');

        if (!$authUser->isSuperAdmin()) {
            $query->where('firm_id', $authUser->firm_id)
                  ->where(function ($q) {
                      $q->whereNull('role')
                        ->orWhere(function ($subQ) {
                            $subQ->where('role', '!=', 'superadmin')
                                 ->where('role', '!=', 'Superadmin');
                        });
                  })
                  ->whereDoesntHave('roles', function ($q) {
                      $q->where('slug', 'superadmin');
                  });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(10);

        $rolesQuery = Role::query();
        if (!$authUser->isSuperAdmin()) {
            $rolesQuery->where('slug', '!=', 'superadmin');
        }
        $roles = $rolesQuery->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $authUser = auth()->user();
        $rolesQuery = Role::query();
        if (!$authUser->isSuperAdmin()) {
            $rolesQuery->where('slug', '!=', 'superadmin');
        }
        $roles = $rolesQuery->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'role_id'  => ['required', 'exists:roles,id'],
            'status'   => ['required', 'in:active,inactive'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        if (!$authUser->isSuperAdmin() && $role->slug === 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::create([
            'firm_id'  => $authUser->isSuperAdmin() ? null : $authUser->firm_id,
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role'     => $role->name,
            'status'   => $validated['status'],
        ]);

        $user->roles()->sync([$role->id]);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user)
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin()) {
            if ($user->isSuperAdmin() || $user->firm_id !== $authUser->firm_id) {
                abort(403, 'Unauthorized access to user profile.');
            }
        }

        $rolesQuery = Role::query();
        if (!$authUser->isSuperAdmin()) {
            $rolesQuery->where('slug', '!=', 'superadmin');
        }
        $roles = $rolesQuery->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin()) {
            if ($user->isSuperAdmin() || $user->firm_id !== $authUser->firm_id) {
                abort(403, 'Unauthorized access to user profile.');
            }
        }

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'   => ['nullable', 'string', 'max:20'],
            'role_id' => ['required', 'exists:roles,id'],
            'status'  => ['required', 'in:active,inactive'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        if (!$authUser->isSuperAdmin() && $role->slug === 'superadmin') {
            abort(403, 'Unauthorized action.');
        }

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(6)],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->name   = $validated['name'];
        $user->email  = $validated['email'];
        $user->phone  = $validated['phone'];
        $user->role   = $role->name;
        $user->status = $validated['status'];
        $user->save();

        $user->roles()->sync([$role->id]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->firm_id !== $authUser->firm_id)) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id === $authUser->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'User status updated to ' . $user->status . '.');
    }

    public function destroy(User $user)
    {
        $authUser = auth()->user();
        if (!$authUser->isSuperAdmin() && ($user->isSuperAdmin() || $user->firm_id !== $authUser->firm_id)) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->id === $authUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User account deleted successfully.');
    }
}
