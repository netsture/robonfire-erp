<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Project;
use App\Models\ProjectExpense;
use Illuminate\Http\Request;

class ProjectExpenseController extends Controller
{
    private function checkAccess(Project $project, bool $isWriteAction = false)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            if ($isWriteAction) {
                abort(403, 'SuperAdmin has view-only access.');
            }
        } else {
            if ($project->firm_id !== $user->firm_id) {
                abort(403, 'Unauthorized access to project expenses.');
            }
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ProjectExpense::with(['project', 'project.firm']);

        if (!$user->isSuperAdmin()) {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('firm_id', $user->firm_id);
            });
        } elseif ($request->filled('firm_id')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('firm_id', $request->firm_id);
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('expense_date', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('project_name', 'like', "%{$search}%")
                         ->orWhere('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $totalExpensesQuery = clone $query;
        $totalExpensesAmount = $totalExpensesQuery->sum('amount');
        $totalExpensesCount  = $totalExpensesQuery->count();

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'project_name') {
                $query->join('projects', 'project_expenses.project_id', '=', 'projects.id')
                      ->select('project_expenses.*')
                      ->orderBy('projects.project_name', $sortOrder);
            } elseif (in_array($sortBy, ['expense_date', 'item_name', 'description', 'amount', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->latest('expense_date');
            }
        } else {
            $query->latest('expense_date');
        }

        $expenses = $query->paginate(10)->withQueryString();

        $projectQuery = Project::query();
        if (!$user->isSuperAdmin()) {
            $projectQuery->where('firm_id', $user->firm_id);
        }
        $projects = $projectQuery->latest()->get();

        $firms = $user->isSuperAdmin() ? Firm::all() : collect();

        return view('projects.expenses_index', compact(
            'expenses',
            'projects',
            'firms',
            'totalExpensesAmount',
            'totalExpensesCount'
        ));
    }

    public function printReport(Request $request)
    {
        $user = auth()->user();
        $query = ProjectExpense::with(['project', 'project.firm']);

        if (!$user->isSuperAdmin()) {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('firm_id', $user->firm_id);
            });
        } elseif ($request->filled('firm_id')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('firm_id', $request->firm_id);
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('amount', 'like', "%{$search}%")
                  ->orWhere('expense_date', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('project_name', 'like', "%{$search}%")
                         ->orWhere('po_number', 'like', "%{$search}%");
                  });
            });
        }

        $totalExpensesQuery = clone $query;
        $totalExpensesAmount = $totalExpensesQuery->sum('amount');
        $totalExpensesCount  = $totalExpensesQuery->count();

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'project_name') {
                $query->join('projects', 'project_expenses.project_id', '=', 'projects.id')
                      ->select('project_expenses.*')
                      ->orderBy('projects.project_name', $sortOrder);
            } elseif (in_array($sortBy, ['expense_date', 'item_name', 'description', 'amount'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->latest('expense_date');
            }
        } else {
            $query->latest('expense_date');
        }

        $expenses = $query->get();

        return view('projects.expenses_print', compact(
            'expenses',
            'totalExpensesAmount',
            'totalExpensesCount'
        ));
    }

    public function store(Request $request, Project $project)
    {
        $this->checkAccess($project, true);

        $validated = $request->validate([
            'item_name'    => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'document'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $uploadDir = public_path('uploads/project_expenses');

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $filename);
            $documentPath = 'uploads/project_expenses/' . $filename;
        }

        $project->expenses()->create([
            'item_name'     => $validated['item_name'],
            'description'   => $validated['description'] ?? null,
            'amount'        => $validated['amount'],
            'expense_date'  => $validated['expense_date'],
            'document_path' => $documentPath,
        ]);

        return back()->with('success', 'Project expense entry added successfully.');
    }

    public function storeGeneral(Request $request)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            abort(403, 'SuperAdmin has view-only access.');
        }

        $validated = $request->validate([
            'project_id'   => ['required', 'exists:projects,id'],
            'item_name'    => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'document'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if ($project->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to project expenses.');
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $uploadDir = public_path('uploads/project_expenses');

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $filename);
            $documentPath = 'uploads/project_expenses/' . $filename;
        }

        ProjectExpense::create([
            'project_id'    => $project->id,
            'item_name'     => $validated['item_name'],
            'description'   => $validated['description'] ?? null,
            'amount'        => $validated['amount'],
            'expense_date'  => $validated['expense_date'],
            'document_path' => $documentPath,
        ]);

        return back()->with('success', 'Project expense entry added successfully.');
    }

    public function update(Request $request, Project $project, ProjectExpense $expense)
    {
        if ($expense->project_id !== $project->id) {
            abort(404);
        }

        $this->checkAccess($project, true);

        $validated = $request->validate([
            'item_name'    => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'document'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ]);

        $documentPath = $expense->document_path;
        if ($request->hasFile('document')) {
            if ($expense->document_path && file_exists(public_path($expense->document_path))) {
                @unlink(public_path($expense->document_path));
            }

            $file = $request->file('document');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $uploadDir = public_path('uploads/project_expenses');

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file->move($uploadDir, $filename);
            $documentPath = 'uploads/project_expenses/' . $filename;
        }

        $expense->update([
            'item_name'     => $validated['item_name'],
            'description'   => $validated['description'] ?? null,
            'amount'        => $validated['amount'],
            'expense_date'  => $validated['expense_date'],
            'document_path' => $documentPath,
        ]);

        return back()->with('success', 'Project expense entry updated successfully.');
    }

    public function destroy(Project $project, ProjectExpense $expense)
    {
        if ($expense->project_id !== $project->id) {
            abort(404);
        }

        $this->checkAccess($project, true);

        if ($expense->document_path && file_exists(public_path($expense->document_path))) {
            @unlink(public_path($expense->document_path));
        }

        $expense->delete();

        return back()->with('success', 'Project expense entry deleted successfully.');
    }

    public function download(Project $project, ProjectExpense $expense)
    {
        if ($expense->project_id !== $project->id) {
            abort(404);
        }

        $this->checkAccess($project, false);

        if ($expense->document_path && file_exists(public_path($expense->document_path))) {
            return response()->download(public_path($expense->document_path));
        }

        return back()->with('error', 'Expense document file not found.');
    }
}
