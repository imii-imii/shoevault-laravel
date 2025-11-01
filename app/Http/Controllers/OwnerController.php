<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OwnerController extends Controller
{
    /**
     * Display the owner dashboard
     */
    public function dashboard()
    {
        $dashboardData = $this->getDashboardKPIs();
        
        return view('owner.dashboard', compact('dashboardData'));
    }

    /**
     * Render Reports page (separate view mirroring dashboard reports tab)
     */
    public function reports()
    {
        $dashboardData = $this->getDashboardKPIs();
        return view('owner.reports', compact('dashboardData'));
    }

    /**
     * Display sales history reports
     */
    public function salesHistory(Request $request)
    {
        $period = $request->get('period', 'weekly');
        
        // Check if we have any transactions and create test data if empty
        $transactionCount = DB::table('transactions')->count();
        if ($transactionCount === 0) {
            Log::info('No transactions found, creating test data');
            $this->createTestTransactionData();
        }
        
        $salesData = $this->getSalesData($period);
        $topProducts = $this->getTopSellingProducts();
        // Fetch latest transactional rows with required columns
        $transactions = $this->getRecentSalesTransactions($period);

        Log::info("Returning sales history data - transactions count: " . count($transactions));

        return response()->json([
            'salesData' => $salesData,
            'topProducts' => $topProducts,
            'transactions' => $transactions,
            'period' => $period
        ]);
    }

    /**
     * Display reservation logs
     */
    public function reservationLogs(Request $request)
    {
        // Accept optional filters; default to "all" (completed + cancelled only)
        $status = $request->string('status')->lower()->value() ?: 'all';
        $search = $request->string('search')->value();
        $sort = $request->string('sort')->value() ?: 'date-desc';

        // Base query: only completed and cancelled reservations
        $query = Reservation::query()
            ->whereIn('status', ['completed', 'cancelled']);

        if (in_array($status, ['completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $q = "%$search%";
            $query->where(function ($sub) use ($q) {
                $sub->where('reservation_id', 'like', $q)
                    ->orWhere('customer_name', 'like', $q)
                    ->orWhere('customer_email', 'like', $q)
                    ->orWhere('customer_phone', 'like', $q);
            });
        }

        // Sorting options
        switch ($sort) {
            case 'date-asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'date-desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Fetch records (limit for performance; adjust if needed)
        $reservations = $query->with('customer')->limit(200)->get();

        // Counts for tabs
        $counts = [
            'completed' => Reservation::where('status', 'completed')->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count(),
        ];
        $counts['all'] = $counts['completed'] + $counts['cancelled'];

        return response()->json([
            'success' => true,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'sort' => $sort,
            ],
            'counts' => $counts,
            'reservations' => $reservations,
        ]);
    }

    /**
     * Display supply logs
     */
    public function supplyLogs(Request $request)
    {
        // Return supply log rows joined with suppliers as a single table
        $search = trim((string) $request->get('search', ''));
        $sort = $request->get('sort', 'date-desc'); // date-asc|date-desc|id-asc|id-desc

        $query = \App\Models\SupplyLog::query()
            ->with('supplier:id,name,country')
            ->select(['id','supplier_id','brand','size','quantity','received_at']);

        if ($search !== '') {
            $like = "%$search%";
            $query->where(function ($q) use ($like) {
                $q->where('brand', 'like', $like)
                  ->orWhere('size', 'like', $like);
            });
            // Join supplier constraints
            $query->orWhereHas('supplier', function ($qs) use ($like) {
                $qs->where('name', 'like', $like)
                   ->orWhere('country', 'like', $like);
            });
        }

        // Sorting
        switch ($sort) {
            case 'date-asc':
                $query->orderBy('received_at', 'asc');
                break;
            case 'id-asc':
                $query->orderBy('id', 'asc');
                break;
            case 'id-desc':
                $query->orderBy('id', 'desc');
                break;
            case 'date-desc':
            default:
                $query->orderBy('received_at', 'desc');
                break;
        }

        $logs = $query->limit(1000)->get();

        $rows = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'supplier_name' => optional($log->supplier)->name,
                'country' => optional($log->supplier)->country,
                'brand' => $log->brand,
                'size' => $log->size,
                'quantity' => (int) $log->quantity,
                'received_at' => optional($log->received_at)->toDateTimeString(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'supplyData' => $rows,
            'count' => $rows->count(),
        ]);
    }

    /**
     * Display inventory overview
     */
    public function inventoryOverview(Request $request)
    {
        $source = strtolower($request->get('source', 'pos')); // 'pos' or 'reservation'
        $category = $request->get('category'); // optional future filter
        $search = $request->get('search'); // optional

        // Use the Product model with proper scopes instead of raw queries
        if ($source === 'reservation') {
            $query = Product::with('sizes')
                ->reservationInventory()
                ->active();
        } else {
            $query = Product::with('sizes')
                ->posInventory()
                ->active();
        }

        // Apply filters
        if (!empty($category)) {
            $query->where('category', $category);
        }
        
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('brand', 'like', "%$search%")
                  ->orWhere('product_id', 'like', "%$search%");
            });
        }

        $products = $query->orderBy('name')->get();

        // Transform products to match the expected format
        $items = $products->map(function ($product) {
            $sizes = $product->sizes->pluck('size')->sort()->implode(', ');
            $sizesStock = $product->sizes->map(function ($size) {
                return $size->size . ':' . $size->stock;
            })->implode(',');
            $totalStock = $product->sizes->sum('stock');

            return (object) [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $product->name,
                'brand' => $product->brand,
                'color' => $product->color,
                'category' => $product->category,
                'price' => $product->price,
                'image_url' => $product->image_url && !str_starts_with($product->image_url, 'http') 
                    ? asset($product->image_url) 
                    : $product->image_url,
                'total_stock' => $totalStock,
                'sizes' => $sizes,
                'sizes_stock' => $sizesStock,
            ];
        });

        return response()->json([
            'success' => true,
            'source' => $source,
            'items' => $items,
        ]);
    }

    /**
     * Display settings/master controls
     */
    public function settings()
    {
        return view('owner.settings');
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if it exists
                if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
                    unlink(public_path($user->profile_picture));
                }
                
                // Upload new profile picture
                $file = $request->file('profile_picture');
                $fileName = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = 'assets/images/profiles/' . $fileName;
                
                // Create directory if it doesn't exist
                $directory = public_path('assets/images/profiles');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                $file->move($directory, $fileName);
                $validated['profile_picture'] = $filePath;
            }

            // Update user data
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_picture' => $this->getProfilePictureUrl($user)
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user profile picture
     */
    public function removeProfilePicture(Request $request)
    {
        try {
            $user = $request->user();
            
            // Delete old profile picture if it exists
            if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
                unlink(public_path($user->profile_picture));
            }
            
            // Update user record to remove profile picture
            $user->update(['profile_picture' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully!',
                'user' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_picture' => $this->getProfilePictureUrl($user)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string'
            ]);

            // Check if current password is correct
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                    'errors' => [
                        'current_password' => ['Current password is incorrect']
                    ]
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard KPIs
     */
    private function getDashboardKPIs()
    {
        $totalProducts = Product::count();
        $totalStock = Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                      ->sum('product_sizes.stock');
        $lowStockItems = Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                        ->where('product_sizes.stock', '<=', 10)
                        ->count();
        // Use resilient total calculation depending on current schema
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        $todaySales = Transaction::whereDate($dateCol, today())
            ->select(DB::raw("SUM($amountExpr) as total"))
            ->value('total') ?? 0;
        $activeReservations = Reservation::where('status', 'pending')->count();

        // KPIs requested: total products sold (units), completed and cancelled reservations (all-time)
        $totalQuantitySold = (int) (TransactionItem::sum('quantity') ?? 0);
        $completedReservations = (int) Reservation::where('status', 'completed')->count();
        $cancelledReservations = (int) Reservation::where('status', 'cancelled')->count();

        // Build popular products by category with sold units and current stock
        $popularByCategory = $this->getPopularProductsByCategory();

        return [
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'lowStockItems' => $lowStockItems,
            'todaySales' => $todaySales,
            'activeReservations' => $activeReservations,
            'totalQuantitySold' => $totalQuantitySold,
            'completedReservations' => $completedReservations,
            'cancelledReservations' => $cancelledReservations,
            'totalValue' => Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                          ->sum(DB::raw('products.price * product_sizes.stock')),
            // For the compact dashboard widgets
            'popularProducts' => $popularByCategory,
        ];
    }

    /**
     * Get top popular products per category with sold units and current stock.
     * Returns shape: [ 'men' => [ { name, sold, stock }, ... ], 'women' => [...], 'accessories' => [...] ]
     */
    private function getPopularProductsByCategory(): array
    {
        // Aggregate total sold per product
        $soldAgg = DB::table('transaction_items')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id');

        // Aggregate current stock per product
        $stockAgg = DB::table('product_sizes')
            ->select('product_id', DB::raw('SUM(stock) as total_stock'))
            ->groupBy('product_id');

        $rows = DB::table('products as p')
            ->leftJoinSub($soldAgg, 'sa', function ($join) {
                $join->on('sa.product_id', '=', 'p.id');
            })
            ->leftJoinSub($stockAgg, 'st', function ($join) {
                $join->on('st.product_id', '=', 'p.id');
            })
            ->where('p.is_active', true)
            ->select('p.id', 'p.name', 'p.category', DB::raw('COALESCE(sa.total_sold, 0) as sold'), DB::raw('COALESCE(st.total_stock, 0) as stock'))
            ->orderByDesc('sold')
            ->orderBy('p.name')
            ->get();

        $grouped = [ 'men' => [], 'women' => [], 'accessories' => [] ];
        foreach ($rows as $row) {
            $cat = strtolower((string)($row->category ?? ''));
            if (!isset($grouped[$cat])) continue; // ignore other categories
            $grouped[$cat][] = [
                'name' => $row->name,
                'sold' => (int) $row->sold,
                'stock' => (int) $row->stock,
            ];
        }

        // Limit to top N per category
        $limit = 5;
        foreach ($grouped as $key => $list) {
            usort($list, function ($a, $b) {
                if ($a['sold'] === $b['sold']) return strcmp($a['name'], $b['name']);
                return $b['sold'] <=> $a['sold'];
            });
            $grouped[$key] = array_slice($list, 0, $limit);
        }

        return $grouped;
    }

    /**
     * Get sales data for charts
     */
    private function getSalesData($period)
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        $query = Transaction::select(
            DB::raw("DATE($dateCol) as date"),
            DB::raw("SUM($amountExpr) as total"),
            DB::raw('COUNT(*) as transactions')
        );

        switch ($period) {
            case 'daily':
                $query->where($dateCol, '>=', Carbon::now()->subDays(7));
                break;
            case 'weekly':
                $query->where($dateCol, '>=', Carbon::now()->subWeeks(4));
                break;
            case 'monthly':
                $query->where($dateCol, '>=', Carbon::now()->subMonths(12));
                break;
            case 'yearly':
                $query->where($dateCol, '>=', Carbon::now()->subYears(3));
                break;
        }

        return $query->groupBy(DB::raw("DATE($dateCol)"))
                    ->orderBy('date')
                    ->get();
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts()
    {
        return Product::join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
                     ->select('products.name', DB::raw('SUM(transaction_items.quantity) as total_sold'))
                     ->groupBy('products.id', 'products.name')
                     ->orderBy('total_sold', 'desc')
                     ->limit(5)
                     ->get();
    }

    /**
     * Get recent sales transactions with required fields for the Sales History table.
     */
    private function getRecentSalesTransactions(string $period)
    {
        // Use created_at for datetime display (includes time), sale_date for date filtering only
        $dateCol = $this->salesDateColumn(); // sale_date or created_at - for filtering
        $cashierCol = Schema::hasColumn('transactions', 'cashier_id') ? 'cashier_id' : (Schema::hasColumn('transactions', 'user_id') ? 'user_id' : null);
        $totalCol = Schema::hasColumn('transactions', 'total_amount') ? 'total_amount' : (Schema::hasColumn('transactions', 'total') ? 'total' : null);
        $changeCol = Schema::hasColumn('transactions', 'change_given') ? 'change_given' : (Schema::hasColumn('transactions', 'change_amount') ? 'change_amount' : null);
        $discountExists = Schema::hasColumn('transactions', 'discount_amount');

        // Debug: Check if transactions table has any data
        $totalTransactions = DB::table('transactions')->count();
        Log::info("Total transactions in database: {$totalTransactions}");

        // Aggregate transaction items into a semicolon-separated list with quantities
        // Fixed: Ensure we're using the correct column name for transaction reference
        $itemsAgg = DB::table('transaction_items')
            ->select('transaction_id', DB::raw("GROUP_CONCAT(CONCAT(product_name, ' (', product_size, ') x', quantity) ORDER BY product_name SEPARATOR ', ') as products"))
            ->groupBy('transaction_id');

        $query = DB::table('transactions as s')
            ->leftJoin('users as u', function ($join) use ($cashierCol) {
                if ($cashierCol) {
                    $join->on('u.id', '=', DB::raw("s.$cashierCol"));
                }
            })
            ->leftJoinSub($itemsAgg, 'items_agg', function ($join) {
                $join->on('items_agg.transaction_id', '=', 's.transaction_id');
            })
            ->select([
                's.transaction_id',
                DB::raw("COALESCE(s.sale_type, 'pos') as sale_type"),
                DB::raw('COALESCE(u.name, "System") as cashier_name'),
                DB::raw(($discountExists ? 's.discount_amount' : '0') . ' as discount_amount'),
                DB::raw(($totalCol ? "s.$totalCol" : '0') . ' as total_amount'),
                's.amount_paid',
                DB::raw(($changeCol ? "s.$changeCol" : '0') . ' as change_given'),
                // Use created_at for display to get proper datetime with time
                DB::raw("s.created_at as sale_datetime"),
                DB::raw('COALESCE(items_agg.products, "No items") as products'),
            ]);

        // Time window filtering - use broader time ranges to ensure we catch transactions
        switch ($period) {
            case 'daily':
                $query->where('s.created_at', '>=', Carbon::now()->subDays(30)); // Expanded from 7 days
                break;
            case 'weekly':
                $query->where('s.created_at', '>=', Carbon::now()->subWeeks(12)); // Expanded from 4 weeks
                break;
            case 'monthly':
                $query->where('s.created_at', '>=', Carbon::now()->subMonths(24)); // Expanded from 12 months
                break;
            case 'yearly':
                $query->where('s.created_at', '>=', Carbon::now()->subYears(5)); // Expanded from 3 years
                break;
            default:
                // No time filter for debugging - show all transactions
                break;
        }

        $results = $query->orderByDesc('s.created_at') // Order by created_at for proper chronological order
            ->limit(200)
            ->get();

        // Debug logging
        Log::info("Sales history query results count: " . $results->count());
        Log::info("Date column used: {$dateCol}, Period: {$period}");
        if ($results->count() > 0) {
            Log::info("First transaction: " . json_encode($results->first()));
        }

        return $results;
    }

    /**
     * Create test transaction data if transactions table is empty
     */
    private function createTestTransactionData()
    {
        try {
            // Create a few test transactions
            $testTransactions = [
                [
                    'transaction_id' => 'TXN-TEST-001',
                    'sale_type' => 'pos',
                    'cashier_id' => '1',
                    'subtotal' => 5000.00,
                    'discount_amount' => 0.00,
                    'total_amount' => 5000.00,
                    'amount_paid' => 5000.00,
                    'change_given' => 0.00,
                    'sale_date' => now()->format('Y-m-d'),
                    'created_at' => now()->subHours(2),
                    'updated_at' => now()->subHours(2),
                ],
                [
                    'transaction_id' => 'TXN-TEST-002',
                    'sale_type' => 'pos',
                    'cashier_id' => '1',
                    'subtotal' => 3500.00,
                    'discount_amount' => 500.00,
                    'total_amount' => 3000.00,
                    'amount_paid' => 3000.00,
                    'change_given' => 0.00,
                    'sale_date' => now()->format('Y-m-d'),
                    'created_at' => now()->subHours(1),
                    'updated_at' => now()->subHours(1),
                ]
            ];

            foreach ($testTransactions as $transaction) {
                DB::table('transactions')->insert($transaction);
                
                // Create test transaction items
                $items = [
                    [
                        'transaction_id' => $transaction['transaction_id'],
                        'product_id' => 1,
                        'size_id' => 1,
                        'product_name' => 'Test Shoe',
                        'product_brand' => 'Test Brand',
                        'product_size' => '9',
                        'product_color' => 'Black',
                        'product_category' => 'men',
                        'sku' => 'TEST-001',
                        'quantity' => 1,
                        'size' => '9',
                        'unit_price' => $transaction['subtotal'],
                        'cost_price' => $transaction['subtotal'] * 0.6,
                        'subtotal' => $transaction['subtotal'],
                        'created_at' => $transaction['created_at'],
                        'updated_at' => $transaction['updated_at'],
                    ]
                ];

                foreach ($items as $item) {
                    DB::table('transaction_items')->insert($item);
                }
            }

            Log::info('Created test transaction data successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create test transaction data: ' . $e->getMessage());
        }
    }

    /**
     * Get reservation data
     */
    private function getReservationData($period)
    {
        $query = Reservation::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        );

        switch ($period) {
            case 'daily':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                break;
            case 'weekly':
                $query->where('created_at', '>=', Carbon::now()->subWeeks(4));
                break;
            case 'monthly':
                $query->where('created_at', '>=', Carbon::now()->subMonths(12));
                break;
            case 'yearly':
                $query->where('created_at', '>=', Carbon::now()->subYears(3));
                break;
        }

        return $query->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('date')
                    ->get();
    }

    /**
     * Get popular reserved products
     */
    private function getPopularReservedProducts()
    {
        return Product::join('reservations', 'products.id', '=', 'reservations.product_id')
                     ->select('products.name', DB::raw('COUNT(*) as reservation_count'))
                     ->groupBy('products.id', 'products.name')
                     ->orderBy('reservation_count', 'desc')
                     ->limit(5)
                     ->get();
    }

    /**
     * Popular products endpoint with optional range and exact month/year filters.
     * Query params:
     * - range: monthly|quarterly|yearly (default: monthly)
     * - month: 1-12 (used when range=monthly or to infer quarter when provided)
     * - year: four-digit year (defaults to current year)
     * - limit: number of items to return (default: 20)
     */
    public function popularProducts(Request $request)
    {
        try {
            $range = strtolower((string) $request->get('range', 'monthly'));
            $month = $request->integer('month');
            $year = $request->integer('year');
            $limit = max(1, min(100, (int) $request->get('limit', 20)));

            $now = Carbon::now();
            $y = $year ?: (int)$now->year;

            $start = null; $end = null;
            if ($month && $y && $range === 'monthly') {
                $start = Carbon::create($y, max(1, min(12, (int)$month)), 1)->startOfMonth();
                $end = (clone $start)->endOfMonth();
            } elseif ($range === 'quarterly') {
                // If a month is specified, infer its quarter; otherwise use current quarter of selected year
                $m = $month ?: (int)$now->month;
                $q = (int) ceil($m / 3);
                $qStartMonth = ($q - 1) * 3 + 1; // 1,4,7,10
                $start = Carbon::create($y, $qStartMonth, 1)->startOfMonth();
                $end = (clone $start)->addMonths(2)->endOfMonth();
            } elseif ($range === 'yearly') {
                $start = Carbon::create($y, 1, 1)->startOfYear();
                $end = (clone $start)->endOfYear();
            } else {
                // default monthly window for current month/year
                $start = Carbon::create($y, (int)($month ?: $now->month), 1)->startOfMonth();
                $end = (clone $start)->endOfMonth();
            }

            // Aggregate sold quantity per product within window using transactions schema
            $items = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween('t.created_at', [$start, $end])
                ->select(
                    DB::raw('COALESCE(ti.product_id, 0) as product_id'),
                    DB::raw('COALESCE(ti.product_name, "Unknown") as name'),
                    DB::raw('SUM(ti.quantity) as sold')
                )
                ->groupBy('product_id', 'name')
                ->orderByDesc('sold')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'range' => $range,
                'month' => $month,
                'year' => $y,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load popular products: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get supply data (for demonstration - you may need to create a supplies table)
     */
    private function getSupplyData($period)
    {
        // For now, returning mock data. You would implement actual supply tracking
        return collect([
            ['date' => '2024-01-01', 'supplies' => 50],
            ['date' => '2024-01-02', 'supplies' => 30],
            ['date' => '2024-01-03', 'supplies' => 75],
            ['date' => '2024-01-04', 'supplies' => 40],
            ['date' => '2024-01-05', 'supplies' => 60],
        ]);
    }

    /**
     * Get supplier statistics
     */
    private function getSupplierStats()
    {
        // Mock data - implement actual supplier tracking
        return [
            ['name' => 'Nike Supplier', 'supplies' => 120],
            ['name' => 'Adidas Distributor', 'supplies' => 98],
            ['name' => 'Puma Wholesale', 'supplies' => 85],
            ['name' => 'Local Supplier', 'supplies' => 67],
            ['name' => 'Import Co.', 'supplies' => 45],
        ];
    }

    /**
     * Get inventory value distribution
     */
    private function getInventoryValueDistribution()
    {
        return Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                     ->select(
                         'products.category',
                         DB::raw('SUM(products.price * product_sizes.stock) as total_value')
                     )
                     ->groupBy('products.category')
                     ->get();
    }

    /**
     * Determine the expression that calculates the sales total based on schema.
     */
    private function salesAmountExpression(): string
    {
        // If a native 'total' column exists, prefer it
        if (Schema::hasColumn('transactions', 'total')) {
            return 'total';
        }

        // Build expression from parts to be robust across schema changes
        $parts = [];
        if (Schema::hasColumn('transactions', 'subtotal')) {
            $parts[] = 'COALESCE(subtotal,0)';
        }
        if (Schema::hasColumn('transactions', 'tax')) {
            $parts[] = 'COALESCE(tax,0)';
        }
        $expr = count($parts) ? ('(' . implode(' + ', $parts) . ')') : '0';
        if (Schema::hasColumn('transactions', 'discount_amount')) {
            $expr = "($expr - COALESCE(discount_amount,0))";
        }
        return $expr;
    }

    /**
     * Choose the date column to use for sales time-based queries.
     */
    private function salesDateColumn(): string
    {
        return Schema::hasColumn('transactions', 'sale_date') ? 'sale_date' : 'created_at';
    }

    /**
     * Get profile picture URL with fallback to default
     */
    private function getProfilePictureUrl($user)
    {
        if (!$user->profile_picture) {
            return asset('assets/images/profile.png');
        }
        
        $profilePicturePath = public_path($user->profile_picture);
        if (!file_exists($profilePicturePath)) {
            return asset('assets/images/profile.png');
        }
        
        return asset($user->profile_picture);
    }
}