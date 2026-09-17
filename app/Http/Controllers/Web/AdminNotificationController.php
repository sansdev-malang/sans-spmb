<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminNotificationController extends Controller
{
    /**
     * Get base query for notifications scoped by unit admin.
     */
    protected function getNotificationsQuery($onlyUnread = true)
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

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
        try {
            $user = auth()->user();
            if (!$user) {
                return response('', 200)->header('Content-Type', 'text/html');
            }

            $query = $this->getNotificationsQuery(true);
            if (!$query) {
                return response('', 200)->header('Content-Type', 'text/html');
            }

            $count = $query->count();
            if ($count > 0) {
                return response('<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>' .
                       '<span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>', 200)
                       ->header('Content-Type', 'text/html');
            }

            return response('', 200)->header('Content-Type', 'text/html');
        } catch (\Throwable $e) {
            Log::debug('Notification unread count error handled: ' . $e->getMessage());
            return response('', 200)->header('Content-Type', 'text/html');
        }
    }

    /**
     * Get the HTML content of the notification dropdown list.
     */
    public function dropdownList()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response('<div class="p-4 text-xs text-slate-400 text-center">Silakan login kembali</div>', 200);
            }

            $query = $this->getNotificationsQuery(true);
            $notifications = $query ? $query->take(10)->get() : collect();
            $unreadCount = $query ? $query->count() : 0;

            return view('admin.partials.notification-dropdown', compact('notifications', 'unreadCount'));
        } catch (\Throwable $e) {
            Log::debug('Notification dropdown error handled: ' . $e->getMessage());
            return response('<div class="p-4 text-xs text-slate-400 text-center">Tidak ada notifikasi baru</div>', 200);
        }
    }

    /**
     * Mark all notifications of the authenticated admin as read.
     */
    public function markAllRead()
    {
        try {
            $user = auth()->user();
            if ($user) {
                $query = $this->getNotificationsQuery(true);
                if ($query) {
                    $query->get()->markAsRead();
                }
            }

            return response(view('admin.partials.notification-dropdown', [
                'notifications' => collect(),
                'unreadCount' => 0
            ])->render())
            ->header('HX-Trigger', 'refresh-notification-count');
        } catch (\Throwable $e) {
            return response('', 200);
        }
    }

    /**
     * Mark specific notification as read and redirect.
     */
    public function markAsReadAndRedirect($id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('admin.dashboard');
        }

        $notification = $user->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            $url = $notification->data['url'] ?? route('admin.dashboard');
            return redirect($url);
        }

        return redirect()->route('admin.dashboard');
    }
}
