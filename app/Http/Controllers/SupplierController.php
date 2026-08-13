<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['nullable', 'email', 'max:255'],
            'phone'           => ['required', 'string', 'max:20'],
            'company_name'    => ['nullable', 'string', 'max:255'],
            'tax_number'      => ['nullable', 'string', 'max:50'],
            'address'         => ['nullable', 'string'],
            'city'            => ['nullable', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric'],
            'status'          => ['required', 'in:active,inactive'],
        ]);

        $openingBal = $validated['opening_balance'] ?? 0;

        Supplier::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'],
            'company_name'    => $validated['company_name'],
            'tax_number'      => $validated['tax_number'],
            'address'         => $validated['address'],
            'city'            => $validated['city'],
            'opening_balance' => $openingBal,
            'current_balance' => $openingBal,
            'status'          => $validated['status'],
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier record created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load('purchases');
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number'   => ['nullable', 'string', 'max:50'],
            'address'      => ['nullable', 'string'],
            'city'         => ['nullable', 'string', 'max:100'],
            'status'       => ['required', 'in:active,inactive'],
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('success', 'Supplier record updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
