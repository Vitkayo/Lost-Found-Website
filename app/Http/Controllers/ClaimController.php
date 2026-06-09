<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ClaimDataService;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index(Request $request, ClaimDataService $claims)
    {
        $filter = $request->query('type', $request->query('status', 'all'));
        $search = $request->query('search', '');
        $sort = $request->query('sort', 'desc');

        return view('claims.index', [
            'claims' => $claims->filtered([
                'type' => $filter,
                'search' => $search,
                'sort' => $sort,
            ]),
            'filter' => $filter,
            'search' => $search,
            'sort' => $sort,
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

        $message = $item->status === 'found'
            ? 'Claim submitted successfully. The reporter will contact you soon.'
            : 'Found report submitted successfully. The owner will contact you soon.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'claim' => $claim,
            ]);
        }

        if ($request->boolean('modal_flow')) {
            return redirect()
                ->back()
                ->with('success', $message);
        }

        return redirect()
            ->route('claims.index', ['type' => $item->status === 'found' ? 'claim' : 'return'])
            ->with('success', $message);
    }
}
