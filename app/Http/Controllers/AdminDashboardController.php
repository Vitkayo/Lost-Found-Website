<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Item;
use App\Models\ItemClaim;
use App\Models\User;
use App\Notifications\ClaimDisputeResolvedNotification;
use App\Notifications\ReportModeratedNotification;
use App\Services\AuditService;
use App\Services\ClaimDataService;
use App\Services\EmailCodeService;
use App\Services\ItemDataService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $userRole = $request->query('user_role', 'all');
        $userStatus = $request->query('user_status', 'all');
        $userVerification = $request->query('user_verification', 'all');

        $itemFilters = [
            'include_claimed' => true,
            'status' => $status,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
        ];
        $allItems = $items->paginated($itemFilters, 15, 'items_page');
        $itemStats = $items->filtered($itemFilters);

        $claimFilters = [
            'type' => $claimFilter,
            'status' => $reviewStatus,
            'category' => $category,
            'search' => $search,
            'sort' => $sort,
        ];
        $allClaims = $claims->paginated($claimFilters, 15, 'claims_page');

        $claimStats = $claims->filtered([]);
        $usersQuery = User::query()
            ->when($search, fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            }))
            ->when($userRole !== 'all', fn ($query) => $query->where('role', $userRole))
            ->when($userStatus !== 'all', fn ($query) => $query->where('status', $userStatus))
            ->when($userVerification === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($userVerification === 'unverified', fn ($query) => $query->whereNull('email_verified_at'));

        $users = (clone $usersQuery)
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'users_page');
        $auditLogs = AuditLog::query()->latest()->paginate(20, ['*'], 'audit_page');
        $allUsers = User::query();

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
            'userRole' => $userRole,
            'userStatus' => $userStatus,
            'userVerification' => $userVerification,
            'totalItems' => count($itemStats),
            'lostItems' => collect($itemStats)->where('status', 'lost')->count(),
            'foundItems' => collect($itemStats)->where('status', 'found')->count(),
            'totalClaims' => count($claimStats),
            'ownershipClaims' => collect($claimStats)->where('type', 'claim')->count(),
            'pendingClaims' => collect($claimStats)->where('status', 'pending')->count(),
            'users' => $users,
            'auditLogs' => $auditLogs,
            'totalUsers' => $allUsers->count(),
            'activeUsers' => (clone $allUsers)->where('status', 'active')->count(),
            'suspendedUsers' => (clone $allUsers)->where('status', 'suspended')->count(),
            'adminUsers' => (clone $allUsers)->whereIn('role', ['admin', 'super_admin'])->count(),
            'unverifiedUsers' => (clone $allUsers)->whereNull('email_verified_at')->count(),
            'openDisputes' => ItemClaim::where('dispute_status', 'open')->count(),
        ]);
    }

    public function destroy(string $id, ItemDataService $items, AuditService $audit)
    {
        $item = Item::find($id);
        if ($item) {
            $audit->record('item.deleted', $item);
        }
        $items->delete($id);

        return redirect()
            ->back()
            ->with('success', 'Report deleted.');
    }

    public function destroyClaim(string $id, ClaimDataService $claims, AuditService $audit)
    {
        $claim = ItemClaim::find($id);
        if ($claim) {
            $audit->record('claim.deleted', $claim);
        }
        $claims->delete($id);

        return redirect()
            ->back()
            ->with('success', 'Claim removed.');
    }

    public function reviewClaim(Request $request, ItemClaim $claim, ClaimDataService $claims, AuditService $audit)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $claims->review($claim, $validated['status'], $request->user()?->id);
        $audit->record('claim.reviewed', $claim, $validated['status']);

        return redirect()->back()->with('success', 'Claim '.$validated['status'].'.');
    }

    public function updateUser(Request $request, User $user, AuditService $audit)
    {
        $actor = $request->user();

        if (! $actor?->isSuperAdmin()) {
            abort(403, 'Only super administrators can manage user access.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:user,admin,super_admin'],
            'status' => ['required', 'in:active,suspended'],
        ]);

        if ($actor->is($user)) {
            return back()->with('error', 'You cannot change your own administrator access.');
        }

        $wouldRemoveActiveSuperAdmin = $user->isSuperAdmin()
            && ($validated['role'] !== 'super_admin' || $validated['status'] !== 'active');

        if ($wouldRemoveActiveSuperAdmin && User::where('role', 'super_admin')->where('status', 'active')->count() <= 1) {
            return back()->with('error', 'At least one active super administrator is required.');
        }

        $user->update($validated);

        if ($validated['status'] === 'suspended') {
            $user->tokens()->delete();
        }

        $audit->record('user.updated', $user, "role={$validated['role']}; status={$validated['status']}");

        return back()->with('success', 'User access updated.');
    }

    public function resendVerification(User $user, EmailCodeService $codes, AuditService $audit)
    {
        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'This user is already verified.');
        }

        try {
            $codes->send($user, EmailCodeService::VERIFY_EMAIL);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        $audit->record('user.verification_resent', $user, $user->email);

        return back()->with('success', 'A new verification code has been sent to the user.');
    }

    public function sendPasswordReset(User $user, EmailCodeService $codes, AuditService $audit)
    {
        $codes->sendPasswordReset($user->email);
        $audit->record('user.password_reset_requested', $user, $user->email);

        return back()->with('success', 'A password reset code has been emailed to the user.');
    }

    public function moderateItem(Request $request, Item $item, AuditService $audit)
    {
        $validated = $request->validate([
            'moderation_status' => ['required', 'in:active,hidden'],
            'reason' => ['nullable', 'required_if:moderation_status,hidden', 'string', 'max:1000'],
        ]);

        $item->update([
            'moderation_status' => $validated['moderation_status'],
            'moderation_reason' => $validated['reason'] ?? null,
        ]);
        $item->user?->notify(new ReportModeratedNotification($item->fresh()));
        $audit->record('item.moderated', $item, $validated['reason'] ?? $validated['moderation_status']);

        return back()->with('success', 'Report moderation updated.');
    }

    public function resolveDispute(Request $request, ItemClaim $claim, AuditService $audit)
    {
        $validated = $request->validate([
            'dispute_status' => ['required', 'in:resolved,dismissed'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $claim->update($validated);
        $claim = $claim->fresh(['item', 'user']);
        $claim->user?->notify(new ClaimDisputeResolvedNotification($claim));
        $audit->record('claim.dispute_resolved', $claim, json_encode($validated));

        return back()->with('success', 'Claim dispute resolved.');
    }
}
