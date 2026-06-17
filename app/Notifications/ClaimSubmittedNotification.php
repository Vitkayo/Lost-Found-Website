<?php

namespace App\Notifications;

use App\Models\ItemClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ItemClaim $claim) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New response to your Campus Found report')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Someone responded to your report: '.$this->claim->item->title)
            ->line('Sign in to review their private proof and approve or reject the claim.')
            ->action('Review claim', route('claims.index', ['type' => $this->claim->type]))
            ->line('Claimant contact details and proof remain private.');
    }
}
