<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\ProductSize;

/**
 * Seeder: Generate large volume of mock POS transactions (1000+)
 * Affects ONLY: users (adds 3 cashier accounts if missing), employees (links to those users),
 *               transactions, transaction_items.
 * Does NOT touch: products/product_sizes (used as-is), reservations, other domain tables.
 *
 * Generation rules:
 * - Span of last 3 years up to yesterday (avoid today to reduce interference with live counts)
 * - Business hours: random time between 10:00 and 19:00 (inclusive)
 * - sale_type: always 'pos'
 * - 1–3 items per transaction; quantity per item 1–2
 * - Discount occasionally applied (≈15% of transactions get 5–10% discount)
 * - amount_paid rounded to nearest 5 or 10 to simulate real cash handling, producing change
 * - transaction_id format: TXN-YYYYMMDD-#### (sequential per sale_date)
 * - Skips generation entirely if >= 1000 transactions already exist (idempotence guard)
 */
class MockTransactionsSeeder extends Seeder
{
    /** @var int */
    protected int $targetCount = 1200; // 1000+ as requested

    /** @var array<string,array<string,string>> */
    protected array $cashierProfiles = [
        // Only two mock cashiers as requested
        'kyla.mariz'   => ['fullname' => 'Kyla Mariz',   'email' => 'kyla.mariz@example.test',   'phone' => '09170000001'],
        'aeron.talain' => ['fullname' => 'Aeron Talain', 'email' => 'aeron.talain@example.test', 'phone' => '09170000002'],
    ];

    public function run(): void
    {
        // Idempotence guard: skip if we already have sufficient data
        $existing = Transaction::count();
        if ($existing >= 1000) {
            echo "[MockTransactionsSeeder] Skipping: {$existing} transactions already present.\n";
            return;
        }

        echo "[MockTransactionsSeeder] Preparing cashier accounts...\n";
        $cashierUserIds = $this->ensureCashierUsers();
        echo "[MockTransactionsSeeder] Cashiers ready: ".implode(', ', $cashierUserIds)."\n";

        // Fetch available product sizes with product relation for richer item data
        $sizes = ProductSize::with('product')
            ->where('is_available', true)
            ->where('stock', '>', 0)
            ->get();

        if ($sizes->isEmpty()) {
            echo "[MockTransactionsSeeder] Aborting: No available product sizes found.\n";
            return;
        }

    echo "[MockTransactionsSeeder] Generating {$this->targetCount} mock transactions within business hours (10:00–19:00)...\n";

        // Pre-cache sizes in array for faster random access
        $sizePool = $sizes->all();
        $sizeCount = count($sizePool);

        // Date range (last 3 years excluding today)
        $endDate = Carbon::yesterday();
        $startDate = (clone $endDate)->subYears(3)->startOfDay();

        // We will generate a random date for each transaction; to keep IDs sequential per day
        // we query the count per date before insertion.

        $transactionsCreated = 0;

        // Use chunked insertion for items for modest performance improvement
        $itemBuffer = [];
        $bufferLimit = 500; // flush every 500 items

        while ($transactionsCreated < $this->targetCount) {
            // Random date within range
            $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
            $saleDate = Carbon::createFromTimestamp($randomTimestamp);

            // Adjust time to business hours (10:00–19:00 inclusive at 19:00:00)
            // Hours 10..18 allow any minute/second; hour 19 is clamped to exactly 19:00:00
            $hour = rand(10, 19);
            if ($hour === 19) {
                $saleDate->setTime(19, 0, 0);
            } else {
                $saleDate->setTime($hour, rand(0, 59), rand(0, 59));
            }

            // Determine sequential number for the day for transaction_id
            $dailySeq = Transaction::whereDate('sale_date', $saleDate->toDateString())->count() + 1;
            $txnId = 'TXN-' . $saleDate->format('Ymd') . '-' . str_pad($dailySeq, 4, '0', STR_PAD_LEFT);

            // Pick cashier
            $cashierUserId = $cashierUserIds[array_rand($cashierUserIds)];

            // Build items
            $itemCount = rand(1, 3);
            $subtotal = 0;
            $builtItems = [];

            for ($i = 0; $i < $itemCount; $i++) {
                $ps = $sizePool[rand(0, $sizeCount - 1)];
                $product = $ps->product; // may be null if inconsistency; guard
                if (!$product) { continue; }
                $quantity = rand(1, 2);
                $unitPrice = (float) $product->price;
                $costPrice = round($unitPrice * rand(50, 75) / 100, 2); // 50–75% of retail
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
                    'created_at' => $saleDate->copy(),
                    'updated_at' => $saleDate->copy(),
                ];
            }

            if (empty($builtItems)) {
                // If no items built (unlikely), skip this iteration
                continue;
            }

            // Discount logic
            $discountRate = (mt_rand(1, 100) <= 15) ? rand(5, 10) / 100 : 0; // 15% chance
            $discountAmount = round($subtotal * $discountRate, 2);
            $total = $subtotal - $discountAmount;

            // Amount paid: simulate paying with rounded bills (nearest 5 or 10)
            $roundBase = (mt_rand(0, 1) === 1) ? 10 : 5;
            $amountPaid = ceil($total / $roundBase) * $roundBase;
            if ($amountPaid < $total) { $amountPaid = $total; }
            $change = round($amountPaid - $total, 2);

            // Insert transaction
            Transaction::create([
                'transaction_id' => $txnId,
                'sale_type' => 'pos',
                'reservation_id' => null,
                'user_id' => $cashierUserId,
                'subtotal' => round($subtotal, 2),
                'discount_amount' => $discountAmount,
                'total_amount' => round($total, 2),
                'amount_paid' => round($amountPaid, 2),
                'change_given' => $change,
                'sale_date' => $saleDate->copy(),
                'created_at' => $saleDate->copy(),
                'updated_at' => $saleDate->copy(),
            ]);

            // Buffer items
            foreach ($builtItems as $it) { $itemBuffer[] = $it; }
            if (count($itemBuffer) >= $bufferLimit) {
                DB::table('transaction_items')->insert($itemBuffer);
                $itemBuffer = [];
            }

            $transactionsCreated++;
            if ($transactionsCreated % 100 === 0) {
                echo "[MockTransactionsSeeder] Created {$transactionsCreated} transactions...\n";
            }
        }

        // Flush remaining items
        if (!empty($itemBuffer)) {
            DB::table('transaction_items')->insert($itemBuffer);
        }

        echo "[MockTransactionsSeeder] Done. Inserted {$transactionsCreated} transactions with ".TransactionItem::whereIn('transaction_id', Transaction::orderBy('created_at', 'desc')->limit($transactionsCreated)->pluck('transaction_id'))->count()." items.\n";
    }

    /**
     * Ensure 3 cashier user+employee profiles exist; return their user_ids.
     * @return array<int,string>
     */
    protected function ensureCashierUsers(): array
    {
        $ids = [];
        foreach ($this->cashierProfiles as $username => $profile) {
            // Prefer existing employee by fullname (reuses existing user_id if present)
            $existingEmp = Employee::where('fullname', $profile['fullname'])->first();
            if ($existingEmp && $existingEmp->user) {
                $ids[] = $existingEmp->user->user_id;
                continue;
            }

            // Otherwise, look up/create by username
            $user = User::where('username', $username)->first();
            if (!$user) {
                $user = User::create([
                    'username' => $username,
                    'password' => Hash::make('password123'),
                    'role' => 'staff', // using existing role taxonomy
                    'is_active' => true,
                ]);

                Employee::create([
                    'user_id' => $user->user_id,
                    'fullname' => $profile['fullname'],
                    'email' => $profile['email'],
                    'phone_number' => $profile['phone'],
                    'hire_date' => Carbon::now()->subYears(rand(1, 3))->subDays(rand(0, 365)),
                    'position' => 'Cashier',
                ]);
            }
            $ids[] = $user->user_id;
        }
        return $ids;
    }
}
