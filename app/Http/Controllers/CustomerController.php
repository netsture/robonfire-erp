<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Customer::with('firm');

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
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(10);
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('customers.index', compact('customers', 'firms'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255', Rule::unique('customers', 'company_name')->where('firm_id', $firmId)],
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

        $customer = Customer::create([
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
                'message'  => 'Customer created successfully.',
                'customer' => $customer
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer record added successfully.');
    }

    public function show(Customer $customer)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $customer->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $customer->load('sales');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $customer->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $customer->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255', Rule::unique('customers', 'company_name')->where('firm_id', $customer->firm_id)->ignore($customer->id)],
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

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $customer->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer record deleted.');
    }
}
