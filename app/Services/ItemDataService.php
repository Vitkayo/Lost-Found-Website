<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemDataService
{
    public function all(): array
    {
        return $this->openItemsQuery()
            ->orderByDesc('reported_at')
            ->get()
            ->map(fn (Item $item) => $item->toLegacyArray())
            ->all();
    }

    public function find(string $id): ?array
    {
        $item = Item::find($id);

        return $item?->toLegacyArray();
    }

    public function recent(int $limit = 4): array
    {
        return $this->openItemsQuery()
            ->orderByDesc('reported_at')
            ->limit($limit)
            ->get()
            ->map(fn (Item $item) => $item->toLegacyArray())
            ->all();
    }

    public function categoryCounts(): array
    {
        return $this->openItemsQuery()
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->all();
    }

    public function stats(): array
    {
        $reported = $this->openItemsQuery()->count();
        $found = $this->openItemsQuery()->where('status', 'found')->count();
        $lost = $this->openItemsQuery()->where('status', 'lost')->count();

        return [
            'reported' => $reported,
            'found' => $found,
            'lost' => $lost,
            'active' => $reported,
        ];
    }

    public function filtered(array $filters = []): array
    {
        $query = ! empty($filters['include_claimed'])
            ? Item::query()
            : $this->openItemsQuery();

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $matchingCategories = collect(config('lostfound.categories'))
                ->filter(function (string $label, string $slug) use ($term) {
                    $normalizedTerm = strtolower($term);
                    $aliases = config("lostfound.category_search_aliases.{$slug}", []);

                    return str_contains(strtolower($label), $normalizedTerm)
                        || str_contains(strtolower(str_replace('_', ' ', $slug)), $normalizedTerm)
                        || collect($aliases)->contains(fn (string $alias) => str_contains($alias, $normalizedTerm));
                })
                ->keys()
                ->all();

            $query->where(function ($q) use ($term, $matchingCategories) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");

                if ($matchingCategories) {
                    $q->orWhereIn('category', $matchingCategories);
                }
            });
        }

        if (! empty($filters['date'])) {
            $query->whereDate('reported_at', $filters['date']);
        }

        $direction = ($filters['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy('reported_at', $direction)
            ->get()
            ->map(fn (Item $item) => $item->toLegacyArray())
            ->all();
    }

    public function create(array $data, ?UploadedFile $image = null): array
    {
        [$imageUrl, $imagePath] = $this->storeImage($image);

        $item = Item::create([
            'title' => $data['title'],
            'status' => $data['status'],
            'category' => $data['category'] ?? 'other',
            'reported_at' => Carbon::parse($data['created_at']),
            'location' => $data['location'],
            'contact_info' => $data['contact_info'],
            'description' => $data['description'] ?? '',
            'image_url' => $imageUrl,
            'image_path' => $imagePath,
        ]);

        return $item->toLegacyArray();
    }

    public function delete(string $id): bool
    {
        $item = Item::find($id);
        if (! $item) {
            return false;
        }

        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return true;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function storeImage(?UploadedFile $image): array
    {
        if (! $image) {
            return ['', ''];
        }

        $path = $image->store('items', 'public');

        return ['/storage/'.$path, $path];
    }

    private function openItemsQuery()
    {
        return Item::query()->whereDoesntHave('claims');
    }
}
