<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemClaim;
use App\Services\AuditService;
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
            'claims' => $claims->paginated([
                'type' => $filter,
                'search' => $search,
                'sort' => $sort,
            ], 12),
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
            'ownership_proof' => ['nullable', 'string', 'max:1000'],
            'verification_answer' => ['nullable', 'string', 'max:1000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ((int) $item->user_id === (int) $request->user()->id) {
            return back()->withErrors(['item_id' => 'You cannot claim your own report.']);
        }

        $hasOpenClaim = ItemClaim::query()
            ->where('item_id', $item->id)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($hasOpenClaim) {
            return back()->withErrors(['item_id' => 'You already submitted a claim for this item.']);
        }

        $ownershipProof = $validated['ownership_proof']
            ?? $validated['message']
            ?? $validated['verification_answer']
            ?? null;

        if ($item->status === 'found' && blank($ownershipProof)) {
            return back()->withErrors(['ownership_proof' => 'Explain how you can prove this item belongs to you.']);
        }

        $validated['ownership_proof'] = $ownershipProof;
        $claim = $claims->create($item, $validated, $request->user()->id, $request->file('proof_image'));

        $message = $item->status === 'found'
            ? 'Claim submitted. The reporter will review your private proof.'
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

    public function dispute(Request $request, ItemClaim $claim, AuditService $audit)
    {
        abort_unless((int) $claim->user_id === (int) $request->user()->id, 403);
        abort_unless(in_array($claim->status, ['approved', 'rejected'], true), 422);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $claim->update([
            'dispute_status' => 'open',
            'dispute_reason' => $validated['reason'],
        ]);
        $audit->record('claim.disputed', $claim, $validated['reason']);

        return back()->with('success', 'Dispute submitted for administrator review.');
    }
}
