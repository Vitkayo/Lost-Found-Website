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
        'claimant_name',
        'contact_info',
        'message',
        'verification_answer',
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

        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'type_label' => $this->type === 'claim' ? 'Claim' : 'Found Report',
            'type_class' => $this->type === 'claim' ? 'claim' : 'return',
            'status' => $this->status ?? 'pending',
            'status_label' => ucfirst($this->status ?? 'pending'),
            'status_class' => $this->status ?? 'pending',
            'claimant_name' => $this->claimant_name ?: 'Anonymous',
            'contact_info' => $this->contact_info,
            'message' => $this->message ?? '',
            'verification_answer' => $this->verification_answer ?? '',
            'user_id' => $this->user_id ? (string) $this->user_id : null,
            'can_review' => auth()->check() && $item && (int) auth()->id() === (int) $item->user_id,
            'created_at' => $this->created_at->toIso8601String(),
            'item' => $item ? $item->toLegacyArray() : null,
            'item_status' => $item?->status ?? 'unknown',
        ];
    }
}
