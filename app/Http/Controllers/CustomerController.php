<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
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
            'credit_limit'    => ['nullable', 'numeric', 'min:0'],
            'opening_balance' => ['nullable', 'numeric'],
            'status'          => ['required', 'in:active,inactive'],
        ]);

        $openingBal = $validated['opening_balance'] ?? 0;

        Customer::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'],
            'company_name'    => $validated['company_name'],
            'tax_number'      => $validated['tax_number'],
            'address'         => $validated['address'],
            'city'            => $validated['city'],
            'credit_limit'    => $validated['credit_limit'] ?? 0,
            'opening_balance' => $openingBal,
            'current_balance' => $openingBal,
            'status'          => $validated['status'],
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer record added successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load('sales');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_number'   => ['nullable', 'string', 'max:50'],
            'address'      => ['nullable', 'string'],
            'city'         => ['nullable', 'string', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'status'       => ['required', 'in:active,inactive'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer record deleted.');
    }
}
