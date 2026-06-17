<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'status',
        'moderation_status',
        'moderation_reason',
        'category',
        'location',
        'contact_info',
        'description',
        'verification_question',
        'verification_answer_hash',
        'hidden_details',
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

        return '/storage/'.$this->image_path;
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ItemClaim::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toLegacyArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'moderation_status' => $this->moderation_status ?? 'active',
            'moderation_reason' => $this->moderation_reason ?? '',
            'category' => $this->category ?? 'other',
            'category_label' => config('lostfound.categories')[$this->category ?? 'other'] ?? 'Other',
            'created_at' => $this->reported_at?->toIso8601String() ?? $this->created_at->toIso8601String(),
            'location' => $this->location,
            'contact_info' => $this->canManageCurrentUser() ? $this->contact_info : '',
            'description' => $this->description ?? '',
            'verification_question' => $this->verification_question ?? '',
            'has_verification' => filled($this->verification_question),
            'user_id' => $this->user_id ? (string) $this->user_id : null,
            'can_manage' => $this->canManageCurrentUser(),
            'hidden_details' => $this->canManageCurrentUser()
                ? ($this->hidden_details ?? '')
                : '',
            'image_url' => $this->displayImageUrl(),
            'has_image' => $this->hasDisplayImage(),
            'image_path' => $this->image_path ?? '',
        ];
    }

    private function canManageCurrentUser(): bool
    {
        return auth()->check()
            && (
                auth()->user()?->isAdmin()
                || (int) auth()->id() === (int) $this->user_id
            );
    }
}
