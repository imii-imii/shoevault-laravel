<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
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
        
        $salesData = $this->getSalesData($period);
        $topProducts = $this->getTopSellingProducts();
        // Fetch latest transactional rows with required columns
        $transactions = $this->getRecentSalesTransactions($period);

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
        $reservations = $query->limit(200)->get([
            'id',
            'reservation_id',
            'customer_name',
            'customer_email',
            'customer_phone',
            'pickup_date',
            'pickup_time',
            'status',
            'total_amount',
            'created_at',
        ]);

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
        $period = $request->get('period', 'weekly');
        
        $supplyData = $this->getSupplyData($period);
        $supplierStats = $this->getSupplierStats();
        
        return response()->json([
            'supplyData' => $supplyData,
            'supplierStats' => $supplierStats,
            'period' => $period
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

        if ($source === 'reservation') {
            // Reservation inventory: reservation_products + reservation_product_sizes
            $query = DB::table('reservation_products as p')
                ->join('reservation_product_sizes as s', 'p.id', '=', 's.reservation_product_id')
                ->select(
                    'p.id', 'p.product_id', 'p.name', 'p.brand', 'p.color', 'p.category', 'p.price', 'p.image_url',
                    DB::raw('COALESCE(SUM(s.stock),0) as total_stock'),
                    DB::raw("GROUP_CONCAT(DISTINCT s.size ORDER BY s.size SEPARATOR ', ') as sizes"),
                    DB::raw("GROUP_CONCAT(CONCAT(s.size, ':', COALESCE(s.stock,0)) ORDER BY s.size SEPARATOR ',') as sizes_stock")
                )
                ->where('p.is_active', true)
                ->where('s.is_available', true)
                ->groupBy('p.id', 'p.product_id', 'p.name', 'p.brand', 'p.color', 'p.category', 'p.price', 'p.image_url');

            if (!empty($category)) $query->where('p.category', $category);
            if (!empty($search)) {
                $like = "%$search%";
                $query->where(function ($q) use ($like) {
                    $q->where('p.name', 'like', $like)
                      ->orWhere('p.brand', 'like', $like)
                      ->orWhere('p.product_id', 'like', $like);
                });
            }

            $items = $query->orderBy('p.name')->get();
        } else {
            // POS inventory: products + product_sizes
            $query = DB::table('products as p')
                ->join('product_sizes as s', 'p.id', '=', 's.product_id')
                ->select(
                    'p.id', 'p.product_id', 'p.name', 'p.brand', 'p.color', 'p.category', 'p.price', 'p.image_url',
                    DB::raw('COALESCE(SUM(s.stock),0) as total_stock'),
                    DB::raw("GROUP_CONCAT(DISTINCT s.size ORDER BY s.size SEPARATOR ', ') as sizes"),
                    DB::raw("GROUP_CONCAT(CONCAT(s.size, ':', COALESCE(s.stock,0)) ORDER BY s.size SEPARATOR ',') as sizes_stock")
                )
                ->where('p.is_active', true)
                ->where('s.is_available', true)
                ->groupBy('p.id', 'p.product_id', 'p.name', 'p.brand', 'p.color', 'p.category', 'p.price', 'p.image_url');

            if (!empty($category)) $query->where('p.category', $category);
            if (!empty($search)) {
                $like = "%$search%";
                $query->where(function ($q) use ($like) {
                    $q->where('p.name', 'like', $like)
                      ->orWhere('p.brand', 'like', $like)
                      ->orWhere('p.product_id', 'like', $like);
                });
            }

            $items = $query->orderBy('p.name')->get();
        }

        // Normalize image URLs to absolute if stored as relative paths
        $items = $items->map(function ($row) {
            if (!empty($row->image_url) && !str_starts_with($row->image_url, 'http')) {
                $row->image_url = asset($row->image_url);
            }
            return $row;
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
        $todaySales = Sale::whereDate($dateCol, today())
            ->select(DB::raw("SUM($amountExpr) as total"))
            ->value('total') ?? 0;
        $activeReservations = Reservation::where('status', 'pending')->count();

        return [
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'lowStockItems' => $lowStockItems,
            'todaySales' => $todaySales,
            'activeReservations' => $activeReservations,
            'totalValue' => Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                          ->sum(DB::raw('products.price * product_sizes.stock'))
        ];
    }

    /**
     * Get sales data for charts
     */
    private function getSalesData($period)
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        $query = Sale::select(
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
        return Product::join('sale_items', 'products.id', '=', 'sale_items.product_id')
                     ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
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
        // Resolve dynamic column names
        $dateCol = $this->salesDateColumn(); // sale_date or created_at
        $cashierCol = Schema::hasColumn('sales', 'cashier_id') ? 'cashier_id' : (Schema::hasColumn('sales', 'user_id') ? 'user_id' : null);
        $totalCol = Schema::hasColumn('sales', 'total_amount') ? 'total_amount' : (Schema::hasColumn('sales', 'total') ? 'total' : null);
        $changeCol = Schema::hasColumn('sales', 'change_given') ? 'change_given' : (Schema::hasColumn('sales', 'change_amount') ? 'change_amount' : null);
        $discountExists = Schema::hasColumn('sales', 'discount_amount');

        // Aggregate sale items into a semicolon-separated list with quantities
        $itemsAgg = DB::table('sale_items')
            ->select('sale_id', DB::raw("GROUP_CONCAT(CONCAT(product_name, ' x', quantity) ORDER BY product_name SEPARATOR '; ') as products"))
            ->groupBy('sale_id');

        $query = DB::table('sales as s')
            ->leftJoin('users as u', function ($join) use ($cashierCol) {
                if ($cashierCol) {
                    $join->on('u.id', '=', DB::raw("s.$cashierCol"));
                }
            })
            ->leftJoinSub($itemsAgg, 'items_agg', function ($join) {
                $join->on('items_agg.sale_id', '=', 's.id');
            })
            ->select([
                's.transaction_id',
                DB::raw("COALESCE(s.sale_type, 'pos') as sale_type"),
                DB::raw('COALESCE(u.name, "System") as cashier_name'),
                DB::raw(($discountExists ? 's.discount_amount' : '0') . ' as discount_amount'),
                DB::raw(($totalCol ? "s.$totalCol" : '0') . ' as total_amount'),
                's.amount_paid',
                DB::raw(($changeCol ? "s.$changeCol" : '0') . ' as change_given'),
                DB::raw("s.$dateCol as sale_datetime"),
                DB::raw('COALESCE(items_agg.products, "") as products'),
            ]);

        // Time window similar to charts
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

        return $query->orderByDesc($dateCol)
            ->limit(200)
            ->get();
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
        if (Schema::hasColumn('sales', 'total')) {
            return 'total';
        }

        // Build expression from parts to be robust across schema changes
        $parts = [];
        if (Schema::hasColumn('sales', 'subtotal')) {
            $parts[] = 'COALESCE(subtotal,0)';
        }
        if (Schema::hasColumn('sales', 'tax')) {
            $parts[] = 'COALESCE(tax,0)';
        }
        $expr = count($parts) ? ('(' . implode(' + ', $parts) . ')') : '0';
        if (Schema::hasColumn('sales', 'discount_amount')) {
            $expr = "($expr - COALESCE(discount_amount,0))";
        }
        return $expr;
    }

    /**
     * Choose the date column to use for sales time-based queries.
     */
    private function salesDateColumn(): string
    {
        return Schema::hasColumn('sales', 'sale_date') ? 'sale_date' : 'created_at';
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
