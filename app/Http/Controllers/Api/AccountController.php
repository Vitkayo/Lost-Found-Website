<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemClaim;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $allReports = Item::query()
            ->where('user_id', $user->id)
            ->withCount([
                'claims as approved_claims_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->get();
        $allClaims = ItemClaim::query()->where('user_id', $user->id)->get();

        return response()->json([
            'user' => $user,
            'stats' => [
                'reports' => $allReports->count(),
                'active_reports' => $allReports->where('approved_claims_count', 0)->count(),
                'claims' => $allClaims->count(),
                'approved_claims' => $allClaims->where('status', 'approved')->count(),
                'pending_claims' => $allClaims->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function reports(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,lost,found'],
            'sort' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $status = $validated['status'] ?? 'all';
        $direction = ($validated['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $reports = Item::query()
            ->where('user_id', $request->user()->id)
            ->withCount([
                'claims',
                'claims as pending_claims_count' => fn ($query) => $query->where('status', 'pending'),
                'claims as approved_claims_count' => fn ($query) => $query->where('status', 'approved'),
            ])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderBy('reported_at', $direction)
            ->paginate($perPage)
            ->through(fn (Item $item) => array_merge($item->toLegacyArray(), [
                'claims_count' => $item->claims_count,
                'pending_claims_count' => $item->pending_claims_count,
                'approved_claims_count' => $item->approved_claims_count,
            ]));

        return response()->json($reports);
    }

    public function claims(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,pending,approved,rejected'],
            'sort' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $status = $validated['status'] ?? 'all';
        $direction = ($validated['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $claims = ItemClaim::query()
            ->where('user_id', $request->user()->id)
            ->with('item')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderBy('created_at', $direction)
            ->paginate($perPage)
            ->through(fn (ItemClaim $claim) => $claim->toDisplayArray());

        return response()->json($claims);
    }
}
