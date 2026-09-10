<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Supplier::with('firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(10);
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('suppliers.index', compact('suppliers', 'firms'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'company_name')->where('firm_id', $firmId)],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'min:10', 'max:10', 'regex:/^[0-9]{10}$/'],
            'gst_number'   => ['required', 'string', 'max:50'],
            'address'      => ['required', 'string'],
            'status'       => ['required', 'in:active,inactive'],
        ], [
            'company_name.unique' => 'Company Name already exists.',
            'phone.min'   => 'Phone number must be exactly 10 digits.',
            'phone.max'   => 'Phone number must be exactly 10 digits.',
            'phone.regex' => 'Phone number must contain 10 digits only.',
        ]);

        $supplier = Supplier::create([
            'firm_id'      => $firmId,
            'company_name' => $validated['company_name'],
            'email'        => $validated['email'] ?? null,
            'phone'        => $validated['phone'],
            'gst_number'   => $validated['gst_number'],
            'address'      => $validated['address'],
            'status'       => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Supplier created successfully.',
                'supplier' => $supplier
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier record created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $supplier->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $supplier->load('purchases');
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $supplier->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $supplier->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'company_name')->where('firm_id', $supplier->firm_id)->ignore($supplier->id)],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'min:10', 'max:10', 'regex:/^[0-9]{10}$/'],
            'gst_number'   => ['required', 'string', 'max:50'],
            'address'      => ['required', 'string'],
            'status'       => ['required', 'in:active,inactive'],
        ], [
            'company_name.unique' => 'Company Name already exists.',
            'phone.min'   => 'Phone number must be exactly 10 digits.',
            'phone.max'   => 'Phone number must be exactly 10 digits.',
            'phone.regex' => 'Phone number must contain 10 digits only.',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier record updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $supplier->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
