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
            return '<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>' .
                   '<span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>';
        }
        return '';
    }

    /**
     * Get the HTML content of the notification dropdown list.
     */
    public function dropdownList()
    {
        $notifications = auth()->user()->unreadNotifications()->take(10)->get();
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
            'notifications' => auth()->user()->unreadNotifications()->take(10)->get(),
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
