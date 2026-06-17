<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportModeratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Item $item) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hidden = ($this->item->moderation_status ?? 'active') === 'hidden';

        return (new MailMessage)
            ->subject($hidden ? 'Your Campus Found report was hidden' : 'Your Campus Found report is visible again')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your report "'.$this->item->title.'" has been updated by the Campus Found team.')
            ->line($hidden
                ? 'The report is temporarily hidden from the public board.'
                : 'The report has been restored to the public board.')
            ->when(filled($this->item->moderation_reason), fn (MailMessage $mail) => $mail->line('Reason: '.$this->item->moderation_reason))
            ->action('View my reports', route('account.show').'#my-reports');
    }
}
