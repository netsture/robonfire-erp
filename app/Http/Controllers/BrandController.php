<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Brand::withCount('products');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        }

        $brands = $query->latest()->get();
        return view('brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Brand::create([
            'firm_id'     => $firmId,
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $brand->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

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
