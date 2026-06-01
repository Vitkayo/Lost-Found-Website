<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $fillable = [
        'title',
        'status',
        'category',
        'location',
        'contact_info',
        'description',
        'image_url',
        'image_path',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    public function hasDisplayImage(): bool
    {
        if (! $this->image_path) {
            return false;
        }

        return Storage::disk('public')->exists($this->image_path);
    }

    public function displayImageUrl(): string
    {
        if (! $this->hasDisplayImage()) {
            return '';
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ItemClaim::class);
    }

    public function toLegacyArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'category' => $this->category ?? 'other',
            'category_label' => config('lostfound.categories')[$this->category ?? 'other'] ?? 'Other',
            'created_at' => $this->reported_at?->toIso8601String() ?? $this->created_at->toIso8601String(),
            'location' => $this->location,
            'contact_info' => $this->contact_info,
            'description' => $this->description ?? '',
            'image_url' => $this->displayImageUrl(),
            'has_image' => $this->hasDisplayImage(),
            'image_path' => $this->image_path ?? '',
        ];
    }
}
