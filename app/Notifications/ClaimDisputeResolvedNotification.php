<?php

namespace App\Notifications;

use App\Models\ItemClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimDisputeResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ItemClaim $claim) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->claim->dispute_status === 'resolved' ? 'reopened for review' : 'closed';

        return (new MailMessage)
            ->subject('Your Campus Found dispute was updated')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your dispute for "'.($this->claim->item?->title ?? 'a campus item').'" was '.$label.'.')
            ->line('Current claim status: '.ucfirst($this->claim->status ?? 'pending').'.')
            ->action('View my claims', route('account.show').'#my-claims');
    }
}
