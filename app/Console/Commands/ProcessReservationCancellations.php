<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\ProductSize;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class ProcessReservationCancellations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:process-cancellations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic reservation cancellations based on pickup date and time rules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting reservation cancellation processing...');
        Log::info('Processing reservation cancellations');

        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        
        // Define business rules
        $cancelationCutoffTime = '19:00:00'; // 7 PM
        
        $processedCount = 0;
        $cancelledCount = 0;
        $markedForCancellationCount = 0;

        try {
            // Rule 1: Cancel reservations where pickup_date is before today
            $expiredReservations = Reservation::where('status', 'pending')
                ->where('pickup_date', '<', $today)
                ->get();

            foreach ($expiredReservations as $reservation) {
                $this->cancelReservation($reservation, 'Pickup date exceeded');
                $cancelledCount++;
                $processedCount++;
            }

            // Rule 2: For today's reservations - mark for cancellation if pickup time has passed
            $todayReservations = Reservation::where('status', 'pending')
                ->where('pickup_date', $today)
                ->where('pickup_time', '<', $currentTime)
                ->get();

            foreach ($todayReservations as $reservation) {
                $this->markForCancellation($reservation, 'Pickup time exceeded');
                $markedForCancellationCount++;
                $processedCount++;
            }

            // Rule 3: Cancel reservations that have been marked for cancellation from previous day
            $markedForCancellation = Reservation::where('status', 'for_cancellation')
                ->where('pickup_date', '<', $today)
                ->get();

            foreach ($markedForCancellation as $reservation) {
                $this->cancelReservation($reservation, 'Previously marked for cancellation and day has passed');
                $cancelledCount++;
                $processedCount++;
            }

            // Log results
            $message = "Reservation cancellation processing completed. Processed: {$processedCount}, Cancelled: {$cancelledCount}, Marked for cancellation: {$markedForCancellationCount}";
            
            Log::info($message, [
                'processed_count' => $processedCount,
                'cancelled_count' => $cancelledCount,
                'marked_for_cancellation_count' => $markedForCancellationCount,
                'current_time' => $now->toDateTimeString(),
                'cancellation_cutoff_time' => $cancelationCutoffTime
            ]);

            $this->info($message);

            // Create notifications for cancelled reservations if any
            if ($cancelledCount > 0 || $markedForCancellationCount > 0) {
                $this->createCancellationNotifications($cancelledCount, $markedForCancellationCount);
            }

        } catch (\Exception $e) {
            $errorMessage = 'Error processing reservation cancellations: ' . $e->getMessage();
            Log::error($errorMessage, [
                'exception' => $e,
                'processed_count' => $processedCount
            ]);
            $this->error($errorMessage);
            return 1;
        }

        return 0;
    }

    /**
     * Cancel a reservation
     */
    private function cancelReservation(Reservation $reservation, string $reason)
    {
        // Restore stock since reservation is being cancelled (stock was deducted on creation)
        if ($reservation->items && is_array($reservation->items)) {
            foreach ($reservation->items as $item) {
                $sizeId = $item['size_id'] ?? null;
                if ($sizeId) {
                    $size = \App\Models\ProductSize::find($sizeId);
                    if ($size) {
                        $size->increment('stock', $item['quantity']);
                        Log::info("Stock restored due to auto-cancellation: Size ID {$sizeId}, Product: {$item['product_name']}, Quantity: {$item['quantity']}");
                    }
                }
            }
        }

        $reservation->status = 'cancelled';
        $reservation->notes = ($reservation->notes ? $reservation->notes . "\n" : '') . 
                             "Auto-cancelled on " . Carbon::now()->toDateTimeString() . ": " . $reason;
        $reservation->save();

        Log::info('Reservation auto-cancelled', [
            'reservation_id' => $reservation->reservation_id,
            'customer_name' => $reservation->customer_name,
            'pickup_date' => $reservation->pickup_date,
            'pickup_time' => $reservation->pickup_time,
            'reason' => $reason
        ]);
    }

    /**
     * Mark a reservation for cancellation
     */
    private function markForCancellation(Reservation $reservation, string $reason)
    {
        $reservation->status = 'for_cancellation';
        $reservation->notes = ($reservation->notes ? $reservation->notes . "\n" : '') . 
                             "Marked for cancellation on " . Carbon::now()->toDateTimeString() . ": " . $reason;
        $reservation->save();

        Log::info('Reservation marked for cancellation', [
            'reservation_id' => $reservation->reservation_id,
            'customer_name' => $reservation->customer_name,
            'pickup_date' => $reservation->pickup_date,
            'pickup_time' => $reservation->pickup_time,
            'reason' => $reason
        ]);
    }

    /**
     * Create notifications about cancelled reservations
     */
    private function createCancellationNotifications(int $cancelledCount, int $markedForCancellationCount)
    {
        try {
            $notificationService = app(NotificationService::class);
            
            if ($cancelledCount > 0) {
                $notificationService->createCustomNotification([
                    'title' => 'Reservations Auto-Cancelled',
                    'message' => "{$cancelledCount} reservation(s) were automatically cancelled due to expired pickup dates.",
                    'type' => 'reservation_auto_cancellation',
                    'target_role' => 'all',
                    'icon' => 'fas fa-exclamation-triangle',
                    'priority' => 'high',
                    'data' => [
                        'cancelled_count' => $cancelledCount,
                        'processed_at' => Carbon::now()->toISOString()
                    ]
                ]);
            }

            if ($markedForCancellationCount > 0) {
                $notificationService->createCustomNotification([
                    'title' => 'Reservations Marked for Cancellation',
                    'message' => "{$markedForCancellationCount} reservation(s) were marked for cancellation as pickup time has expired and it's past business hours.",
                    'type' => 'reservation_pending_cancellation',
                    'target_role' => 'all',
                    'icon' => 'fas fa-clock',
                    'priority' => 'medium',
                    'data' => [
                        'marked_count' => $markedForCancellationCount,
                        'processed_at' => Carbon::now()->toISOString()
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create cancellation notifications', [
                'error' => $e->getMessage(),
                'cancelled_count' => $cancelledCount,
                'marked_for_cancellation_count' => $markedForCancellationCount
            ]);
        }
    }
}