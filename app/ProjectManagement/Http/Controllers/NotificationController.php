<?php

namespace App\ProjectManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all notifications for the user, paginated
        $notifications = Auth::user()->notifications()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read and redirect.
     */
    public function markAsRead(DatabaseNotification $notification)
    {
        // Ensure the user owns the notification
        if (Auth::id() !== $notification->notifiable_id) {
            abort(403);
        }

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications have been marked as read.');
    }

     /**
     * Remove the specified notification from storage.
     */
    public function destroy(DatabaseNotification $notification)
    {
        // Ensure the user owns the notification before deleting
        if (Auth::id() !== $notification->notifiable_id) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notification deleted successfully.');
    }

    /**
     * Clear all read notifications for the user.
     */
    public function clearRead()
    {
        Auth::user()->readNotifications()->delete();

        return back()->with('success', 'All read notifications have been cleared.');
    }
}