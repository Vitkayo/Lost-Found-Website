<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemClaim;
use App\Services\ClaimDataService;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index(Request $request, ClaimDataService $claims)
    {
        return response()->json([
            'data' => $claims->filtered([
                'type' => $request->query('type', 'all'),
                'status' => $request->query('status'),
                'category' => $request->query('category', 'all'),
                'search' => $request->query('search'),
                'sort' => $request->query('sort', 'desc'),
            ]),
        ]);
    }

    public function store(Request $request, ClaimDataService $claims)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'claimant_name' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $claim = $claims->create($item, $validated);

        return response()->json([
            'message' => $item->status === 'found'
                ? 'Claim submitted successfully.'
                : 'Found report submitted successfully.',
            'data' => $claim,
        ], 201);
    }

    public function show(string $id)
    {
        $claim = ItemClaim::with('item')->find($id);

        if (! $claim) {
            return response()->json(['error' => 'Claim not found'], 404);
        }

        return response()->json(['data' => $claim->toDisplayArray()]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $claim = ItemClaim::with('item')->find($id);

        if (! $claim) {
            return response()->json(['error' => 'Claim not found'], 404);
        }

        $claim->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Claim status updated successfully',
            'data' => $claim->fresh('item')->toDisplayArray(),
        ]);
    }

    public function destroy(string $id, ClaimDataService $claims)
    {
        if (! $claims->delete($id)) {
            return response()->json(['error' => 'Claim not found'], 404);
        }

        return response()->json(['message' => 'Claim deleted successfully']);
    }
}
