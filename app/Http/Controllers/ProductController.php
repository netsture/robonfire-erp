<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Product::with('category', 'brand');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('hsn_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
        }

        $products = $query->latest()->paginate(10);

        $catQuery = Category::query();
        $brandQuery = Brand::query();
        if (!$user->isSuperAdmin()) {
            $catQuery->where('firm_id', $user->firm_id);
            $brandQuery->where('firm_id', $user->firm_id);
        }

        $categories = $catQuery->get();
        $brands = $brandQuery->get();

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $user = auth()->user();
        $catQuery = Category::query();
        $brandQuery = Brand::query();
        if (!$user->isSuperAdmin()) {
            $catQuery->where('firm_id', $user->firm_id);
            $brandQuery->where('firm_id', $user->firm_id);
        }

        $categories = $catQuery->get();
        $brands = $brandQuery->get();
        $autoSku = 'SKU-' . strtoupper(Str::random(6));
        return view('products.create', compact('categories', 'brands', 'autoSku'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['required', 'string', 'max:100'],
            'barcode'        => ['nullable', 'string', 'max:100'],
            'hsn_code'       => ['required', 'string', 'max:50'],
            'category_id'    => ['required', 'exists:categories,id'],
            'brand_id'       => ['required', 'exists:brands,id'],
            'unit'           => ['required', 'string', 'max:50'],
            'cost_price'     => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'tax_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        Product::create([
            'firm_id'        => $firmId,
            'name'           => $validated['name'],
            'sku'            => strtoupper($validated['sku']),
            'barcode'        => $validated['barcode'] ?? $validated['sku'],
            'hsn_code'       => $validated['hsn_code'],
            'category_id'    => $validated['category_id'],
            'brand_id'       => $validated['brand_id'],
            'unit'           => $validated['unit'],
            'cost_price'     => $validated['cost_price'],
            'selling_price'  => $validated['selling_price'],
            'tax_percent'    => $validated['tax_percent'],
            'alert_quantity' => $validated['alert_quantity'],
            'stock_quantity' => $validated['stock_quantity'],
            'description'    => $validated['description'] ?? null,
            'status'         => $validated['status'],
        ]);

        return redirect()->route('products.index')->with('success', 'Product created and added to catalog.');
    }

    public function show(Product $product)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $product->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $product->load('category', 'brand', 'stockAdjustments.user');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $product->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $catQuery = Category::query();
        $brandQuery = Brand::query();
        if (!$user->isSuperAdmin()) {
            $catQuery->where('firm_id', $user->firm_id);
            $brandQuery->where('firm_id', $user->firm_id);
        }

        $categories = $catQuery->get();
        $brands = $brandQuery->get();

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $product->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['required', 'string', 'max:100'],
            'barcode'        => ['nullable', 'string', 'max:100'],
            'hsn_code'       => ['required', 'string', 'max:50'],
            'category_id'    => ['required', 'exists:categories,id'],
            'brand_id'       => ['required', 'exists:brands,id'],
            'unit'           => ['required', 'string', 'max:50'],
            'cost_price'     => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'tax_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product details updated successfully.');
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $product->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted from inventory.');
    }
}
