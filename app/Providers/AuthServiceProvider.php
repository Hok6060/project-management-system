<?php

namespace App\Providers;

use App\ProjectManagement\Models\Project;
use App\ProjectManagement\Models\Task;
use App\ProjectManagement\Models\Attachment;
use App\ProjectManagement\Models\Comment;
use App\ProjectManagement\Policies\ProjectPolicy;
use App\ProjectManagement\Policies\TaskPolicy; 
use App\ProjectManagement\Policies\AttachmentPolicy; 
use App\ProjectManagement\Policies\CommentPolicy;
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
