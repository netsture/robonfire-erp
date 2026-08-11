<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Services\PartyService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PartyController extends Controller
{
    protected $partyService;

    public function __construct(PartyService $partyService)
    {
        $this->partyService = $partyService;
    }

    /**
     * Display a listing of parties.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');

        // Validate sorting columns
        $allowedSorts = ['id', 'name', 'email', 'mobile', 'address'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query = Party::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $parties = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();

        return view('parties.index', compact('parties'));
    }

    /**
     * Store a newly created party.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'required|string',
        ]);

        try {
            $party = $this->partyService->createParty($request->all());
            return response()->json(['success' => true, 'message' => 'Party created successfully.', 'party' => $party]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified party.
     */
    public function edit(Party $party)
    {
        return response()->json($party);
    }

    /**
     * Update the specified party.
     */
    public function update(Request $request, Party $party)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'required|string',
        ]);

        try {
            $this->partyService->updateParty($party, $request->all());
            return response()->json(['success' => true, 'message' => 'Party updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified party.
     */
    public function destroy(Party $party)
    {
        try {
            $party->delete();
            return response()->json(['success' => true, 'message' => 'Party deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX search for Select2.
     */
    public function ajaxSearch(Request $request)
    {
        $search = $request->input('q');
        $parties = Party::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'address']);

        $results = [];
        foreach ($parties as $party) {
            $results[] = [
                'id' => $party->id,
                'text' => $party->name,
                'address' => $party->address, // include address for autofill
            ];
        }

        return response()->json(['results' => $results]);
    }
}
