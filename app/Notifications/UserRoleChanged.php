<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class UserRoleChanged extends Notification
{
    use Queueable;

    protected $originalRole;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $originalRole)
    {
        $this->originalRole = $originalRole;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->notify_by_email) {
            $channels[] = 'mail';
        }

        if ($notifiable->notify_by_telegram && $notifiable->telegram_chat_id) {
            $channels[] = 'telegram';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $oldRole = ucfirst(str_replace('_', ' ', $this->originalRole));
        $newRole = ucfirst(str_replace('_', ' ', $notifiable->role));

        return (new MailMessage)
                    ->subject('Your User Role Has Been Updated')
                    ->line("An administrator has updated your role in the system.")
                    ->line("Your role has been changed from '{$oldRole}' to '{$newRole}'.")
                    ->action('View Dashboard', route('dashboard'));
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $oldRole = ucfirst(str_replace('_', ' ', $this->originalRole));
        $newRole = ucfirst(str_replace('_', ' ', $notifiable->role));

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("👤 *Your Role Was Changed*\n\nAn admin has changed your role from *{$oldRole}* to *{$newRole}*.");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $oldRole = ucfirst(str_replace('_', ' ', $this->originalRole));
        $newRole = ucfirst(str_replace('_', ' ', $notifiable->role));

        return [
            'url' => route('profile.edit'),
            'message' => "Your role was changed from '{$oldRole}' to '{$newRole}'.",
        ];
    }
}