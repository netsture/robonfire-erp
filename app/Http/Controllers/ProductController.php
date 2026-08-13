<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
        }

        $products = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        $autoSku = 'SKU-' . strtoupper(Str::random(6));
        return view('products.create', compact('categories', 'autoSku'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['required', 'string', 'max:100', 'unique:products'],
            'barcode'        => ['nullable', 'string', 'max:100'],
            'category_id'    => ['required', 'exists:categories,id'],
            'unit'           => ['required', 'string', 'max:50'],
            'cost_price'     => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'tax_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'alert_quantity' => ['required', 'integer', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
        ]);

        Product::create([
            'name'           => $validated['name'],
            'sku'            => strtoupper($validated['sku']),
            'barcode'        => $validated['barcode'] ?? $validated['sku'],
            'category_id'    => $validated['category_id'],
            'unit'           => $validated['unit'],
            'cost_price'     => $validated['cost_price'],
            'selling_price'  => $validated['selling_price'],
            'tax_percent'    => $validated['tax_percent'] ?? 0,
            'alert_quantity' => $validated['alert_quantity'],
            'stock_quantity' => $validated['stock_quantity'],
            'description'    => $validated['description'] ?? null,
            'status'         => $validated['status'],
        ]);

        return redirect()->route('products.index')->with('success', 'Product created and added to catalog.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'stockAdjustments.user');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'barcode'        => ['nullable', 'string', 'max:100'],
            'category_id'    => ['required', 'exists:categories,id'],
            'unit'           => ['required', 'string', 'max:50'],
            'cost_price'     => ['required', 'numeric', 'min:0'],
            'selling_price'  => ['required', 'numeric', 'min:0'],
            'tax_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
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
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted from inventory.');
    }
}
