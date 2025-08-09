<?php

namespace App\Notifications;

use App\Models\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewAttachment extends Notification
{
    use Queueable;

    protected $attachment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
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
        return (new MailMessage)
                    ->subject("New File Attached: {$this->attachment->task->name}")
                    ->line("{$this->attachment->user->name} attached a new file to a task.")
                    ->line("File: {$this->attachment->file_name}")
                    ->action('View Task', route('tasks.show', $this->attachment->task));
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $url = route('tasks.show', $this->attachment->task);

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("📎 *New File Attached*\n\n*{$this->attachment->user->name}* attached *{$this->attachment->file_name}* to task: *{$this->attachment->task->name}*")
            ->button('View Task', $url);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->attachment->task->id,
            'task_name' => $this->attachment->task->name,
            'project_name' => $this->attachment->task->project->name,
            'url' => route('tasks.show', $this->attachment->task),
            'message' => "{$this->attachment->user->name} attached a file.",
        ];
    }
}