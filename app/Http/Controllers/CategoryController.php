<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Category::withCount('products')->with('firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        $categories = $query->latest()->get();
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('categories.index', compact('categories', 'firms'));
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
                \Illuminate\Validation\Rule::unique('categories', 'name')->where('firm_id', $firmId),
            ],
            'description' => ['nullable', 'string'],
        ], [
            'name.unique' => 'The Category Name has already been taken.',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('name') ?: 'The Category Name has already been taken.',
                    'errors'  => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('open_modal', 'createCategoryModal');
        }

        $validated = $validator->validated();

        $category = Category::create([
            'firm_id'     => $firmId,
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $category->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('categories', 'name')
                    ->where('firm_id', $category->firm_id)
                    ->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_modal', 'editCategoryModal_' . $category->id);
        }

        $validated = $validator->validated();

        $category->update([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $category->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category containing products.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
