<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemClaim extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'status',
        'claimant_name',
        'contact_info',
        'message',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function toDisplayArray(): array
    {
        $item = $this->item;

        return [
            'id' => (string) $this->id,
            'type' => $this->type,
            'type_label' => $this->type === 'claim' ? 'Claim' : 'Return',
            'type_class' => $this->type === 'claim' ? 'claim' : 'return',
            'status' => $this->status ?? 'pending',
            'status_label' => ucfirst($this->status ?? 'pending'),
            'status_class' => $this->status ?? 'pending',
            'claimant_name' => $this->claimant_name ?: 'Anonymous',
            'contact_info' => $this->contact_info,
            'message' => $this->message ?? '',
            'created_at' => $this->created_at->toIso8601String(),
            'item' => $item ? $item->toLegacyArray() : null,
            'item_status' => $item?->status ?? 'unknown',
        ];
    }
}
