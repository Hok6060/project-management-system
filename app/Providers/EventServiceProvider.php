<?php

namespace App\Providers;

use App\ProjectManagement\Models\Task;
use App\ProjectManagement\Models\Project;
use App\ProjectManagement\Models\Comment;
use App\ProjectManagement\Models\Attachment;
use App\ProjectManagement\Observers\TaskObserver;
use App\ProjectManagement\Observers\ProjectObserver;
use App\ProjectManagement\Observers\CommentObserver;
use App\ProjectManagement\Observers\AttachmentObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        // This is the correct and only place to register the observer.
        Task::class => [TaskObserver::class],
        Project::class => [ProjectObserver::class],
        Comment::class => [CommentObserver::class],
        Attachment::class => [AttachmentObserver::class],
    ];

    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // We no longer need to manually register the observer here.
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
