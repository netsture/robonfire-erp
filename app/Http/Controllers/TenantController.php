<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of tenants.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');

        // Validate sorting columns
        $allowedSorts = ['id', 'name', 'domain', 'database', 'status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Tenant::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('database', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $tenants = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email|unique:users,email',
            'mobile' => 'required|string|max:20',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->tenantService->createTenant($request->all());
            return response()->json(['success' => true, 'message' => 'Tenant and Administrator created successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant)
    {
        return response()->json($tenant);
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:tenants,email,' . $tenant->id . '|unique:users,email,NULL,id,tenant_id,!' . $tenant->id,
            'mobile' => 'required|string|max:20',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            $this->tenantService->updateTenant($tenant, $request->all());
            return response()->json(['success' => true, 'message' => 'Tenant updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
