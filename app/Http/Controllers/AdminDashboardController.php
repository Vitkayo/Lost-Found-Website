<?php

namespace App\Http\Controllers;

use App\Models\ItemClaim;
use App\Services\ClaimDataService;
use App\Services\ItemDataService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request, ItemDataService $items, ClaimDataService $claims)
    {
        $section = $request->query('section', 'items');
        $search = $request->query('search', '');
        $sort = $request->query('sort', 'desc');
        $status = $request->query('status', 'all');
        $category = $request->query('category', 'all');
        $claimFilter = $request->query('claim_status', 'all');
        $reviewStatus = $request->query('review_status', 'all');

        $allItems = $items->filtered([
            'status' => $status,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
        ]);

        $allClaims = $claims->filtered([
            'type' => $claimFilter,
            'status' => $reviewStatus,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
        ]);

        $claimStats = $claims->filtered([]);

        return view('admin.dashboard', [
            'section' => $section,
            'items' => $allItems,
            'claims' => $allClaims,
            'search' => $search,
            'sort' => $sort,
            'status' => $status,
            'category' => $category,
            'categories' => config('lostfound.categories'),
            'claimFilter' => $claimFilter,
            'reviewStatus' => $reviewStatus,
            'totalItems' => count($allItems),
            'lostItems' => collect($allItems)->where('status', 'lost')->count(),
            'foundItems' => collect($allItems)->where('status', 'found')->count(),
            'totalClaims' => count($claimStats),
            'ownershipClaims' => collect($claimStats)->where('type', 'claim')->count(),
            'pendingClaims' => collect($claimStats)->where('status', 'pending')->count(),
        ]);
    }

    public function destroy(string $id, ItemDataService $items)
    {
        $items->delete($id);

        return redirect()
            ->back()
            ->with('success', 'Report deleted.');
    }

    public function destroyClaim(string $id, ClaimDataService $claims)
    {
        $claims->delete($id);

        return redirect()
            ->back()
            ->with('success', 'Claim removed.');
    }

    public function reviewClaim(Request $request, ItemClaim $claim)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $claim->update([
            'status' => $validated['status'],
            'reviewed_at' => now(),
            'reviewed_by' => null,
        ]);

        return redirect()->back()->with('success', 'Claim '.$validated['status'].'.');
    }
}
