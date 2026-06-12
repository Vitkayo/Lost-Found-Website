<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemClaim;
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
            'verification_answer' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ($item->status === 'found' && $item->verification_question && blank($validated['verification_answer'] ?? null)) {
            return back()->withErrors(['verification_answer' => 'Answer the ownership verification question.']);
        }

        $claim = $claims->create($item, $validated, $request->user()->id);

        $message = $item->status === 'found'
            ? 'Claim submitted for verification. The reporter will review your answer.'
            : 'Found report submitted. The owner will review it.';

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

    public function review(Request $request, ItemClaim $claim, ClaimDataService $claims)
    {
        $claim->load('item');
        abort_unless(
            $claim->item && (int) $claim->item->user_id === (int) $request->user()->id,
            403
        );

        $validated = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $claims->review($claim, $validated['status'], $request->user()->id);

        return back()->with('success', 'Claim '.$validated['status'].' successfully.');
    }
}
