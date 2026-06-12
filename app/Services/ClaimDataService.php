<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemClaim;

class ClaimDataService
{
    public function create(Item $item, array $data, ?int $userId = null): array
    {
        $type = $item->status === 'found' ? 'claim' : 'found';

        $claim = ItemClaim::create([
            'item_id' => $item->id,
            'user_id' => $userId,
            'type' => $type,
            'status' => 'pending',
            'claimant_name' => $data['claimant_name'] ?? null,
            'contact_info' => $data['contact_info'],
            'message' => $data['message'] ?? null,
            'verification_answer' => $data['verification_answer'] ?? null,
        ]);

        $claim->load('item');

        return $claim->toDisplayArray();
    }

    public function review(ItemClaim $claim, string $status, int $reviewerId): array
    {
        $claim->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
        ]);

        return $claim->fresh('item')->toDisplayArray();
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
        $query = ItemClaim::query()->with('item');

        if (! session('is_admin')) {
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

        return $query
            ->orderBy('created_at', $direction)
            ->get()
            ->map(fn (ItemClaim $claim) => $claim->toDisplayArray())
            ->all();
    }

    public function delete(string $id): bool
    {
        $claim = ItemClaim::find($id);
        if (! $claim) {
            return false;
        }

        $claim->delete();

        return true;
    }
}
