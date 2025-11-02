<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ProcessNotificationChecks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Job constructor - no parameters needed
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        Log::info('Starting notification checks job');

        try {
            $results = $notificationService->runAllChecks();
            
            $totalCreated = array_sum($results);
            
            if ($totalCreated > 0) {
                Log::info('Notification checks completed', [
                    'low_stock_notifications' => $results['low_stock'],
                    'new_reservation_notifications' => $results['new_reservations'],
                    'expiring_reservation_notifications' => $results['expiring_reservations'],
                    'total_created' => $totalCreated
                ]);
            } else {
                Log::debug('Notification checks completed - no new notifications created');
            }

            // Clean up old notifications (only if total notifications exceed 1000)
            $totalNotifications = \App\Models\Notification::count();
            if ($totalNotifications > 1000) {
                $deletedCount = $notificationService->cleanupOldNotifications(30);
                if ($deletedCount > 0) {
                    Log::info("Cleaned up {$deletedCount} old notifications");
                }
            }

        } catch (\Exception $e) {
            Log::error('Notification checks job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw the exception so the job system can handle retries
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Notification checks job failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
