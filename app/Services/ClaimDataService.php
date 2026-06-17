<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemClaim;
use App\Notifications\ClaimReviewedNotification;
use App\Notifications\ClaimSubmittedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ClaimDataService
{
    public function __construct(private readonly ImageOptimizationService $images) {}

    public function create(
        Item $item,
        array $data,
        ?int $userId = null,
        ?UploadedFile $proofImage = null
    ): array {
        $type = $item->status === 'found' ? 'claim' : 'found';
        [, $proofImagePath] = $this->images->store($proofImage, 'claim-proofs');
        $ownershipProof = $data['ownership_proof']
            ?? $data['message']
            ?? $data['verification_answer']
            ?? null;

        $claim = ItemClaim::create([
            'item_id' => $item->id,
            'user_id' => $userId,
            'type' => $type,
            'status' => 'pending',
            'claimant_name' => $data['claimant_name'] ?? null,
            'contact_info' => $data['contact_info'],
            'message' => $ownershipProof,
            'verification_answer' => null,
            'proof_image_path' => $proofImagePath ?: null,
        ]);

        $claim->load('item');
        $claim->item?->user?->notify(new ClaimSubmittedNotification($claim));

        return $claim->toDisplayArray();
    }

    public function review(ItemClaim $claim, string $status, ?int $reviewerId): array
    {
        $claim->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId ?: null,
        ]);

        $claim = $claim->fresh(['item', 'user']);
        $claim->user?->notify(new ClaimReviewedNotification($claim));

        return $claim->toDisplayArray();
    }

    public function recent(int $limit = 4): array
    {
        return ItemClaim::query()
            ->with('item')
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ItemClaim $claim) => $claim->toDisplayArray())
            ->all();
    }

    public function filtered(array $filters = []): array
    {
        return $this->filteredQuery($filters)
            ->get()
            ->map(fn (ItemClaim $claim) => $claim->toDisplayArray())
            ->all();
    }

    public function paginated(array $filters = [], int $perPage = 12, string $pageName = 'page'): LengthAwarePaginator
    {
        $paginator = $this->filteredQuery($filters)->paginate($perPage, ['*'], $pageName);
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (ItemClaim $claim) => $claim->toDisplayArray())
        );

        return $paginator;
    }

    public function delete(string $id): bool
    {
        $claim = ItemClaim::find($id);
        if (! $claim) {
            return false;
        }

        if ($claim->proof_image_path) {
            Storage::disk('public')->delete($claim->proof_image_path);
        }

        $claim->delete();

        return true;
    }

    private function filteredQuery(array $filters = [])
    {
        $query = ItemClaim::query()->with('item');

        if (! auth()->user()?->isAdmin()) {
            $userId = auth()->id();
            $query->where(function ($visibility) use ($userId) {
                $visibility->where('status', 'approved');

                if ($userId) {
                    $visibility->orWhere('user_id', $userId)
                        ->orWhereHas('item', fn ($item) => $item->where('user_id', $userId));
                }
            });
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $type = $filters['type'] === 'return' ? 'found' : $filters['type'];

            if (in_array($type, ['claim', 'found'], true)) {
                $query->where('type', $type);
            }
        }

        if (! empty($filters['status']) && in_array($filters['status'], ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['status']) && in_array($filters['status'], ['lost', 'found'], true)) {
            $query->whereHas('item', fn ($q) => $q->where('status', $filters['status']));
        }

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->whereHas('item', fn ($q) => $q->where('category', $filters['category']));
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('claimant_name', 'like', "%{$term}%")
                    ->orWhere('contact_info', 'like', "%{$term}%")
                    ->orWhere('message', 'like', "%{$term}%")
                    ->orWhereHas('item', fn ($iq) => $iq->where('title', 'like', "%{$term}%"));
            });
        }

        $direction = ($filters['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $direction);
    }
}
