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
        $query = Product::with('category', 'brand', 'firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('products.index', compact('products', 'categories', 'brands', 'firms'));
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
        return view('products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'name'           => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->where('firm_id', $firmId)
                    ->where('category_id', $request->input('category_id'))
                    ->where('brand_id', $request->input('brand_id')),
            ],
            'hsn_code'       => ['required', 'string', 'max:50'],
            'category_id'    => ['required', 'exists:categories,id'],
            'brand_id'       => ['required', 'exists:brands,id'],
            'unit'           => ['required', 'string', 'max:50'],
            'cost_price'     => ['nullable', 'numeric', 'min:0'],
            'selling_price'  => ['nullable', 'numeric', 'min:0'],
            'tax_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
        ], [
            'name.unique'    => 'A product with this Title, Category, and Brand combination already exists for your firm.',
        ]);

        Product::create([
            'firm_id'        => $firmId,
            'name'           => $validated['name'],
            'hsn_code'       => $validated['hsn_code'],
            'category_id'    => $validated['category_id'],
            'brand_id'       => $validated['brand_id'],
            'unit'           => $validated['unit'],
            'cost_price'     => $validated['cost_price'] ?? 0.00,
            'selling_price'  => $validated['selling_price'] ?? 0.00,
            'tax_percent'    => $validated['tax_percent'],
            'alert_quantity' => $validated['alert_quantity'],
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
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
            'name'           => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->where('firm_id', $product->firm_id)
                    ->where('category_id', $request->input('category_id'))
                    ->where('brand_id', $request->input('brand_id'))
                    ->ignore($product->id),
            ],
            'hsn_code'       => ['required', 'string', 'max:50'],
            'category_id'    => ['required', 'exists:categories,id'],
            'brand_id'       => ['required', 'exists:brands,id'],
            'unit'           => ['required', 'string', 'max:50'],
            'cost_price'     => ['nullable', 'numeric', 'min:0'],
            'selling_price'  => ['nullable', 'numeric', 'min:0'],
            'tax_percent'    => ['required', 'numeric', 'min:0', 'max:100'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
        ], [
            'name.unique'    => 'A product with this Title, Category, and Brand combination already exists for your firm.',
        ]);

        $validated['cost_price'] = $validated['cost_price'] ?? 0.00;
        $validated['selling_price'] = $validated['selling_price'] ?? 0.00;
        if (!isset($validated['stock_quantity'])) {
            $validated['stock_quantity'] = $product->stock_quantity;
        }

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
