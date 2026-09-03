<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = StockAdjustment::with(['product', 'user', 'firm']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        $adjustments = $query->latest()->paginate(15);

        $prodQuery = Product::where('status', 'active');
        if (!$user->isSuperAdmin()) {
            $prodQuery->where('firm_id', $user->firm_id);
        }
        $products = $prodQuery->get();
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('inventory.index', compact('adjustments', 'products', 'firms'));
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
