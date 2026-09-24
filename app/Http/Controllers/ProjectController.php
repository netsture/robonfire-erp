<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Firm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Project::with(['firm', 'expenses']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%")
                  ->orWhere('po_amount', 'like', "%{$search}%")
                  ->orWhere('po_date', 'like', "%{$search}%")
                  ->orWhereRaw("DATE_FORMAT(po_date, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(po_date, '%m-%d-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(po_date, '%d/%m/%Y') LIKE ?", ["%{$search}%"]);

                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $search, $matches)) {
                    $day   = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year  = $matches[3];
                    $dateFormatted = "{$year}-{$month}-{$day}";
                    $q->orWhere('po_date', 'like', "%{$dateFormatted}%");
                }
            });
        }

        $totalQuery = clone $query;
        $totalProjects = $totalQuery->count();
        $totalPoAmount = (float) $totalQuery->sum('po_amount');

        $projectIds = (clone $query)->pluck('id');
        $totalExpensesAmount = (float) \App\Models\ProjectExpense::whereIn('project_id', $projectIds)->sum('amount');
        $totalFinalAmount = $totalPoAmount - $totalExpensesAmount;

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'total_expenses') {
                $query->withSum('expenses', 'amount')
                      ->orderBy('expenses_sum_amount', $sortOrder);
            } elseif ($sortBy === 'final_amount') {
                $query->withSum('expenses', 'amount')
                      ->orderByRaw("(po_amount - COALESCE(expenses_sum_amount, 0)) {$sortOrder}");
            } elseif (in_array($sortBy, ['project_name', 'po_number', 'po_date', 'po_amount', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $projects = $query->paginate(10)->withQueryString();
        $firms = $user->isSuperAdmin() ? Firm::all() : collect();

        return view('projects.index', compact('projects', 'firms', 'totalProjects', 'totalPoAmount', 'totalExpensesAmount', 'totalFinalAmount'));
    }

    public function printReport(Request $request)
    {
        $user = auth()->user();
        $query = Project::with(['firm', 'expenses']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%")
                  ->orWhere('po_amount', 'like', "%{$search}%")
                  ->orWhere('po_date', 'like', "%{$search}%")
                  ->orWhereRaw("DATE_FORMAT(po_date, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(po_date, '%m-%d-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(po_date, '%d/%m/%Y') LIKE ?", ["%{$search}%"]);

                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $search, $matches)) {
                    $day   = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year  = $matches[3];
                    $dateFormatted = "{$year}-{$month}-{$day}";
                    $q->orWhere('po_date', 'like', "%{$dateFormatted}%");
                }
            });
        }

        $totalQuery = clone $query;
        $totalProjects = $totalQuery->count();
        $totalPoAmount = (float) $totalQuery->sum('po_amount');

        $projectIds = (clone $query)->pluck('id');
        $totalExpensesAmount = (float) \App\Models\ProjectExpense::whereIn('project_id', $projectIds)->sum('amount');
        $totalFinalAmount = $totalPoAmount - $totalExpensesAmount;

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'total_expenses') {
                $query->withSum('expenses', 'amount')
                      ->orderBy('expenses_sum_amount', $sortOrder);
            } elseif ($sortBy === 'final_amount') {
                $query->withSum('expenses', 'amount')
                      ->orderByRaw("(po_amount - COALESCE(expenses_sum_amount, 0)) {$sortOrder}");
            } elseif (in_array($sortBy, ['project_name', 'po_number', 'po_date', 'po_amount'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $projects = $query->get();

        return view('projects.print', compact('projects', 'totalProjects', 'totalPoAmount', 'totalExpensesAmount', 'totalFinalAmount'));
    }

    public function create()
    {
        $user = auth()->user();
        $firms = $user->isSuperAdmin() ? Firm::all() : collect();

        return view('projects.create', compact('firms'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'project_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'project_name')->where('firm_id', $firmId),
            ],
            'po_number'    => ['required', 'string', 'max:255'],
            'po_date'      => ['required', 'date'],
            'po_amount'    => ['required', 'numeric', 'min:0'],
            'firm_id'      => [$user->isSuperAdmin() ? 'required' : 'nullable', 'exists:firms,id'],
        ], [
            'project_name.unique' => 'The Project Name has already been taken.',
            'po_number.required'  => 'The PO Number field is required.',
            'po_date.required'    => 'The PO Date field is required.',
            'po_amount.required'  => 'The PO Amount field is required.',
        ]);

        Project::create([
            'firm_id'      => $firmId,
            'project_name' => $validated['project_name'],
            'po_number'    => $validated['po_number'],
            'po_date'      => $validated['po_date'],
            'po_amount'    => $validated['po_amount'],
        ]);

        return redirect()->route('projects.index')->with('success', 'Project entry created successfully.');
    }

    public function show(Project $project)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $project->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to project record.');
        }

        $project->load(['firm', 'expenses']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $project->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to project record.');
        }

        $firms = $user->isSuperAdmin() ? Firm::all() : collect();

        return view('projects.edit', compact('project', 'firms'));
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $project->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to project record.');
        }

        $targetFirmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? $project->firm_id) : $project->firm_id;

        $validated = $request->validate([
            'project_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'project_name')->where('firm_id', $targetFirmId)->ignore($project->id),
            ],
            'po_number'    => ['required', 'string', 'max:255'],
            'po_date'      => ['required', 'date'],
            'po_amount'    => ['required', 'numeric', 'min:0'],
            'firm_id'      => [$user->isSuperAdmin() ? 'required' : 'nullable', 'exists:firms,id'],
        ], [
            'project_name.unique' => 'The Project Name has already been taken.',
            'po_number.required'  => 'The PO Number field is required.',
            'po_date.required'    => 'The PO Date field is required.',
            'po_amount.required'  => 'The PO Amount field is required.',
        ]);

        $project->update([
            'firm_id'      => $targetFirmId,
            'project_name' => $validated['project_name'],
            'po_number'    => $validated['po_number'],
            'po_date'      => $validated['po_date'],
            'po_amount'    => $validated['po_amount'],
        ]);

        return redirect()->route('projects.index')->with('success', 'Project entry updated successfully.');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $project->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to project record.');
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project entry deleted successfully.');
    }
}
