<?php

namespace App\ProjectManagement\Notifications;

use App\ProjectManagement\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TaskAssigned extends Notification
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = []; // Start with an empty array

        // Always add the database channel for in-app notifications
        $channels[] = 'database';

        // Check the user's preference for email
        if ($notifiable->notify_by_email) {
            $channels[] = 'mail';
        }

        // Check the user's preference for Telegram and if they have a chat ID
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
                    ->subject('New Task Assignment')
                    ->line('You have been assigned a new task.')
                    ->line("Task: {$this->task->name}")
                    ->line("Project: {$this->task->project->name}")
                    ->action('View Task', route('tasks.show', $this->task))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable): TelegramMessage
    {
        $url = route('tasks.show', $this->task);

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("🔔 *New Task Assignment*\n\nYou have been assigned a new task.\n")
            ->line("*Task:* {$this->task->name}")
            ->line("*Project:* {$this->task->project->name}")
            ->button('View Task', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name,
            'url' => route('tasks.show', $this->task),
            'message' => 'You have been assigned a new task.',
        ];
    }
}
