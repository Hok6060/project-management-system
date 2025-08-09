<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Attachment;
use App\Models\Comment;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy; 
use App\Policies\AttachmentPolicy; 
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        Attachment::class => AttachmentPolicy::class,
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
