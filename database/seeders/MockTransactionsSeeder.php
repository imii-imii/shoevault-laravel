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
 * - Date range: January 1, 2022 to November 10, 2025
 * - Business hours: 10:00 AM to 7:00 PM
 * - Monthly revenue target: 50k-600k PHP
 * - Daily revenue calculated from monthly target
 * - 10% chance store is closed on any given day
 * - Reservations: 1-5 items, connected to customers, status: completed/cancelled
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

    /** @var array<string,array<int,int>> Philippine holidays that boost sales */
    protected array $philippineHolidays = [
        // Christmas season (biggest sales boost)
        '12' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
        // Valentine's Day period
        '02' => [12, 13, 14, 15, 16],
        // Holy Week (March/April - using March for simplicity)
        '03' => [28, 29, 30, 31],
        '04' => [1, 2, 3],
        // Mother's Day (May 2nd Sunday)
        '05' => [8, 9, 10, 11, 12, 13, 14],
        // Back to School (June)
        '06' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        // Father's Day (June 3rd Sunday)
        '06' => [15, 16, 17, 18, 19, 20, 21],
        // Independence Day
        '06' => [12],
        // All Saints' Day
        '11' => [1, 2],
        // New Year
        '01' => [1, 2, 3]
    ];

    public function run(): void
    {
        // Check for existing data
        $existingTransactions = Transaction::count();
        $existingReservations = Reservation::count();
        
        if ($existingTransactions > 0 || $existingReservations > 0) {
            echo "[MockTransactionsSeeder] Found existing data:\n";
            echo "  - Transactions: {$existingTransactions}\n";
            echo "  - Reservations: {$existingReservations}\n";
            echo "\nThis will remove ALL existing transactions and reservations data.\n";
            echo "Do you want to continue? (yes/no): ";
            
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            fclose($handle);
            
            if (trim(strtolower($line)) !== 'yes') {
                echo "[MockTransactionsSeeder] Cancelled by user.\n";
                return;
            }
            
            echo "[MockTransactionsSeeder] Removing existing data...\n";
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            TransactionItem::truncate();
            Transaction::truncate();
            Reservation::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            echo "[MockTransactionsSeeder] Existing data cleared.\n";
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
        
        // Date range: January 1, 2022 to November 7, 2025
        $startDate = Carbon::createFromDate(2022, 1, 1)->startOfDay();
        $endDate = Carbon::createFromDate(2025, 11, 10)->endOfDay();

        echo "[MockTransactionsSeeder] Generating transactions and reservations from {$startDate->toDateString()} to {$endDate->toDateString()}...\n";

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $currentDate = $startDate->copy();
        
        $reservationsCreated = 0;
        $transactionsCreated = 0;
        $totalItems = 0;

        while ($currentDate->lte($endDate)) {
            // Calculate target monthly revenue (50k-600k)
            $monthlyTarget = rand(50000, 600000);
            $daysInMonth = $currentDate->daysInMonth;
            $dailyRevenueTarget = $monthlyTarget / $daysInMonth;
            
            // Apply Philippine holiday sales multiplier
            $holidayMultiplier = $this->getHolidaySalesMultiplier($currentDate);
            $dailyRevenueTarget *= $holidayMultiplier;
            
            // 10% chance store is closed (holidays, rest days)
            // But reduce closure chance during high sales periods
            $closureChance = $holidayMultiplier > 1.5 ? 3 : 10; // Only 3% chance during major holidays
            $isClosed = mt_rand(1, 100) <= $closureChance;
            if ($isClosed) {
                echo "[MockTransactionsSeeder] {$currentDate->toDateString()}: Store closed\n";
                $currentDate->addDay();
                continue;
            }
            
            // Calculate how many items needed to reach daily revenue target
            // Average item price is around 3000-8000 PHP
            $avgItemPrice = 5500;
            $dailyItemTarget = max(1, (int)($dailyRevenueTarget / $avgItemPrice));
            $dailyItemTarget = min($dailyItemTarget, 25); // Increase cap to 25 for holiday periods
            
            $itemsCreatedToday = 0;
            
            // Reservation frequency increases during holidays
            $baseReservationChance = 25;
            $holidayReservationChance = min(75, $baseReservationChance * $holidayMultiplier);
            $hasReservations = mt_rand(1, 100) <= $holidayReservationChance;
            $reservationCount = 0;
            
            if ($hasReservations) {
                $maxReservations = $holidayMultiplier > 2.0 ? 4 : ($holidayMultiplier > 1.5 ? 3 : 2);
                $reservationCount = rand(1, $maxReservations);
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
                            $itemsCreatedToday += count($reservation->items); // Use actual item count
                        }
                    }
                }
            }
            
            // Fill remaining daily target with POS transactions  
            $attempts = 0;
            while ($itemsCreatedToday < $dailyItemTarget && $attempts < 20) {
                $remainingItems = $dailyItemTarget - $itemsCreatedToday;
                $itemsInTransaction = min(rand(1, 3), $remainingItems);
                
                $transaction = $this->createPosTransaction($currentDate, $staffUserIds, $sizePool, $itemsInTransaction);
                if ($transaction) {
                    $transactionsCreated++;
                    $itemsCreatedToday += $itemsInTransaction;
                }
                $attempts++;
            }
            
            $totalItems += $itemsCreatedToday;
            
            // Calculate daily revenue for reporting
            $dailyRevenue = 0;
            $todaysTransactions = Transaction::whereDate('sale_date', $currentDate->toDateString())->get();
            foreach ($todaysTransactions as $txn) {
                $dailyRevenue += $txn->total_amount;
            }
            
            if ($currentDate->day === 1 || $transactionsCreated % 50 === 0 || $itemsCreatedToday > 0 || $holidayMultiplier > 1.2) {
                $holidayNote = $holidayMultiplier > 1.2 ? " (Holiday boost: x{$holidayMultiplier})" : "";
                echo "[MockTransactionsSeeder] {$currentDate->toDateString()}: {$itemsCreatedToday} items, ₱" . number_format($dailyRevenue, 2) . " revenue{$holidayNote}, {$transactionsCreated} total txn, {$reservationsCreated} reservations\n";
            }
            
            $currentDate->addDay();
        }

        // Calculate final statistics
        $totalRevenue = Transaction::sum('total_amount');
        $monthlyAverage = $totalRevenue / 35; // Approximate months from Jan 2023 to Nov 2025
        
        echo "[MockTransactionsSeeder] Complete!\n";
        echo "  - Transactions: {$transactionsCreated}\n";
        echo "  - Reservations: {$reservationsCreated}\n";
        echo "  - Total Items: {$totalItems}\n";
        echo "  - Total Revenue: ₱" . number_format($totalRevenue, 2) . "\n";
        echo "  - Monthly Average: ₱" . number_format($monthlyAverage, 2) . "\n";
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
     * Check if a date is a Philippine holiday period with sales boost
     */
    protected function getHolidaySalesMultiplier(Carbon $date): float
    {
        $month = $date->format('m');
        $day = (int) $date->format('d');
        
        // Christmas season gets the biggest boost
        if ($month === '12') {
            if ($day >= 15 && $day <= 25) {
                return 3.5; // 350% boost for Christmas week
            } elseif ($day >= 1 && $day <= 31) {
                return 2.2; // 220% boost for entire December
            }
        }
        
        // Valentine's Day period
        if ($month === '02' && $day >= 12 && $day <= 16) {
            return 1.8; // 180% boost
        }
        
        // Holy Week
        if (($month === '03' && $day >= 28) || ($month === '04' && $day <= 3)) {
            return 1.6; // 160% boost
        }
        
        // Mother's Day period (2nd Sunday of May)
        if ($month === '05' && $day >= 8 && $day <= 14) {
            return 1.7; // 170% boost
        }
        
        // Back to School (early June)
        if ($month === '06' && $day >= 1 && $day <= 15) {
            return 1.9; // 190% boost
        }
        
        // All Saints' Day
        if ($month === '11' && ($day === 1 || $day === 2)) {
            return 1.4; // 140% boost
        }
        
        // New Year period
        if ($month === '01' && $day >= 1 && $day <= 3) {
            return 1.5; // 150% boost
        }
        
        return 1.0; // Normal sales
    }

    /**
     * Determine if accessories should be included (30% chance)
     */
    protected function shouldIncludeAccessories(): bool
    {
        return mt_rand(1, 100) <= 30;
    }

    /**
     * Adjust product category (40% chance men becomes women)
     */
    protected function adjustProductCategory(string $originalCategory): string
    {
        if (strtolower($originalCategory) === 'men' && mt_rand(1, 100) <= 40) {
            return 'women';
        }
        return $originalCategory;
    }

    /**
     * Create a reservation with exactly 5 items.
     */
    protected function createReservation($date, $customerIds, $sizePool): ?Reservation
    {
        $customerId = $customerIds[array_rand($customerIds)];
        
        // Select 3-5 random product sizes for the reservation (more realistic)
        $itemCount = rand(1, 5);
        $reservationItems = [];
        $totalAmount = 0;
        
        for ($i = 0; $i < $itemCount; $i++) {
            $ps = $sizePool[array_rand($sizePool)];
            $product = $ps->product;
            if (!$product) continue;
            
            $quantity = 1; // Reservations typically have 1 of each item
            $unitPrice = (float) $product->price;
            $totalAmount += $unitPrice * $quantity;
            
            // Skip accessories unless they pass the 30% chance
            if (strtolower($product->category) === 'accessories' && !$this->shouldIncludeAccessories()) {
                continue;
            }
            
            $reservationItems[] = [
                'product_size_id' => $ps->product_size_id,
                'product_name' => $product->name,
                'product_brand' => $product->brand,
                'product_color' => $product->color,
                'product_category' => $this->adjustProductCategory($product->category),
                'size' => $ps->size,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }
        
        if (count($reservationItems) < 3) {
            return null; // Skip if we couldn't get at least 3 items
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
                'product_category' => $item['product_category'], // Already adjusted during reservation creation
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
        
        $attempts = 0;
        for ($i = 0; $i < $itemCount && $attempts < $itemCount * 3; $i++) {
            $ps = $sizePool[array_rand($sizePool)];
            $product = $ps->product;
            if (!$product) {
                $attempts++;
                $i--; // Try again
                continue;
            }
            
            // Skip accessories unless they pass the 30% chance
            if (strtolower($product->category) === 'accessories' && !$this->shouldIncludeAccessories()) {
                $attempts++;
                $i--; // Try again
                continue;
            }
            
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
                'product_category' => $this->adjustProductCategory($product->category),
                'quantity' => $quantity,
                'size' => $ps->size,
                'unit_price' => $unitPrice,
                'cost_price' => $costPrice,
                'created_at' => $transactionTime,
                'updated_at' => $transactionTime,
            ];
            $attempts++;
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
