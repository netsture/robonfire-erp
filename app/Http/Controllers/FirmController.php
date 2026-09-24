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
                $q->where('firms.name', 'like', "%{$search}%")
                  ->orWhere('firms.email', 'like', "%{$search}%")
                  ->orWhere('firms.phone', 'like', "%{$search}%")
                  ->orWhere('firms.address', 'like', "%{$search}%");
            });
        }

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'users_count') {
                $query->orderBy('users_count', $sortOrder);
            } elseif ($sortBy === 'products_count') {
                $query->orderBy('products_count', $sortOrder);
            } elseif (in_array($sortBy, ['name', 'email', 'phone', 'address', 'status', 'created_at'])) {
                $query->orderBy("firms.{$sortBy}", $sortOrder);
            } else {
                $query->latest('firms.created_at');
            }
        } else {
            $query->latest('firms.created_at');
        }

        $firms = $query->paginate(10)->withQueryString();

        return view('firms.index', compact('firms'));
    }

    public function printReport(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Superadmin can manage Firms.');
        }

        $query = Firm::withCount('users', 'products', 'sales', 'purchases');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firms.name', 'like', "%{$search}%")
                  ->orWhere('firms.email', 'like', "%{$search}%")
                  ->orWhere('firms.phone', 'like', "%{$search}%")
                  ->orWhere('firms.address', 'like', "%{$search}%");
            });
        }

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'users_count') {
                $query->orderBy('users_count', $sortOrder);
            } elseif ($sortBy === 'products_count') {
                $query->orderBy('products_count', $sortOrder);
            } elseif (in_array($sortBy, ['name', 'email', 'phone', 'address', 'status', 'created_at'])) {
                $query->orderBy("firms.{$sortBy}", $sortOrder);
            } else {
                $query->latest('firms.created_at');
            }
        } else {
            $query->latest('firms.created_at');
        }

        $firms = $query->get();
        $totalFirms = $firms->count();
        $activeFirms = $firms->where('status', 'active')->count();
        $inactiveFirms = $totalFirms - $activeFirms;

        return view('firms.print', compact('firms', 'totalFirms', 'activeFirms', 'inactiveFirms'));
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
            'name'     => ['required', 'string', 'max:255', 'unique:firms,name'],
            'address'  => ['required', 'string'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['required', 'string', 'min:10', 'max:10', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'status'   => ['required', 'in:active,inactive'],
        ], [
            'email.unique' => 'This email address is already registered to another user.',
            'phone.unique' => 'This phone number is already registered to another user.',
            'phone.min'    => 'Firm Phone Number must be 10 digits.',
            'phone.max'    => 'Firm Phone Number must be 10 digits.',
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
                'name'     => $validated['name'] . ' Admin',
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone'    => $validated['phone'],
                'role'     => 'admin',
                'status'   => $validated['status'],
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
            'name'     => ['required', 'string', 'max:255', 'unique:firms,name,' . $firm->id],
            'address'  => ['required', 'string'],
            'email'    => ['required', 'email', 'max:255'],
            'phone'    => ['required', 'string', 'min:10', 'max:10'],
            'password' => ['nullable', 'string', 'min:6'],
            'status'   => ['required', 'in:active,inactive'],
        ], [
            'phone.min'    => 'Firm Phone Number must be 10 digits.',
            'phone.max'    => 'Firm Phone Number must be 10 digits.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $firm->update([
            'name'    => $validated['name'],
            'slug'    => Str::slug($validated['name']),
            'email'   => $validated['email'],
            'phone'   => $validated['phone'],
            'address' => $validated['address'],
            'status'  => $validated['status'],
        ]);

        $firm->users()->update([
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $adminUser = $firm->users()->where('role', 'admin')->first();
            if ($adminUser) {
                $adminUser->update([
                    'password' => Hash::make($validated['password']),
                ]);
            } else {
                $firm->users()->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }
        }

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
