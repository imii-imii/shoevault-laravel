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
        
        return response()->json([
            'salesData' => $salesData,
            'topProducts' => $topProducts,
            'period' => $period
        ]);
    }

    /**
     * Display reservation logs
     */
    public function reservationLogs(Request $request)
    {
        $period = $request->get('period', 'weekly');
        
        $reservationData = $this->getReservationData($period);
        $popularReservedProducts = $this->getPopularReservedProducts();
        
        return response()->json([
            'reservationData' => $reservationData,
            'popularProducts' => $popularReservedProducts,
            'period' => $period
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
    public function inventoryOverview()
    {
        $inventoryData = [
            'totalProducts' => Product::count(),
            'totalStock' => Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                          ->sum('product_sizes.stock'),
            'lowStockItems' => Product::join('product_sizes', 'products.id', '=', 'product_sizes.product_id')
                            ->where('product_sizes.stock', '<=', 10)
                            ->count(),
            'categories' => Product::select('category', DB::raw('count(*) as total'))
                         ->groupBy('category')
                         ->get(),
            'brands' => Product::select('brand', DB::raw('count(*) as total'))
                      ->groupBy('brand')
                      ->get(),
            'valueDistribution' => $this->getInventoryValueDistribution()
        ];
        
        return response()->json($inventoryData);
    }

    /**
     * Display settings/master controls
     */
    public function settings()
    {
        $systemStats = [
            'users' => User::count(),
            'products' => Product::count(),
            'sales' => Sale::count(),
            'reservations' => Reservation::count(),
        ];
        
        return response()->json($systemStats);
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
        $todaySales = Sale::whereDate('created_at', today())->sum('total');
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
        $query = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total'),
            DB::raw('COUNT(*) as transactions')
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
}
