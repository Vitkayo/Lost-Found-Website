<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $purpose,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isReset = $this->purpose === 'password_reset';

        return (new MailMessage)
            ->subject($isReset ? 'Reset your Campus Found password' : 'Verify your Campus Found email')
            ->greeting('Campus Found')
            ->line($isReset ? 'Use this code to reset your password.' : 'Use this code to verify your email address.')
            ->line($this->code)
            ->line('This code expires in 10 minutes.')
            ->line('If you did not request this code, you can ignore this email.');
    }
}
