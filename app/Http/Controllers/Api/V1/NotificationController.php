<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use App\Services\LeysNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(LeysNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user.
     * GET /api/notifications
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $query = $user->customNotifications()->with('user');
        
        // Filter by read status
        if ($request->has('is_read')) {
            $isRead = filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN);
            $query = $isRead 
                ? $query->read()
                : $query->unread();
        }
        
        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        // Date range filtering
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }
        
        // Order by latest first
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);
        
        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'last_page' => $notifications->lastPage(),
            ],
            'message' => 'Notifications retrieved successfully.'
        ]);
    }

    /**
     * Mark a notification as read.
     * PUT /api/notifications/{id}/read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Authorization check - user can only mark their own notifications as read
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this notification.'
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($notification),
            'message' => 'Notification marked as read.'
        ]);
    }

    /**
     * Mark all notifications as read.
     * PUT /api/notifications/read-all
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'data' => ['marked_count' => $count],
            'message' => "{$count} notifications marked as read."
        ]);
    }

    /**
     * Delete a notification.
     * DELETE /api/notifications/{id}
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Authorization check
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this notification.'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.'
        ], 200);
    }

    /**
     * Get unread notifications count.
     * GET /api/notifications/unread-count
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
            'message' => 'Unread count retrieved successfully.'
        ]);
    }
}