<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class FirmController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        $query = Firm::withCount('users', 'products', 'sales', 'purchases');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $firms = $query->latest()->paginate(10);

        return view('firms.index', compact('firms'));
    }

    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        return view('firms.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:firms'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'address'        => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
            'admin_name'     => ['required', 'string', 'max:255'],
            'admin_email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($validated, &$firm) {
            $firm = Firm::create([
                'name'    => $validated['name'],
                'slug'    => Str::slug($validated['name']),
                'email'   => $validated['email'],
                'phone'   => $validated['phone'],
                'address' => $validated['address'],
                'status'  => $validated['status'],
            ]);

            // Create Firm Admin User
            $adminUser = User::create([
                'firm_id'  => $firm->id,
                'name'     => $validated['admin_name'],
                'email'    => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'phone'    => $validated['phone'],
                'role'     => 'admin',
                'status'   => 'active',
            ]);

            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole) {
                $adminUser->roles()->sync([$adminRole->id]);
            }
        });

        return redirect()->route('firms.index')->with('success', 'Firm and Firm Admin user created successfully.');
    }

    public function show(Firm $firm)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        $firm->load('users', 'customers', 'suppliers', 'products');
        return view('firms.show', compact('firm'));
    }

    public function edit(Firm $firm)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        return view('firms.edit', compact('firm'));
    }

    public function update(Request $request, Firm $firm)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255', 'unique:firms,name,' . $firm->id],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'status'  => ['required', 'in:active,inactive'],
        ]);

        $firm->update([
            'name'    => $validated['name'],
            'slug'    => Str::slug($validated['name']),
            'email'   => $validated['email'],
            'phone'   => $validated['phone'],
            'address' => $validated['address'],
            'status'  => $validated['status'],
        ]);

        return redirect()->route('firms.index')->with('success', 'Firm updated successfully.');
    }

    public function destroy(Firm $firm)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        $firm->delete();
        return redirect()->route('firms.index')->with('success', 'Firm record and associated data removed.');
    }
}
