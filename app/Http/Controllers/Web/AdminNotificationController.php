<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Get the unread notification badge (HTML).
     */
    public function unreadCount()
    {
        $count = auth()->user()->unreadNotifications()->count();
        if ($count > 0) {
            return '<span id="unread-notifications-badge" class="absolute top-1.5 right-1.5 h-2.5 w-2.5 bg-red-500 rounded-full animate-pulse pointer-events-none"></span>';
        }
        return '<span id="unread-notifications-badge" class="hidden"></span>';
    }

    /**
     * Get the HTML content of the notification dropdown list.
     */
    public function dropdownList()
    {
        $notifications = auth()->user()->notifications()->take(10)->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('admin.partials.notification-dropdown', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark all notifications of the authenticated admin as read.
     */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        // Return updated dropdown view and trigger event to refresh the badge count
        return response(view('admin.partials.notification-dropdown', [
            'notifications' => auth()->user()->notifications()->take(10)->get(),
            'unreadCount' => 0
        ])->render())
        ->header('HX-Trigger', 'refresh-notification-count');
    }

    /**
     * Mark specific notification as read and redirect.
     */
    public function markAsReadAndRedirect($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('admin.dashboard');
        return redirect($url);
    }
}
