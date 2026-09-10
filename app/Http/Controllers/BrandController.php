<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Brand::withCount('products')->with('firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        $brands = $query->latest()->get();
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('brands.index', compact('brands', 'firms'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('brands', 'name')->where('firm_id', $firmId),
            ],
            'description' => ['nullable', 'string'],
        ], [
            'name.unique' => 'The Brand Name has already been taken.',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('name') ?: 'The Brand Name has already been taken.',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('open_modal', 'createBrandModal');
        }

        $validated = $validator->validated();

        $brand = Brand::create([
            'firm_id'     => $firmId,
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'brand'   => $brand,
            ]);
        }

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $brand->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('brands', 'name')
                    ->where('firm_id', $brand->firm_id)
                    ->ignore($brand->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_modal', 'editBrandModal_' . $brand->id);
        }

        $validated = $validator->validated();

        $brand->update([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $brand->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        if ($brand->products()->count() > 0) {
            return back()->with('error', 'Cannot delete brand associated with existing products.');
        }

        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
