<?php

namespace App\Notifications;

use App\Models\Master\MasterInbox;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class InboxMasuk extends Notification
{
    use Queueable;

    public function __construct(public MasterInbox $inbox) {}

    /**
     * Channel yang digunakan: web push
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Isi pesan Web Push Notification
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('📬 Pesan Baru Masuk!')
            ->icon('/assets/img/favicon/favicon-96x96.png')
            ->body('Dari: ' . $this->inbox->name . ' — ' . $this->inbox->subject)
            ->action('Lihat Inbox', 'view_inbox')
            ->data(['url' => url('/inbox/' . $this->inbox->id)])
            ->badge('/assets/img/favicon/favicon-32x32.png');
    }
}
