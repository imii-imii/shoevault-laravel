<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductSize;

/**
 * Seeder: Generate realistic transactions and reservations based on products in database
 * Affects: users (cashiers/managers), employees, customers, reservations, transactions, transaction_items
 *
 * Generation rules:
 * - Date range: January 1, 2023 to November 7, 2025
 * - Business hours: 10:00 AM to 7:00 PM
 * - Daily minimum: 3-10 items sold (POS + Reservations combined)
 * - Reservations: exactly 5 items, connected to customers, status: completed/cancelled
 * - POS transactions: 1-3 items per transaction
 * - Reservation transactions: must match existing reservation records
 * - Cashiers and managers can complete reservations
 * - Sample customers created for reservations
 */
class MockTransactionsSeeder extends Seeder
{
    /** @var array<string,array<string,string>> */
    protected array $staffProfiles = [
        'kyla.mariz'   => ['fullname' => 'Kyla Mariz',   'email' => 'kyla.mariz@example.test',   'phone' => '09170000001', 'position' => 'Cashier'],
        'aeron.talain' => ['fullname' => 'Aeron Talain', 'email' => 'aeron.talain@example.test', 'phone' => '09170000002', 'position' => 'Cashier'],
        'manager.smith' => ['fullname' => 'Alex Smith', 'email' => 'alex.smith@example.test', 'phone' => '09170000003', 'position' => 'Manager'],
    ];

    /** @var array<string,array<string,string>> */
    protected array $customerProfiles = [
        ['fullname' => 'Maria Santos', 'email' => 'maria.santos@example.com', 'phone' => '09171234001'],
        ['fullname' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com', 'phone' => '09171234002'], 
        ['fullname' => 'Ana Rodriguez', 'email' => 'ana.rodriguez@example.com', 'phone' => '09171234003'],
        ['fullname' => 'Carlos Mendoza', 'email' => 'carlos.mendoza@example.com', 'phone' => '09171234004'],
        ['fullname' => 'Lisa Garcia', 'email' => 'lisa.garcia@example.com', 'phone' => '09171234005'],
        ['fullname' => 'Michael Johnson', 'email' => 'michael.johnson@example.com', 'phone' => '09171234006'],
        ['fullname' => 'Sarah Williams', 'email' => 'sarah.williams@example.com', 'phone' => '09171234007'],
        ['fullname' => 'David Brown', 'email' => 'david.brown@example.com', 'phone' => '09171234008'],
        ['fullname' => 'Jennifer Davis', 'email' => 'jennifer.davis@example.com', 'phone' => '09171234009'],
        ['fullname' => 'Robert Miller', 'email' => 'robert.miller@example.com', 'phone' => '09171234010'],
    ];

    public function run(): void
    {
        // Idempotence guard: skip if we already have sufficient data
        $existing = Transaction::count();
        if ($existing >= 1000) {
            echo "[MockTransactionsSeeder] Skipping: {$existing} transactions already present.\n";
            return;
        }

        echo "[MockTransactionsSeeder] Preparing staff accounts...\n";
        $staffUserIds = $this->ensureStaffUsers();
        echo "[MockTransactionsSeeder] Staff ready: ".implode(', ', array_keys($staffUserIds))."\n";

        echo "[MockTransactionsSeeder] Creating customer accounts...\n";
        $customerIds = $this->createCustomers();
        echo "[MockTransactionsSeeder] Created ".count($customerIds)." customers.\n";

        // Fetch available product sizes with product relation
        $sizes = ProductSize::with('product')
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->get();

        if ($sizes->isEmpty()) {
            echo "[MockTransactionsSeeder] Aborting: No available product sizes found.\n";
            return;
        }

        $sizePool = $sizes->all();
        
        // Date range: January 1, 2023 to November 7, 2025
        $startDate = Carbon::createFromDate(2023, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate(2025, 11, 7)->endOfDay();

        echo "[MockTransactionsSeeder] Generating transactions and reservations from {$startDate->toDateString()} to {$endDate->toDateString()}...\n";

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $currentDate = $startDate->copy();
        
        $reservationsCreated = 0;
        $transactionsCreated = 0;
        $totalItems = 0;

        while ($currentDate->lte($endDate)) {
            // Determine daily target: 3-10 items sold
            $dailyItemTarget = rand(3, 10);
            $itemsCreatedToday = 0;
            
            // 30% chance of having reservations on any given day
            $hasReservations = mt_rand(1, 100) <= 30;
            $reservationCount = 0;
            
            if ($hasReservations) {
                $reservationCount = rand(1, 2); // 1-2 reservations per day when they occur
            }
            
            // Create reservations for this day
            for ($r = 0; $r < $reservationCount; $r++) {
                $reservation = $this->createReservation($currentDate, $customerIds, $sizePool);
                if ($reservation) {
                    $reservationsCreated++;
                    
                    // Create transaction for completed reservations
                    if ($reservation->status === 'completed') {
                        $transaction = $this->createReservationTransaction($reservation, $staffUserIds, $currentDate);
                        if ($transaction) {
                            $transactionsCreated++;
                            $itemsCreatedToday += 5; // Reservations always have 5 items
                        }
                    }
                }
            }
            
            // Fill remaining daily target with POS transactions  
            while ($itemsCreatedToday < $dailyItemTarget) {
                $remainingItems = $dailyItemTarget - $itemsCreatedToday;
                $itemsInTransaction = min(rand(1, 3), $remainingItems);
                
                $transaction = $this->createPosTransaction($currentDate, $staffUserIds, $sizePool, $itemsInTransaction);
                if ($transaction) {
                    $transactionsCreated++;
                    $itemsCreatedToday += $itemsInTransaction;
                }
            }
            
            $totalItems += $itemsCreatedToday;
            
            if ($currentDate->day === 1 || $transactionsCreated % 50 === 0) {
                echo "[MockTransactionsSeeder] {$currentDate->toDateString()}: {$itemsCreatedToday} items, {$transactionsCreated} total transactions, {$reservationsCreated} reservations\n";
            }
            
            $currentDate->addDay();
        }

        echo "[MockTransactionsSeeder] Complete! Created {$transactionsCreated} transactions, {$reservationsCreated} reservations, {$totalItems} total items sold.\n";
    }

    /**
     * Ensure staff user+employee profiles exist; return their user_ids with roles.
     * @return array<string,array<string,mixed>>
     */
    protected function ensureStaffUsers(): array
    {
        $ids = [];
        foreach ($this->staffProfiles as $username => $profile) {
            // Prefer existing employee by fullname (reuses existing user_id if present)
            $existingEmp = Employee::where('fullname', $profile['fullname'])->first();
            if ($existingEmp && $existingEmp->user) {
                $ids[$username] = [
                    'user_id' => $existingEmp->user->user_id,
                    'position' => $profile['position']
                ];
                continue;
            }

            // Otherwise, look up/create by username
            $user = User::where('username', $username)->first();
            if (!$user) {
                $role = $profile['position'] === 'Manager' ? 'admin' : 'staff';
                $user = User::create([
                    'username' => $username,
                    'password' => Hash::make('password123'),
                    'role' => $role,
                    'is_active' => true,
                ]);

                Employee::create([
                    'user_id' => $user->user_id,
                    'fullname' => $profile['fullname'],
                    'email' => $profile['email'],
                    'phone_number' => $profile['phone'],
                    'hire_date' => Carbon::now()->subYears(rand(1, 3))->subDays(rand(0, 365)),
                    'position' => $profile['position'],
                ]);
            }
            $ids[$username] = [
                'user_id' => $user->user_id,
                'position' => $profile['position']
            ];
        }
        return $ids;
    }

    /**
     * Create sample customers for reservations.
     * @return array<string>
     */
    protected function createCustomers(): array
    {
        $customerIds = [];
        foreach ($this->customerProfiles as $profile) {
            // Check if customer already exists by email
            $existingCustomer = Customer::where('email', $profile['email'])->first();
            if ($existingCustomer) {
                $customerIds[] = $existingCustomer->customer_id;
                continue;
            }

            // Create user account for customer
            $username = strtolower(str_replace(' ', '.', $profile['fullname']));
            $user = User::where('username', $username)->first();
            if (!$user) {
                $user = User::create([
                    'username' => $username,
                    'password' => Hash::make('customer123'),
                    'role' => 'customer',
                    'is_active' => true,
                ]);
            }

            // Create customer profile
            $customer = Customer::create([
                'user_id' => $user->user_id,
                'fullname' => $profile['fullname'],
                'email' => $profile['email'],
                'phone_number' => $profile['phone'],
            ]);

            $customerIds[] = $customer->customer_id;
        }
        return $customerIds;
    }

    /**
     * Create a reservation with exactly 5 items.
     */
    protected function createReservation($date, $customerIds, $sizePool): ?Reservation
    {
        $customerId = $customerIds[array_rand($customerIds)];
        
        // Select 5 random product sizes for the reservation
        $reservationItems = [];
        $totalAmount = 0;
        
        for ($i = 0; $i < 5; $i++) {
            $ps = $sizePool[array_rand($sizePool)];
            $product = $ps->product;
            if (!$product) continue;
            
            $quantity = 1; // Reservations typically have 1 of each item
            $unitPrice = (float) $product->price;
            $totalAmount += $unitPrice * $quantity;
            
            $reservationItems[] = [
                'product_size_id' => $ps->product_size_id,
                'product_name' => $product->name,
                'product_brand' => $product->brand,
                'product_color' => $product->color,
                'product_category' => $product->category,
                'size' => $ps->size,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }
        
        if (count($reservationItems) < 5) {
            return null; // Skip if we couldn't get 5 items
        }
        
        // Random pickup date (1-7 days after reservation date)
        $pickupDate = $date->copy()->addDays(rand(1, 7));
        $pickupTime = sprintf('%02d:%02d:00', rand(10, 18), rand(0, 59));
        
        // 70% completed, 30% cancelled
        $status = mt_rand(1, 100) <= 70 ? 'completed' : 'cancelled';
        
        // Set reserved_at to a random time during business hours on the reservation date
        $reservedAt = $date->copy()->setTime(rand(10, 19), rand(0, 59), rand(0, 59));
        
        // Generate reservation ID based on the reservation date, not current date
        $reservationDate = $reservedAt->format('Ymd');
        $dailyReservationCount = Reservation::whereDate('reserved_at', $reservedAt->toDateString())->count() + 1;
        $reservationId = 'RSV-' . $reservationDate . '-' . str_pad($dailyReservationCount, 4, '0', STR_PAD_LEFT);

        $reservation = Reservation::create([
            'reservation_id' => $reservationId,
            'customer_id' => $customerId,
            'items' => $reservationItems,
            'total_amount' => round($totalAmount, 2),
            'pickup_date' => $pickupDate->toDateString(),
            'pickup_time' => $pickupTime,
            'status' => $status,
            'reserved_at' => $reservedAt,
            'notes' => $status === 'cancelled' ? 'Customer cancelled reservation' : null,
            'created_at' => $reservedAt,
            'updated_at' => $reservedAt,
        ]);
        
        return $reservation;
    }

    /**
     * Create a transaction for a completed reservation.
     */
    protected function createReservationTransaction($reservation, $staffUserIds, $date): ?Transaction
    {
        // Only cashiers and managers can complete reservations
        $eligibleStaff = array_filter($staffUserIds, function($staff) {
            return in_array($staff['position'], ['Cashier', 'Manager']);
        });
        
        if (empty($eligibleStaff)) {
            return null;
        }
        
        $staff = $eligibleStaff[array_rand($eligibleStaff)];
        
        // Transaction happens at pickup time on pickup date
        $transactionTime = Carbon::parse($reservation->pickup_date->format('Y-m-d') . ' ' . $reservation->pickup_time);
        
        // Generate transaction ID
        $dailySeq = Transaction::whereDate('sale_date', $transactionTime->toDateString())->count() + 1;
        $txnId = 'TXN-' . $transactionTime->format('Ymd') . '-' . str_pad($dailySeq, 4, '0', STR_PAD_LEFT);
        
        $subtotal = $reservation->total_amount;
        $discountAmount = 0; // Reservations don't typically have discounts
        $total = $subtotal;
        
        // Amount paid: simulate payment
        $roundBase = (mt_rand(0, 1) === 1) ? 10 : 5;
        $amountPaid = ceil($total / $roundBase) * $roundBase;
        if ($amountPaid < $total) { $amountPaid = $total; }
        $change = round($amountPaid - $total, 2);
        
        $transaction = Transaction::create([
            'transaction_id' => $txnId,
            'sale_type' => 'reservation',
            'reservation_id' => $reservation->reservation_id,
            'user_id' => $staff['user_id'],
            'subtotal' => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'total_amount' => round($total, 2),
            'amount_paid' => round($amountPaid, 2),
            'change_given' => $change,
            'sale_date' => $transactionTime,
            'created_at' => $transactionTime,
            'updated_at' => $transactionTime,
        ]);
        
        // Create transaction items from reservation items
        foreach ($reservation->items as $item) {
            TransactionItem::create([
                'transaction_id' => $txnId,
                'product_size_id' => $item['product_size_id'],
                'product_name' => $item['product_name'],
                'product_brand' => $item['product_brand'],
                'product_color' => $item['product_color'],
                'product_category' => $item['product_category'],
                'quantity' => $item['quantity'],
                'size' => $item['size'],
                'unit_price' => $item['unit_price'],
                'cost_price' => round($item['unit_price'] * rand(50, 75) / 100, 2),
                'created_at' => $transactionTime,
                'updated_at' => $transactionTime,
            ]);
        }
        
        return $transaction;
    }

    /**
     * Create a POS transaction with specified number of items.
     */
    protected function createPosTransaction($date, $staffUserIds, $sizePool, $itemCount): ?Transaction
    {
        // All staff can handle POS transactions
        $staff = $staffUserIds[array_rand($staffUserIds)];
        
        // Random time during business hours
        $transactionTime = $date->copy()->setTime(rand(10, 19), rand(0, 59), rand(0, 59));
        
        // Generate transaction ID
        $dailySeq = Transaction::whereDate('sale_date', $transactionTime->toDateString())->count() + 1;
        $txnId = 'TXN-' . $transactionTime->format('Ymd') . '-' . str_pad($dailySeq, 4, '0', STR_PAD_LEFT);
        
        // Build items
        $subtotal = 0;
        $builtItems = [];
        
        for ($i = 0; $i < $itemCount; $i++) {
            $ps = $sizePool[array_rand($sizePool)];
            $product = $ps->product;
            if (!$product) continue;
            
            $quantity = rand(1, 2);
            $unitPrice = (float) $product->price;
            $costPrice = round($unitPrice * rand(50, 75) / 100, 2);
            $subtotal += $unitPrice * $quantity;
            
            $builtItems[] = [
                'transaction_id' => $txnId,
                'product_size_id' => $ps->product_size_id,
                'product_name' => $product->name,
                'product_brand' => $product->brand,
                'product_color' => $product->color,
                'product_category' => $product->category,
                'quantity' => $quantity,
                'size' => $ps->size,
                'unit_price' => $unitPrice,
                'cost_price' => $costPrice,
                'created_at' => $transactionTime,
                'updated_at' => $transactionTime,
            ];
        }
        
        if (empty($builtItems)) {
            return null;
        }
        
        // Discount logic (15% chance of 5-10% discount)
        $discountRate = (mt_rand(1, 100) <= 15) ? rand(5, 10) / 100 : 0;
        $discountAmount = round($subtotal * $discountRate, 2);
        $total = $subtotal - $discountAmount;
        
        // Amount paid: simulate payment
        $roundBase = (mt_rand(0, 1) === 1) ? 10 : 5;
        $amountPaid = ceil($total / $roundBase) * $roundBase;
        if ($amountPaid < $total) { $amountPaid = $total; }
        $change = round($amountPaid - $total, 2);
        
        $transaction = Transaction::create([
            'transaction_id' => $txnId,
            'sale_type' => 'pos',
            'reservation_id' => null,
            'user_id' => $staff['user_id'],
            'subtotal' => round($subtotal, 2),
            'discount_amount' => $discountAmount,
            'total_amount' => round($total, 2),
            'amount_paid' => round($amountPaid, 2),
            'change_given' => $change,
            'sale_date' => $transactionTime,
            'created_at' => $transactionTime,
            'updated_at' => $transactionTime,
        ]);
        
        // Create transaction items
        foreach ($builtItems as $item) {
            TransactionItem::create($item);
        }
        
        return $transaction;
    }
}
