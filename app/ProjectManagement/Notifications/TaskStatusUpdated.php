<?php

namespace App\ProjectManagement\Notifications;

use App\ProjectManagement\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TaskStatusUpdated extends Notification
{
    use Queueable;

    protected $task;
    protected $originalStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $originalStatus)
    {
        $this->task = $task;
        $this->originalStatus = $originalStatus;
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
        $oldStatus = ucfirst(str_replace('_', ' ', $this->originalStatus));
        $newStatus = ucfirst(str_replace('_', ' ', $this->task->status));

        return (new MailMessage)
                    ->subject("Task Status Updated: {$this->task->name}")
                    ->line("The status of a task has been updated.")
                    ->line("Task: {$this->task->name}")
                    ->line("Status changed from '{$oldStatus}' to '{$newStatus}'.")
                    ->action('View Task', route('tasks.show', $this->task));
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $url = route('tasks.show', $this->task);
        $oldStatus = ucfirst(str_replace('_', ' ', $this->originalStatus));
        $newStatus = ucfirst(str_replace('_', ' ', $this->task->status));

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("🔄 *Task Status Updated*\n\nThe status of task *{$this->task->name}* was changed from *{$oldStatus}* to *{$newStatus}*.")
            ->button('View Task', $url);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $oldStatus = ucfirst(str_replace('_', ' ', $this->originalStatus));
        $newStatus = ucfirst(str_replace('_', ' ', $this->task->status));

        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name,
            'url' => route('tasks.show', $this->task),
            'message' => "Task status changed from '{$oldStatus}' to '{$newStatus}'.",
        ];
    }
}