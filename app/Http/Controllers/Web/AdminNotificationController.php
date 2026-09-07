<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Get base query for notifications scoped by unit admin.
     */
    protected function getNotificationsQuery($onlyUnread = true)
    {
        $user = auth()->user();
        $query = $onlyUnread ? $user->unreadNotifications() : $user->notifications();

        if ($user->isUnitAdmin() && $user->spmb_unit_id) {
            $unitId = (int) $user->spmb_unit_id;
            $query->where(function($q) use ($unitId) {
                $q->where('data->spmb_unit_id', $unitId)
                  ->orWhere('data->spmb_unit_id', (string) $unitId);
            });
        }

        return $query;
    }

    /**
     * Get the unread notification badge (HTML).
     */
    public function unreadCount()
    {
        $count = $this->getNotificationsQuery(true)->count();
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
        $notifications = $this->getNotificationsQuery(true)->take(10)->get();
        $unreadCount = $this->getNotificationsQuery(true)->count();

        return view('admin.partials.notification-dropdown', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark all notifications of the authenticated admin as read.
     */
    public function markAllRead()
    {
        $this->getNotificationsQuery(true)->get()->markAsRead();

        // Return updated dropdown view and trigger event to refresh the badge count
        return response(view('admin.partials.notification-dropdown', [
            'notifications' => $this->getNotificationsQuery(true)->take(10)->get(),
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
