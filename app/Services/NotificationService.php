<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Stock threshold for low stock notifications
     */
    const LOW_STOCK_THRESHOLD = 10;
    const CRITICAL_STOCK_THRESHOLD = 5;

    /**
     * Get notifications for a specific user role
     */
    public function getNotificationsForUser(string $userId, string $role, int $limit = 50, bool $unreadOnly = false): Collection
    {
        $query = Notification::forRole($role)
            ->with(['reads' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit);
            
        // For owners, exclude reservation-related notifications
        if ($role === 'owner') {
            $query->whereNotIn('type', ['new_reservation', 'reservation_expiring']);
        }
            
        if ($unreadOnly) {
            $query->unreadForUser($userId);
        }
        
        return $query->get()
            ->map(function ($notification) use ($userId) {
                $notification->is_read_by_user = $notification->isReadByUser($userId);
                $notification->read_at_by_user = $notification->getReadAtForUser($userId);
                return $notification;
            });
    }

    /**
     * Get unread notification count for a specific user
     */
    public function getUnreadCountForUser(string $userId, string $role): int
    {
        $query = Notification::forRole($role)
            ->unreadForUser($userId);
            
        // For owners, exclude reservation-related notifications
        if ($role === 'owner') {
            $query->whereNotIn('type', ['new_reservation', 'reservation_expiring']);
        }
        
        return $query->count();
    }

    /**
     * Mark notification as read by specific user
     */
    public function markAsReadByUser($notificationId, string $userId): bool
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsReadByUser($userId);
            return true;
        }
        return false;
    }

    /**
     * Mark all notifications as read for a specific user and role
     */
    public function markAllAsReadForUser(int $userId, string $role): int
    {
        $query = Notification::forRole($role)
            ->unreadForUser($userId);
            
        // For owners, exclude reservation-related notifications
        if ($role === 'owner') {
            $query->whereNotIn('type', ['new_reservation', 'reservation_expiring']);
        }
        
        $notifications = $query->get();

        $count = 0;
        foreach ($notifications as $notification) {
            $notification->markAsReadByUser($userId);
            $count++;
        }

        return $count;
    }

    /**
     * Mark all notifications as read for a specific user in a role
     */
    public function markAllAsReadByUser(string $userId, string $role): int
    {
        $query = Notification::forRole($role)->unreadForUser($userId);
        
        // For owners, exclude reservation-related notifications
        if ($role === 'owner') {
            $query->whereNotIn('type', ['new_reservation', 'reservation_expiring']);
        }
        
        $notifications = $query->get();
        $count = 0;

        foreach ($notifications as $notification) {
            if ($notification->markAsReadByUser($userId)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check for low stock products and create notifications
     */
    public function checkLowStock(): int
    {
        $lowStockItems = $this->getLowStockItems();
        $notificationsCreated = 0;

        foreach ($lowStockItems as $item) {
            // Check if we already have a recent notification for this specific product size
            $existingNotification = Notification::where('type', 'low_stock')
                ->where('data->product_id', $item['product_id'])
                ->where('data->size', $item['size'])
                ->where('created_at', '>=', Carbon::now()->subHours(24)) // Don't spam notifications
                ->first();

            if (!$existingNotification) {
                Notification::createLowStockNotification([
                    'product_id' => $item['product_id'],
                    'product_size_id' => $item['product_size_id'],
                    'name' => $item['product_name'],
                    'size' => $item['size'],
                    'stock' => $item['stock'],
                    'category' => $item['category']
                ]);
                $notificationsCreated++;
            }
        }

        return $notificationsCreated;
    }

    /**
     * Check for new reservations and create notifications
     */
    public function checkNewReservations(): int
    {
        // Get reservations created in the last hour that don't have notifications yet
        $newReservations = Reservation::where('created_at', '>=', Carbon::now()->subHour())
            ->whereNotIn('id', function ($query) {
                $query->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.reservation_id"))'))
                    ->from('notifications')
                    ->where('type', 'new_reservation')
                    ->whereRaw('JSON_EXTRACT(data, "$.reservation_id") IS NOT NULL');
            })
            ->get();

        $notificationsCreated = 0;

        foreach ($newReservations as $reservation) {
            Notification::createNewReservationNotification([
                'reservation_id' => $reservation->id,
                'customer_name' => $reservation->customer_name ?? 'Unknown Customer',
                'total_amount' => $reservation->total_amount ?? 0,
                'status' => $reservation->status,
                'created_at' => $reservation->created_at->toISOString()
            ]);
            $notificationsCreated++;
        }

        return $notificationsCreated;
    }

    /**
     * Check for expiring reservations
     */
    public function checkExpiringReservations(): int
    {
        // Get reservations that will expire in 24 hours
        $expiringReservations = Reservation::where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subDays(6)) // Assuming 7-day expiry
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->whereNotIn('id', function ($query) {
                $query->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(data, "$.reservation_id"))'))
                    ->from('notifications')
                    ->where('type', 'reservation_expiring')
                    ->where('created_at', '>=', Carbon::now()->subDay())
                    ->whereRaw('JSON_EXTRACT(data, "$.reservation_id") IS NOT NULL');
            })
            ->get();

        $notificationsCreated = 0;

        foreach ($expiringReservations as $reservation) {
            Notification::createReservationExpiringNotification([
                'reservation_id' => $reservation->id,
                'customer_name' => $reservation->customer_name ?? 'Unknown Customer',
                'expires_at' => Carbon::now()->addDay()->toISOString()
            ]);
            $notificationsCreated++;
        }

        return $notificationsCreated;
    }

    /**
     * Run all notification checks
     */
    public function runAllChecks(): array
    {
        return [
            'low_stock' => $this->checkLowStock(),
            'new_reservations' => $this->checkNewReservations(),
            'expiring_reservations' => $this->checkExpiringReservations()
        ];
    }

    /**
     * Clean up old notifications
     */
    public function cleanupOldNotifications(int $daysToKeep = 30): int
    {
        return Notification::where('created_at', '<', Carbon::now()->subDays($daysToKeep))
            ->delete();
    }

    /**
     * Get low stock items from database
     */
    private function getLowStockItems(): Collection
    {
        return DB::table('product_sizes as ps')
            ->join('products as p', 'p.id', '=', 'ps.product_id')
            ->where('ps.stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->where('ps.is_available', true)
            ->where('p.is_active', true)
            ->select([
                'ps.product_size_id',
                'ps.product_id',
                'p.name as product_name',
                'p.category',
                'ps.size',
                'ps.stock'
            ])
            ->orderBy('ps.stock')
            ->get();
    }

    /**
     * Get notification stats for dashboard
     */
    public function getNotificationStatsForUser(string $userId, string $role): array
    {
        $query = Notification::forRole($role);
        
        // For owners, exclude reservation-related notifications
        if ($role === 'owner') {
            $query->whereNotIn('type', ['new_reservation', 'reservation_expiring']);
        }

        return [
            'total' => $query->count(),
            'unread' => $query->unreadForUser($userId)->count(),
            'today' => $query->whereDate('created_at', Carbon::today())->count(),
            'this_week' => $query->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
            'by_type' => $query->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => $query->select('priority', DB::raw('COUNT(*) as count'))
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray()
        ];
    }

    /**
     * Create a custom notification
     */
    public function createCustomNotification(array $data): Notification
    {
        return Notification::create([
            'type' => $data['type'] ?? 'custom',
            'title' => $data['title'],
            'message' => $data['message'],
            'target_role' => $data['target_role'] ?? 'all',
            'data' => $data['data'] ?? null,
            'icon' => $data['icon'] ?? 'fas fa-info-circle',
            'priority' => $data['priority'] ?? 'normal'
        ]);
    }
}