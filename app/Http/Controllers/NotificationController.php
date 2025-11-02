<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\NotificationService;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Map user role to notification role and get user ID
     */
    private function mapUserRole(string $userRole): string
    {
        return match($userRole) {
            'admin' => 'owner',
            'owner', 'manager', 'cashier' => $userRole,
            default => 'cashier'
        };
    }

    /**
     * Get the user ID from the authenticated user
     */
    private function getUserId($user): string
    {
        return $user->user_id ?? $user->id ?? '';
    }

    /**
     * Get notifications for the current user's role
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $role = $this->mapUserRole($user->role ?? 'cashier');
        $userId = $this->getUserId($user);
        $limit = $request->get('limit', 50);
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getNotificationsForUser($userId, $role, $limit, $unreadOnly);

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) use ($userId) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'icon' => $notification->icon,
                    'priority' => $notification->priority,
                    'is_read' => $notification->isReadByUser($userId),
                    'created_at' => $notification->created_at->format('M j, Y g:i A'),
                    'created_at_human' => $notification->created_at->diffForHumans(),
                    'data' => $notification->data
                ];
            }),
            'unread_count' => $this->notificationService->getUnreadCountForUser($userId, $role)
        ]);
    }

    /**
     * Get unread notification count for the current user's role
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $role = $this->mapUserRole($user->role ?? 'cashier');
        $userId = $this->getUserId($user);
        $count = $this->notificationService->getUnreadCountForUser($userId, $role);

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $success = $this->notificationService->markAsReadByUser($id, $this->getUserId($user));

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark all notifications as read for the current user's role
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $role = $this->mapUserRole($user->role ?? 'cashier');
        $userId = $this->getUserId($user);
        $markedCount = $this->notificationService->markAllAsReadByUser($userId, $role);

        return response()->json([
            'success' => true,
            'message' => "Marked {$markedCount} notifications as read"
        ]);
    }

    /**
     * Get notification statistics for the current user's role
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $role = $this->mapUserRole($user->role ?? 'cashier');
        $userId = $this->getUserId($user);
        $stats = $this->notificationService->getNotificationStatsForUser($userId, $role);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Manually trigger notification checks (admin only)
     */
    public function triggerChecks(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $results = $this->notificationService->runAllChecks();

        return response()->json([
            'success' => true,
            'message' => 'Notification checks completed',
            'results' => $results
        ]);
    }

    /**
     * Create a custom notification (admin only)
     */
    public function create(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['owner', 'manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'target_role' => 'required|in:owner,manager,cashier,all',
            'type' => 'sometimes|string|max:50',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'icon' => 'sometimes|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = $this->notificationService->createCustomNotification($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully',
            'notification' => $notification
        ], 201);
    }

    /**
     * Delete a notification (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['owner', 'manager'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification = Notification::find($id);
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Clean up old notifications (admin only)
     */
    public function cleanup(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deletedCount = $this->notificationService->cleanupOldNotifications();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deletedCount} old notifications"
        ]);
    }
}
