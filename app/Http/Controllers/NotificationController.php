<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 15);
        $type = $request->get('type');
        $read = $request->get('read');

        $query = $user->notifications();

        if ($type) {
            $query->ofType($type);
        }

        if ($read !== null) {
            if ($read) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->items(),
                'pagination' => [
                    'currentPage' => $notifications->currentPage(),
                    'lastPage' => $notifications->lastPage(),
                    'perPage' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'unreadCount' => $user->getUnreadNotificationsCount(),
            ],
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Broadcast the notification read event
        broadcast(new \App\Events\NotificationRead($notification, $user));

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->markAllNotificationsAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notificationId = $notification->id;
        $notification->delete();

        // Broadcast the notification deleted event
        broadcast(new \App\Events\NotificationDeleted($notificationId, $user));

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = $user->getUnreadNotificationsCount();

        return response()->json([
            'success' => true,
            'data' => [
                'unreadCount' => $count,
            ],
        ]);
    }

    public function getNotificationTypes()
    {
        $types = collect(\App\Enums\NotificationType::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $this->notificationService->getNotificationTitle($type),
                'icon' => $this->notificationService->getNotificationIcon($type),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
