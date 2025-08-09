<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ProjectStatusUpdated extends Notification
{
    use Queueable;

    protected $project;
    protected $originalStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Project $project, string $originalStatus)
    {
        $this->project = $project;
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
        $newStatus = ucfirst(str_replace('_', ' ', $this->project->status));

        return (new MailMessage)
                    ->subject("Project Status Updated: {$this->project->name}")
                    ->line("The status of a project you are a part of has been updated.")
                    ->line("Project: {$this->project->name}")
                    ->line("Status changed from '{$oldStatus}' to '{$newStatus}'.")
                    ->action('View Project', route('projects.show', $this->project));
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $url = route('projects.show', $this->project);
        $oldStatus = ucfirst(str_replace('_', ' ', $this->originalStatus));
        $newStatus = ucfirst(str_replace('_', ' ', $this->project->status));

        return TelegramMessage::create()
            ->to($notifiable->telegram_chat_id)
            ->content("🔄 *Project Status Updated*\n\nThe status of project *{$this->project->name}* was changed from *{$oldStatus}* to *{$newStatus}*.")
            ->button('View Project', $url);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $oldStatus = ucfirst(str_replace('_', ' ', $this->originalStatus));
        $newStatus = ucfirst(str_replace('_', ' ', $this->project->status));

        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'url' => route('projects.show', $this->project),
            'message' => "Project status changed from '{$oldStatus}' to '{$newStatus}'.",
        ];
    }
}