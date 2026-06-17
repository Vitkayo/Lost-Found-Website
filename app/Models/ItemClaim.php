<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemClaim extends Model
{
    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'status',
        'dispute_status',
        'dispute_reason',
        'claimant_name',
        'contact_info',
        'message',
        'verification_answer',
        'proof_image_path',
        'reviewed_at',
        'reviewed_by',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function toDisplayArray(): array
    {
        $item = $this->item;
        $userId = auth()->id();
        $canViewPrivate = auth()->user()?->isAdmin()
            || ($userId && (
                (int) $userId === (int) $this->user_id
                || (int) $userId === (int) $item?->user_id
            ));

        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'type_label' => $this->type === 'claim' ? 'Claim' : 'Found Report',
            'type_class' => $this->type === 'claim' ? 'claim' : 'return',
            'status' => $this->status ?? 'pending',
            'status_label' => ucfirst($this->status ?? 'pending'),
            'status_class' => $this->status ?? 'pending',
            'dispute_status' => $this->dispute_status ?? 'none',
            'dispute_reason' => $canViewPrivate ? ($this->dispute_reason ?? '') : '',
            'claimant_name' => $this->claimant_name ?: 'Anonymous',
            'contact_info' => $canViewPrivate ? $this->contact_info : '',
            'message' => $canViewPrivate ? ($this->message ?? '') : '',
            'ownership_proof' => $canViewPrivate
                ? ($this->message ?? $this->verification_answer ?? '')
                : '',
            'verification_answer' => $canViewPrivate ? ($this->verification_answer ?? '') : '',
            'proof_image_url' => $canViewPrivate && $this->proof_image_path
                ? '/storage/'.$this->proof_image_path
                : '',
            'user_id' => $this->user_id ? (string) $this->user_id : null,
            'can_view_private' => (bool) $canViewPrivate,
            'can_review' => auth()->check() && $item && (int) auth()->id() === (int) $item->user_id,
            'created_at' => $this->created_at->toIso8601String(),
            'item' => $item ? $item->toLegacyArray() : null,
            'item_status' => $item?->status ?? 'unknown',
        ];
    }
}
