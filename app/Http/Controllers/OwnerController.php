<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
        // New filtering parameters replacing period-based filtering
        $month = $request->get('month'); // format YYYY-MM
        $date = $request->get('date');   // format YYYY-MM-DD (takes precedence over month)
    $page = max(1, (int) $request->get('page', 1));
    $perPage = min(100, max(10, (int) $request->get('per_page', 25))); // clamp per_page
    $type = strtolower((string) $request->get('type', 'all'));
        
        // Check if we have any transactions and create test data if empty
        $transactionCount = DB::table('transactions')->count();
        if ($transactionCount === 0) {
            Log::info('No transactions found, creating test data');
            $this->createTestTransactionData();
        }
        
        // Chart data still uses broader range; optionally narrowed by month or date if provided
        $salesData = $this->getSalesDataForFilters($month, $date);
        $topProducts = $this->getTopSellingProducts();
    $transactionsResult = $this->getFilteredSalesTransactions($month, $date, $page, $perPage, $type);

        Log::info("Returning sales history data - transactions count: " . count($transactionsResult['data']));

        return response()->json([
            'salesData' => $salesData,
            'topProducts' => $topProducts,
            'transactions' => $transactionsResult['data'],
            'filters' => [
                'month' => $month,
                'date' => $date,
                'type' => $type,
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $transactionsResult['total'],
                'total_pages' => $transactionsResult['total_pages']
            ]
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
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(10, (int) $request->get('per_page', 25)));

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

        // Clone for total count
        $total = (clone $query)->count();

        // Fetch records with pagination
        $reservations = $query->with('customer')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

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
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ]
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
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(10, (int) $request->get('per_page', 25)));

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

        // Total count for pagination
        $total = (clone $query)->count();

        // Fetch page slice
        $logs = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

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
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ]
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
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(10, (int) $request->get('per_page', 25)));

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

        $total = (clone $query)->count();
        $products = $query->orderBy('name')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Transform products to match the expected format
        $items = $products->map(function ($product) {
            $sizes = $product->sizes->pluck('size')->sort()->implode(', ');
            $sizesStock = $product->sizes->map(function ($size) {
                return $size->size . ':' . $size->stock;
            })->implode(',');
            $totalStock = $product->sizes->sum('stock');

            return (object) [
                'id' => $product->product_id,
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
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ]
        ]);
    }

    /**
     * Display settings/master controls
     */
    public function settings()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        return view('owner.settings', [
            'userIndexRoute' => route('owner.users.index'),
            'userStoreRoute' => route('owner.users.store'),
            'userToggleRoute' => route('owner.users.toggle'),
            'customerIndexRoute' => route('owner.customers.index'),
            'customerToggleRoute' => route('owner.customers.toggle'),
            'employee' => $employee,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            // Debug: Log the incoming request data
            Log::info('Profile update request data:', [
                'all_data' => $request->all(),
                'hasFile' => $request->hasFile('profile_picture'),
                'fileSize' => $request->hasFile('profile_picture') ? $request->file('profile_picture')->getSize() : 'N/A',
                'mimeType' => $request->hasFile('profile_picture') ? $request->file('profile_picture')->getMimeType() : 'N/A',
                'originalName' => $request->hasFile('profile_picture') ? $request->file('profile_picture')->getClientOriginalName() : 'N/A',
                'phpUploadMaxFilesize' => ini_get('upload_max_filesize'),
                'phpPostMaxSize' => ini_get('post_max_size'),
                'requestContentLength' => $request->header('Content-Length'),
            ]);
            
            // Step-by-step validation to isolate the issue
            $rules = [
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->user_id . ',user_id',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
            ];
            
            // Only add profile picture validation if file is present
            if ($request->hasFile('profile_picture')) {
                $rules['profile_picture'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            }
            
            Log::info('Validation rules:', $rules);
            
            $validated = $request->validate($rules);
            
            Log::info('Validation passed, validated data:', $validated);
            
            // Get or create employee record
            $employee = $user->employee;
            if (!$employee) {
                $employee = Employee::create([
                    'user_id' => $user->user_id,
                    'fullname' => $validated['name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'position' => $user->role,
                    'hire_date' => now(),
                ]);
            }
            
            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                Log::info('Processing profile picture upload');
                
                // Delete old profile picture if it exists
                if ($employee->profile_picture && file_exists(public_path($employee->profile_picture))) {
                    unlink(public_path($employee->profile_picture));
                    Log::info('Deleted old profile picture: ' . $employee->profile_picture);
                }
                
                try {
                    // Upload new profile picture
                    $file = $request->file('profile_picture');
                    
                    // Get file extension, with fallback
                    $extension = $file->getClientOriginalExtension();
                    if (empty($extension)) {
                        // Fallback to guessing extension from MIME type
                        $mimeType = $file->getMimeType();
                        switch ($mimeType) {
                            case 'image/jpeg':
                                $extension = 'jpg';
                                break;
                            case 'image/png':
                                $extension = 'png';
                                break;
                            case 'image/gif':
                                $extension = 'gif';
                                break;
                            case 'image/webp':
                                $extension = 'webp';
                                break;
                            default:
                                $extension = 'jpg'; // Default fallback
                        }
                    }
                    
                    $fileName = 'profile_' . $user->user_id . '_' . time() . '.' . $extension;
                    $relativePath = 'assets/images/profiles/' . $fileName;
                    
                    Log::info('File details:', [
                        'originalName' => $file->getClientOriginalName(),
                        'extension' => $extension,
                        'mimeType' => $file->getMimeType(),
                        'fileName' => $fileName
                    ]);
                    
                    // Create directory if it doesn't exist
                    $directory = public_path('assets/images/profiles');
                    if (!is_dir($directory)) {
                        if (!mkdir($directory, 0755, true)) {
                            throw new \Exception('Failed to create upload directory');
                        }
                        Log::info('Created directory: ' . $directory);
                    }
                    
                    // Move the uploaded file
                    if (!$file->move($directory, $fileName)) {
                        throw new \Exception('Failed to move uploaded file');
                    }
                    
                    $fullPath = $directory . DIRECTORY_SEPARATOR . $fileName;
                    
                    Log::info('File upload successful:', [
                        'fileName' => $fileName,
                        'relativePath' => $relativePath,
                        'fullPath' => $fullPath,
                        'fileExists' => file_exists($fullPath),
                        'fileSize' => file_exists($fullPath) ? filesize($fullPath) : 'N/A'
                    ]);
                    
                    $validated['profile_picture'] = $relativePath;
                    
                } catch (\Exception $uploadException) {
                    Log::error('Profile picture upload failed:', [
                        'error' => $uploadException->getMessage(),
                        'file_size' => $file->getSize(),
                        'file_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload profile picture: ' . $uploadException->getMessage()
                    ], 500);
                }
            }

            // Update user username
            $user->update(['username' => $validated['username']]);
            
            // Update employee profile data
            $updateData = [
                'fullname' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone'],
            ];
            
            // Only update profile picture if a new one was uploaded
            if (isset($validated['profile_picture'])) {
                $updateData['profile_picture'] = $validated['profile_picture'];
            }
            
            $employee->update($updateData);
            
            // Refresh the employee model to get updated data
            $employee->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => [
                    'name' => $employee->fullname,
                    'username' => $user->username,
                    'email' => $employee->email,
                    'phone' => $employee->phone_number,
                    'profile_picture' => $this->getProfilePictureUrl($employee) . '?t=' . time() // Cache busting
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->implode(', '),
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
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee profile not found!'
                ], 404);
            }
            
            // Delete old profile picture if it exists
            if ($employee->profile_picture && file_exists(public_path($employee->profile_picture))) {
                unlink(public_path($employee->profile_picture));
            }
            
            // Update employee record to remove profile picture
            $employee->update(['profile_picture' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture removed successfully!',
                'user' => [
                    'name' => $employee->fullname,
                    'username' => $user->username,
                    'email' => $employee->email,
                    'phone' => $employee->phone_number,
                    'profile_picture' => $this->getProfilePictureUrl($employee)
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
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string'
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
                'password' => Hash::make($validated['password'])
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
     * Get dashboard data for specific date range (API endpoint)
     */
    public function getDashboardData(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $range = $request->input('range', 'day');
            
            // Validate dates
            if (!$startDate || !$endDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Start date and end date are required'
                ], 400);
            }
            
            $data = $this->getDashboardKPIsByDateRange($startDate, $endDate, $range);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard data fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data'
            ], 500);
        }
    }

    /**
     * Get dashboard KPIs for default view (today's data)
     */
    private function getDashboardKPIs()
    {
        $rangeData = $this->getDashboardKPIsByDateRange(
            today()->toDateString(),
            today()->toDateString(),
            'day'
        );
        
        // Return data in the format expected by the frontend for initial load
        return [
            'totalProducts' => Product::count(),
            'totalStock' => Product::join('product_sizes', 'products.product_id', '=', 'product_sizes.product_id')
                          ->sum('product_sizes.stock'),
            'lowStockItems' => Product::join('product_sizes', 'products.product_id', '=', 'product_sizes.product_id')
                            ->where('product_sizes.stock', '<=', 10)
                            ->count(),
            'todaySales' => $rangeData['kpis']['revenue'],
            'activeReservations' => $rangeData['kpis']['pending_reservations'],
            'totalQuantitySold' => $rangeData['kpis']['products_sold'],
            'completedReservations' => $rangeData['kpis']['completed_reservations'],
            'cancelledReservations' => $rangeData['kpis']['cancelled_reservations'],
            'totalValue' => Product::join('product_sizes', 'products.product_id', '=', 'product_sizes.product_id')
                          ->sum(DB::raw('products.price * product_sizes.stock')),
            'popularProducts' => $this->getPopularProductsByCategory(),
        ];
    }

    /**
     * Get dashboard KPIs for specific date range
     */
    private function getDashboardKPIsByDateRange($startDate, $endDate, $range)
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        
        // Calculate sales for the date range
        $sales = Transaction::whereBetween($dateCol, [$startDate, $endDate])
            ->select(DB::raw("SUM($amountExpr) as total"))
            ->value('total') ?? 0;
            
        // Calculate products sold in the date range
        $productsSold = TransactionItem::whereHas('transaction', function($query) use ($dateCol, $startDate, $endDate) {
            $query->whereBetween($dateCol, [$startDate, $endDate]);
        })->sum('quantity') ?? 0;
        
        // Calculate reservations for the date range
        $completedReservations = Reservation::where('status', 'completed')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();
            
        $cancelledReservations = Reservation::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();
            
        $pendingReservations = Reservation::where('status', 'pending')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->count();
        
        // Generate forecast data based on range
        $forecast = $this->generateForecastData($startDate, $endDate, $range);
        
        // Get popular products for the date range
        $popularProducts = $this->getPopularProductsForDateRange($startDate, $endDate);
        
        return [
            'kpis' => [
                'revenue' => (float) $sales,
                'products_sold' => (int) $productsSold,
                'completed_reservations' => (int) $completedReservations,
                'cancelled_reservations' => (int) $cancelledReservations,
                'pending_reservations' => (int) $pendingReservations,
            ],
            'forecast' => $forecast,
            'popular_products' => $popularProducts,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate,
                'range' => $range
            ]
        ];
    }

    /**
     * Generate forecast data for chart
     */
    private function generateForecastData($startDate, $endDate, $range)
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        
        $labels = [];
        $posData = [];
        $reservationData = [];
        
        if ($range === 'day') {
            // Hourly data for the day
            for ($hour = 9; $hour <= 20; $hour++) {
                $hourStart = $startDate . ' ' . sprintf('%02d:00:00', $hour);
                $hourEnd = $startDate . ' ' . sprintf('%02d:59:59', $hour);
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posRevenue = Transaction::whereBetween($dateCol, [$hourStart, $hourEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    })
                    ->select(DB::raw("SUM($amountExpr) as total"))
                    ->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvRevenue = Transaction::whereBetween($dateCol, [$hourStart, $hourEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    })
                    ->select(DB::raw("SUM($amountExpr) as total"))
                    ->value('total') ?? 0;
                
                $labels[] = $hour <= 12 ? $hour . ' AM' : ($hour - 12) . ' PM';
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
            }
        } else if ($range === 'weekly') {
            // Daily data for the week
            $current = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            
            while ($current->lte($end)) {
                $dayStart = $current->toDateString() . ' 00:00:00';
                $dayEnd = $current->toDateString() . ' 23:59:59';
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posRevenue = Transaction::whereBetween($dateCol, [$dayStart, $dayEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    })
                    ->select(DB::raw("SUM($amountExpr) as total"))
                    ->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvRevenue = Transaction::whereBetween($dateCol, [$dayStart, $dayEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    })
                    ->select(DB::raw("SUM($amountExpr) as total"))
                    ->value('total') ?? 0;
                
                $labels[] = $current->format('M d');
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addDay();
            }
        } else {
            // Weekly data for the month
            $current = Carbon::parse($startDate)->startOfWeek();
            $end = Carbon::parse($endDate)->endOfWeek();
            
            while ($current->lte($end)) {
                $weekStart = $current->toDateString() . ' 00:00:00';
                $weekEnd = $current->copy()->endOfWeek()->toDateString() . ' 23:59:59';
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posRevenue = Transaction::whereBetween($dateCol, [$weekStart, $weekEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    })
                    ->select(DB::raw("SUM($amountExpr) as total"))
                    ->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvRevenue = Transaction::whereBetween($dateCol, [$weekStart, $weekEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    })
                    ->select(DB::raw("SUM($amountExpr) as total"))
                    ->value('total') ?? 0;
                
                $labels[] = 'Week ' . $current->week;
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addWeek();
            }
        }
        
        return [
            'labels' => $labels,
            'pos' => $posData,
            'reservation' => $reservationData
        ];
    }

    /**
     * Get popular products for specific date range
     */
    private function getPopularProductsForDateRange($startDate, $endDate)
    {
        $dateCol = $this->salesDateColumn();
        
        // Get products sold in the date range using product_name directly from transaction_items
        $popularProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.transaction_id')
            ->select(
                'transaction_items.product_name as name',
                DB::raw('LOWER(COALESCE(transaction_items.product_category, "")) as category'),
                DB::raw('SUM(transaction_items.quantity) as total_sold')
            )
            ->whereBetween("transactions.{$dateCol}", [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('transaction_items.product_name', 'transaction_items.product_category')
            ->orderBy('total_sold', 'desc')
            ->limit(12)
            ->get();

        return $popularProducts->map(function($product) {
            return [
                'name' => $product->name,
                'category' => (string) $product->category,
                'sales' => (int) $product->total_sold
            ];
        })->toArray();
    }

    /**
     * Get current stock levels for all products
     */
    public function getStockLevels(Request $request)
    {
        try {
            $source = $request->get('source', 'pos'); // 'pos' or 'reservation'
            $category = $request->get('category');
            
            $query = Product::select([
                'products.name',
                'products.brand',
                'products.color',
                'products.category',
                DB::raw('SUM(product_sizes.stock) as total_stock')
            ])
            ->join('product_sizes', 'products.product_id', '=', 'product_sizes.product_id');
            
            // Filter by inventory type (pos/reservation)
            if ($source === 'pos') {
                $query->where('products.inventory_type', 'pos');
            } else {
                $query->where('products.inventory_type', 'reservation');
            }
            
            // Filter by category if specified and not "all"
            if ($category && $category !== 'all') {
                $query->where('products.category', $category);
            }
            
            $stockLevels = $query->groupBy(
                'products.product_id', 
                'products.name', 
                'products.brand', 
                'products.color', 
                'products.category'
            )
            ->orderBy('total_stock', 'desc')
            ->get();
            
            return response()->json([
                'success' => true,
                'items' => $stockLevels->map(function($item) {
                    $stock = (int) $item->total_stock;
                    $status = 'good';
                    if ($stock <= 5) {
                        $status = 'critical';
                    } elseif ($stock <= 15) {
                        $status = 'low';
                    } elseif ($stock <= 30) {
                        $status = 'medium';
                    }
                    
                    return [
                        'name' => $item->name,
                        'brand' => $item->brand,
                        'color' => $item->color,
                        'category' => $item->category,
                        'total_stock' => $stock,
                        'status' => $status
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Stock levels fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stock levels'
            ], 500);
        }
    }

    /**
     * Get top popular products per category with sold units and current stock.
     * Returns shape: [ 'men' => [ { name, sold, stock }, ... ], 'women' => [...], 'accessories' => [...] ]
     */
    private function getPopularProductsByCategory(): array
    {
        // Aggregate total sold per product name (since we no longer have product_id in transaction_items)
        $soldAgg = DB::table('transaction_items')
            ->select('product_name', 'product_category', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_name', 'product_category');

        // Aggregate current stock per product
        $stockAgg = DB::table('product_sizes')
            ->select('product_id', DB::raw('SUM(stock) as total_stock'))
            ->groupBy('product_id');

        $rows = DB::table('products as p')
            ->leftJoinSub($soldAgg, 'sa', function ($join) {
                $join->on('sa.product_name', '=', 'p.name')
                     ->on('sa.product_category', '=', 'p.category');
            })
            ->leftJoinSub($stockAgg, 'st', function ($join) {
                $join->on('st.product_id', '=', 'p.product_id');
            })
            ->where('p.is_active', true)
            ->select('p.product_id', 'p.name', 'p.category', DB::raw('COALESCE(sa.total_sold, 0) as sold'), DB::raw('COALESCE(st.total_stock, 0) as stock'))
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
     * New chart sales data builder honoring optional month/date filters.
     */
    private function getSalesDataForFilters(?string $month, ?string $date)
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        $query = Transaction::select(
            DB::raw("DATE($dateCol) as date"),
            DB::raw("SUM($amountExpr) as total"),
            DB::raw('COUNT(*) as transactions')
        );

        if ($date) {
            // Specific date
            $query->whereDate($dateCol, $date);
        } elseif ($month) {
            // Month filter YYYY-MM
            try {
                [$y,$m] = explode('-', $month);
                $start = Carbon::createFromDate((int)$y,(int)$m,1)->startOfDay();
                $end = (clone $start)->endOfMonth();
                $query->whereBetween($dateCol, [$start, $end]);
            } catch (\Throwable $e) {
                // Fallback: last 3 months if parse fails
                $query->where($dateCol, '>=', Carbon::now()->subMonths(3));
            }
        } else {
            // Default: last 6 months for chart readability
            $query->where($dateCol, '>=', Carbon::now()->subMonths(6));
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
        return DB::table('transaction_items')
                     ->select('product_name as name', DB::raw('SUM(quantity) as total_sold'))
                     ->groupBy('product_name')
                     ->orderBy('total_sold', 'desc')
                     ->limit(5)
                     ->get();
    }

    /**
     * Get recent sales transactions with required fields for the Sales History table.
     */
    private function getFilteredSalesTransactions(?string $month, ?string $date, int $page, int $perPage, string $type = 'all')
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
            ->select('transaction_id', DB::raw("GROUP_CONCAT(CONCAT(product_name, ' (', size, ') x', quantity) ORDER BY product_name SEPARATOR ', ') as products"))
            ->groupBy('transaction_id');

        $query = DB::table('transactions as s')
            ->leftJoin('users as u', function ($join) use ($cashierCol) {
                if ($cashierCol) {
                    // Join to users using the correct PK 'user_id' (not 'id')
                    $join->on('u.user_id', '=', DB::raw("s.$cashierCol"));
                }
            })
            ->leftJoinSub($itemsAgg, 'items_agg', function ($join) {
                $join->on('items_agg.transaction_id', '=', 's.transaction_id');
            })
            ->select([
                's.transaction_id',
                DB::raw("COALESCE(s.sale_type, 'pos') as sale_type"),
                // Prefer username since users table may not have a 'name' column
                DB::raw('COALESCE(u.username, "System") as cashier_name'),
                DB::raw(($discountExists ? 's.discount_amount' : '0') . ' as discount_amount'),
                DB::raw(($totalCol ? "s.$totalCol" : '0') . ' as total_amount'),
                's.amount_paid',
                DB::raw(($changeCol ? "s.$changeCol" : '0') . ' as change_given'),
                // Use created_at for display to get proper datetime with time
                DB::raw("s.created_at as sale_datetime"),
                DB::raw('COALESCE(items_agg.products, "No items") as products'),
            ]);

        // Date filtering logic: specific date > month > default (last 12 months)
        if ($date) {
            $query->whereDate('s.created_at', $date);
        } elseif ($month) {
            try {
                [$y,$m] = explode('-', $month);
                $start = Carbon::createFromDate((int)$y,(int)$m,1)->startOfDay();
                $end = (clone $start)->endOfMonth();
                $query->whereBetween('s.created_at', [$start, $end]);
            } catch (\Throwable $e) {
                $query->where('s.created_at', '>=', Carbon::now()->subMonths(12));
            }
        } else {
            $query->where('s.created_at', '>=', Carbon::now()->subMonths(12));
        }

        // Sale type filter (pos/reservation). Default 'all' means no filtering.
        if (!empty($type) && $type !== 'all') {
            $query->where('s.sale_type', '=', $type);
        }

        // Total for pagination
        $total = (clone $query)->count();
        $totalPages = (int) ceil($total / $perPage);
        $page = min(max(1,$page), max(1,$totalPages));
        $offset = ($page - 1) * $perPage;

        $results = $query->orderByDesc('s.created_at')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Debug logging
        Log::info("Sales history query results count: " . $results->count());
        Log::info("Date column used: {$dateCol}, Filters month={$month} date={$date}");
        if ($results->count() > 0) {
            Log::info("First transaction: " . json_encode($results->first()));
        }

        return [
            'data' => $results,
            'total' => $total,
            'total_pages' => $totalPages,
        ];
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
                        'product_size_id' => 1, // Updated to use product_size_id
                        'product_name' => 'Test Shoe',
                        'product_brand' => 'Test Brand',
                        'product_color' => 'Black',
                        'product_category' => 'men',
                        'quantity' => 1,
                        'size' => '9',
                        'unit_price' => $transaction['subtotal'],
                        'cost_price' => $transaction['subtotal'] * 0.6,
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
        return Product::join('reservations', 'products.product_id', '=', 'reservations.product_id')
                     ->select('products.name', DB::raw('COUNT(*) as reservation_count'))
                     ->groupBy('products.product_id', 'products.name')
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
            $category = strtolower((string) $request->get('category', 'all'));

            $now = Carbon::now();
            $y = $year ?: (int)$now->year;

            // Optional explicit start/end override for custom windows (supports day/weekly)
            $startParam = $request->get('start');
            $endParam = $request->get('end');

            $start = null; $end = null;
            if ($startParam && $endParam) {
                // Validate and use explicit window
                try {
                    $start = Carbon::parse($startParam)->startOfDay();
                    $end = Carbon::parse($endParam)->endOfDay();
                    if ($end->lt($start)) {
                        // swap if reversed
                        [$start, $end] = [$end, $start];
                    }
                    // Normalize range label for response
                    $range = in_array($range, ['day','weekly','monthly','quarterly','yearly']) ? $range : 'custom';
                } catch (\Exception $e) {
                    // fall back to computed windows below if parsing fails
                    $start = null; $end = null;
                }
            }

            if (!$start || !$end) {
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
                } elseif ($range === 'day') {
                    $ref = Carbon::create($y, (int)($month ?: $now->month), (int)$now->day);
                    $start = (clone $ref)->startOfDay();
                    $end = (clone $ref)->endOfDay();
                } elseif ($range === 'weekly') {
                    $ref = Carbon::create($y, (int)($month ?: $now->month), (int)$now->day);
                    $start = (clone $ref)->startOfWeek();
                    $end = (clone $ref)->endOfWeek();
                } else {
                    // default monthly window for current month/year
                    $start = Carbon::create($y, (int)($month ?: $now->month), 1)->startOfMonth();
                    $end = (clone $start)->endOfMonth();
                }
            }

            // Aggregate sold quantity per product within window using transactions schema
            $itemsQuery = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween('t.created_at', [$start, $end])
                ->select(
                    DB::raw('COALESCE(ti.product_name, "Unknown") as name'),
                    DB::raw('LOWER(COALESCE(ti.product_category, "")) as category'),
                    DB::raw('SUM(ti.quantity) as sold')
                )
                ->groupBy('product_name', 'ti.product_category');

            if (!empty($category) && $category !== 'all') {
                $itemsQuery->whereRaw('LOWER(COALESCE(ti.product_category, "")) = ?', [$category]);
            }

            $items = $itemsQuery->orderByDesc('sold')->limit($limit)->get();

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
        return Product::join('product_sizes', 'products.product_id', '=', 'product_sizes.product_id')
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
        // Prefer modern column names first
        if (Schema::hasColumn('transactions', 'total_amount')) {
            return 'total_amount';
        }
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
    private function getProfilePictureUrl($profileObject)
    {
        if (!$profileObject->profile_picture) {
            return asset('assets/images/profile.png');
        }
        
        $profilePicturePath = public_path($profileObject->profile_picture);
        if (!file_exists($profilePicturePath)) {
            return asset('assets/images/profile.png');
        }
        
        return asset($profileObject->profile_picture);
    }
}