<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ItemDataService
{
    public function __construct(private readonly ImageOptimizationService $images) {}

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
        return $this->filteredQuery($filters)
            ->get()
            ->map(fn (Item $item) => $item->toLegacyArray())
            ->all();
    }

    public function paginated(array $filters = [], int $perPage = 12, string $pageName = 'page'): LengthAwarePaginator
    {
        $paginator = $this->filteredQuery($filters)->paginate($perPage, ['*'], $pageName);
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Item $item) => $item->toLegacyArray())
        );

        return $paginator;
    }

    public function create(array $data, ?UploadedFile $image = null, ?int $userId = null): array
    {
        [$imageUrl, $imagePath] = $this->images->store($image);

        $item = Item::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'status' => $data['status'],
            'category' => $data['category'] ?? 'other',
            'reported_at' => Carbon::parse($data['created_at']),
            'location' => $data['location'],
            'contact_info' => $data['contact_info'],
            'description' => $data['description'] ?? '',
            'verification_question' => $data['verification_question'] ?? null,
            'verification_answer_hash' => filled($data['verification_answer'] ?? null)
                ? Hash::make(mb_strtolower(trim($data['verification_answer'])))
                : null,
            'hidden_details' => $data['hidden_details'] ?? null,
            'image_url' => $imageUrl,
            'image_path' => $imagePath,
        ]);

        return $item->toLegacyArray();
    }

    public function update(Item $item, array $data, ?UploadedFile $image = null): array
    {
        $values = [
            'title' => $data['title'],
            'status' => $data['status'],
            'category' => $data['category'],
            'reported_at' => Carbon::parse($data['created_at']),
            'location' => $data['location'],
            'contact_info' => $data['contact_info'],
            'description' => $data['description'] ?? '',
            'verification_question' => $data['verification_question'] ?? null,
            'hidden_details' => $data['hidden_details'] ?? null,
        ];

        if (filled($data['verification_answer'] ?? null)) {
            $values['verification_answer_hash'] = Hash::make(mb_strtolower(trim($data['verification_answer'])));
        }

        if ($image) {
            [$values['image_url'], $values['image_path']] = $this->images->store($image);
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
        }

        $item->update($values);

        return $item->fresh()->toLegacyArray();
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

        $item->claims()
            ->whereNotNull('proof_image_path')
            ->pluck('proof_image_path')
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        $item->delete();

        return true;
    }

    private function openItemsQuery()
    {
        return Item::query()
            ->where('moderation_status', 'active')
            ->whereDoesntHave(
                'claims',
                fn ($query) => $query->where('status', 'approved')
            );
    }

    private function filteredQuery(array $filters = [])
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

        return $query->orderBy('reported_at', $direction);
    }
}
