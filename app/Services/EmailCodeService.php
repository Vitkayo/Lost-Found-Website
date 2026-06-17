<?php

namespace App\Services;

use App\Models\EmailCode;
use App\Models\User;
use App\Notifications\EmailCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailCodeService
{
    public const VERIFY_EMAIL = 'verify_email';

    public const PASSWORD_RESET = 'password_reset';

    public function send(User $user, string $purpose): void
    {
        $this->ensureCanSend($user->email, $purpose);

        $code = $this->makeCode();

        EmailCode::query()
            ->where('email', $user->email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        EmailCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'sent_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new EmailCodeNotification($code, $purpose));
    }

    public function sendPasswordReset(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        try {
            $this->send($user, self::PASSWORD_RESET);
        } catch (ValidationException) {
            return;
        }
    }

    public function verify(User $user, string $purpose, string $code): void
    {
        $record = EmailCode::query()
            ->where('user_id', $user->id)
            ->where('email', $user->email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (! $record || $record->expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'This code is invalid or expired.']);
        }

        if ($record->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'Too many attempts. Please request a new code.']);
        }

        $record->increment('attempts');

        if (! Hash::check($code, $record->code_hash)) {
            throw ValidationException::withMessages(['code' => 'This code is invalid or expired.']);
        }

        $record->update(['consumed_at' => now()]);
    }

    private function ensureCanSend(string $email, string $purpose): void
    {
        $latest = EmailCode::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->latest('sent_at')
            ->first();

        if ($latest?->sent_at && $latest->sent_at->gt(now()->subSeconds(60))) {
            throw ValidationException::withMessages([
                'email' => 'Please wait before requesting another code.',
            ]);
        }
    }

    private function makeCode(): string
    {
        return (string) random_int(100000, 999999);
    }
}
