<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class NewComment extends Notification
{
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        $channels[] = 'database'; // Always send in-app

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
                    ->subject("New Comment on Task: {$this->comment->task->name}")
                    ->line("{$this->comment->user->name} posted a new comment.")
                    ->line("\"{$this->comment->body}\"")
                    ->action('View Task', route('tasks.show', $this->comment->task))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $url = route('tasks.show', $this->comment->task);

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("💬 *New Comment*\n\n*{$this->comment->user->name}* commented on task: *{$this->comment->task->name}*")
            ->line("\"{$this->comment->body}\"")
            ->button('View Task', $url);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->comment->task->id,
            'task_name' => $this->comment->task->name,
            'project_name' => $this->comment->task->project->name,
            'url' => route('tasks.show', $this->comment->task),
            'message' => "{$this->comment->user->name} commented on a task.",
        ];
    }
}