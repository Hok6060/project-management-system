<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $view->with('unreadNotifications', $user->unreadNotifications()->take(5)->get());
            $view->with('unreadNotificationsCount', $user->unreadNotifications()->count());
        } else {
            $view->with('unreadNotifications', collect());
            $view->with('unreadNotificationsCount', 0);
        }
    }
}
