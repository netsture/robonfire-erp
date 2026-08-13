<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with('product', 'user')->latest()->paginate(15);
        $products = Product::where('status', 'active')->get();

        return view('inventory.index', compact('adjustments', 'products'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:add,subtract'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'reason'     => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['type'] === 'subtract' && $product->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Cannot subtract more stock than available (' . $product->stock_quantity . ' in stock).');
        }

        if ($validated['type'] === 'add') {
            $product->increment('stock_quantity', $validated['quantity']);
        } else {
            $product->decrement('stock_quantity', $validated['quantity']);
        }

        StockAdjustment::create([
            'product_id' => $product->id,
            'user_id'    => auth()->id(),
            'type'       => $validated['type'],
            'quantity'   => $validated['quantity'],
            'reason'     => $validated['reason'],
        ]);

        return back()->with('success', 'Stock adjustment recorded successfully. New stock: ' . $product->stock_quantity . ' ' . $product->unit);
    }
}
