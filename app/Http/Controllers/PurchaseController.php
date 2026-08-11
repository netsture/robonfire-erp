<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Party;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    /**
     * Get details (e.g. title, columns) for a purchase type.
     */
    private function getTypeDetails(string $type): array
    {
        switch ($type) {
            case 'inward':
                return [
                    'title' => 'Inward - Purchase',
                    'doc_label' => 'Bill No',
                    'doc_field' => 'bill_no',
                    'has_vehicle' => false,
                    'has_reason' => false,
                    'has_type_column' => false, // Inward doesn't have Type column in table spreadsheet
                ];
            case 'outward':
                return [
                    'title' => 'Outward',
                    'doc_label' => 'Chalan No',
                    'doc_field' => 'chalan_no',
                    'has_vehicle' => true,
                    'has_reason' => false,
                    'has_type_column' => true, // Outward has Type (Nos./KG/Mtr.)
                ];
            case 'returnable_material':
                return [
                    'title' => 'Returnable Material',
                    'doc_label' => 'Chalan No',
                    'doc_field' => 'chalan_no',
                    'has_vehicle' => false,
                    'has_reason' => true,
                    'has_type_column' => true,
                ];
            case 'returnable_chalan':
                return [
                    'title' => 'Returnable Chalan',
                    'doc_label' => 'Chalan No',
                    'doc_field' => 'chalan_no',
                    'has_vehicle' => false,
                    'has_reason' => false,
                    'has_type_column' => true,
                ];
            default:
                abort(404, 'Invalid purchase log type');
        }
    }

    /**
     * Display a listing of purchases for a type.
     */
    public function index(Request $request, string $type)
    {
        $details = $this->getTypeDetails($type);
        $search = $request->input('search');
        $sort = $request->input('sort', 'date');
        $direction = $request->input('direction', 'desc');

        // Validate sorting columns
        $docField = $details['doc_field'];
        $allowedSorts = ['id', 'date', $docField, 'grand_total_with_tax', 'party_name'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'date';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        // Left join with parties to allow searching and sorting by party name
        $query = Purchase::where('type', $type)
            ->leftJoin('parties', 'purchases.party_id', '=', 'parties.id')
            ->select('purchases.*', 'parties.name as party_name');

        if ($search) {
            $query->where(function ($q) use ($search, $docField) {
                $q->where('purchases.' . $docField, 'like', "%{$search}%")
                  ->orWhere('parties.name', 'like', "%{$search}%")
                  ->orWhere('purchases.grand_total_with_tax', 'like', "%{$search}%");
            });
        }

        if ($sort === 'party_name') {
            $query->orderBy('parties.name', $direction);
        } elseif ($sort === $docField) {
            $query->orderBy('purchases.' . $docField, $direction);
        } else {
            $query->orderBy('purchases.' . $sort, $direction);
        }

        $purchases = $query->paginate(10)->withQueryString();

        return view('purchases.index', compact('type', 'details', 'purchases'));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create(string $type)
    {
        $details = $this->getTypeDetails($type);
        $parties = Party::all(); // load initially, select2 will also search via ajax
        return view('purchases.form', compact('type', 'details', 'parties'));
    }

    /**
     * Store a newly created purchase.
     */
    public function store(Request $request, string $type)
    {
        $details = $this->getTypeDetails($type);

        // Dynamic validation
        $rules = [
            'party_id' => 'required|exists:parties,id',
            'date' => 'required|date',
            'project_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.hsn_code' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'required|numeric|min:0',
        ];

        if ($type === 'inward') {
            $rules['bill_no'] = 'required|string|max:255';
        } else {
            $rules['chalan_no'] = 'required|string|max:255';
        }

        if ($details['has_vehicle']) {
            $rules['vehicle'] = 'required|string|max:255';
        }

        if ($details['has_reason']) {
            $rules['reason'] = 'required|string|max:255';
        }

        if ($details['has_type_column']) {
            $rules['items.*.type'] = 'required|string|max:50';
        }

        $request->validate($rules);

        try {
            $data = $request->all();
            $data['type'] = $type;
            $purchase = $this->purchaseService->createPurchase($data);

            return response()->json([
                'success' => true,
                'message' => $details['title'] . ' created successfully.',
                'redirect' => route('purchases.index', ['type' => $type])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing.
     */
    public function edit(string $type, int $id)
    {
        $details = $this->getTypeDetails($type);
        $purchase = Purchase::with('items')->findOrFail($id);
        $parties = Party::all();
        return view('purchases.form', compact('type', 'details', 'purchase', 'parties'));
    }

    /**
     * Update the purchase.
     */
    public function update(Request $request, string $type, int $id)
    {
        $purchase = Purchase::findOrFail($id);
        $details = $this->getTypeDetails($type);

        // Dynamic validation
        $rules = [
            'party_id' => 'required|exists:parties,id',
            'date' => 'required|date',
            'project_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.hsn_code' => 'nullable|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'required|numeric|min:0',
        ];

        if ($type === 'inward') {
            $rules['bill_no'] = 'required|string|max:255';
        } else {
            $rules['chalan_no'] = 'required|string|max:255';
        }

        if ($details['has_vehicle']) {
            $rules['vehicle'] = 'required|string|max:255';
        }

        if ($details['has_reason']) {
            $rules['reason'] = 'required|string|max:255';
        }

        if ($details['has_type_column']) {
            $rules['items.*.type'] = 'required|string|max:50';
        }

        $request->validate($rules);

        try {
            $this->purchaseService->updatePurchase($purchase, $request->all());

            return response()->json([
                'success' => true,
                'message' => $details['title'] . ' updated successfully.',
                'redirect' => route('purchases.index', ['type' => $type])
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete the purchase.
     */
    public function destroy(string $type, int $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $this->purchaseService->deletePurchase($purchase);

            return response()->json(['success' => true, 'message' => 'Record deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
