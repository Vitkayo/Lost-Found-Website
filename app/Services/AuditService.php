<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function record(string $action, ?Model $subject = null, ?string $details = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'actor' => auth()->user()?->email ?? 'system',
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'details' => $details,
        ]);
    }
}
