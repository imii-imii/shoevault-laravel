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
                    ->orWhereHas('customer', function ($customer) use ($q) {
                        $customer->where('fullname', 'like', $q)
                                 ->orWhere('email', 'like', $q)
                                 ->orWhere('phone_number', 'like', $q);
                    });
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
            // Only include sizes that have stock > 0
            $availableSizes = $product->sizes->where('stock', '>', 0);
            $sizes = $availableSizes->pluck('size')->sort()->implode(', ');
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
            $brand = $request->input('brand', 'all');
            

            // Validate dates
            if (!$startDate || !$endDate) {
                Log::warning('getDashboardData: Missing required dates', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Start date and end date are required'
                ], 400);
            }
            
            $data = $this->getDashboardKPIsByDateRange($startDate, $endDate, $range, $brand);
            
            // Debug: Log the final response data
            Log::info('getDashboardData response', [
                'kpis' => $data['kpis'] ?? null,
                'forecast_data_count' => isset($data['forecast']) ? count($data['forecast']) : 0
            ]);
            
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
        // Get current date in local timezone for initial dashboard load
        $currentDate = now()->toDateString();
        

        
        $rangeData = $this->getDashboardKPIsByDateRange(
            $currentDate,
            $currentDate,
            'day',
            'all'
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
            'popularProducts' => $rangeData['popular_products'],
        ];
    }

    /**
     * Apply brand filter to transaction query if brand is specified
     */
    private function applyBrandFilter($query, $brand)
    {
        if ($brand && $brand !== 'all') {
            return $query->whereHas('items', function($subQuery) use ($brand) {
                $subQuery->where('product_brand', $brand);
            });
        }
        return $query;
    }

    /**
     * Get dashboard KPIs for specific date range
     */
    private function getDashboardKPIsByDateRange($startDate, $endDate, $range, $brand = 'all')
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        
        // Debug logging
        Log::info('Dashboard KPI Query Debug', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'range' => $range,
            'date_column' => $dateCol,
            'amount_expression' => $amountExpr
        ]);
        
        // Check if we have any data at all
        $totalTransactionsCount = Transaction::count();
        $totalTransactionItemsCount = TransactionItem::count();
        $totalReservationsCount = Reservation::count();
        
        // Check what the actual date formats are in the database
        $recentTransactions = Transaction::orderBy($dateCol, 'desc')->limit(3)
            ->select('transaction_id', $dateCol, $amountExpr)
            ->get();
        
        Log::info('Database Records Count', [
            'total_transactions' => $totalTransactionsCount,
            'total_transaction_items' => $totalTransactionItemsCount,
            'total_reservations' => $totalReservationsCount,
            'recent_transactions_sample' => $recentTransactions->toArray()
        ]);
        
        // Calculate sales for the date range
        // Handle timestamp columns properly by converting dates to full datetime ranges
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';
        
        $salesQuery = Transaction::whereBetween($dateCol, [$startDateTime, $endDateTime]);
        $salesQuery = $this->applyBrandFilter($salesQuery, $brand);
        
        $totalTransactions = $salesQuery->count();
        $sales = $salesQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
        
        // Debug: Check what data exists in the date range
        $sampleTransactions = Transaction::whereBetween($dateCol, [$startDateTime, $endDateTime])
            ->select('transaction_id', $dateCol, $amountExpr)
            ->limit(5)
            ->get();
        
        Log::info('Sales Query Results', [
            'total_transactions' => $totalTransactions,
            'total_sales' => $sales,
            'sample_transactions' => $sampleTransactions->toArray(),
            'query_date_range' => [$startDateTime, $endDateTime],
            'original_date_range' => [$startDate, $endDate],
            'date_column_used' => $dateCol
        ]);
            
        // Calculate products sold in the date range
        $productsSoldQuery = TransactionItem::whereHas('transaction', function($query) use ($dateCol, $startDateTime, $endDateTime) {
            $query->whereBetween($dateCol, [$startDateTime, $endDateTime]);
        });
        
        // Apply brand filter to products sold
        if ($brand && $brand !== 'all') {
            $productsSoldQuery = $productsSoldQuery->where('product_brand', $brand);
        }
        
        $productsSold = $productsSoldQuery->sum('quantity') ?? 0;
        
        Log::info('Products Sold Query Results', [
            'products_sold' => $productsSold
        ]);
        
        // Calculate reservations for the date range
        // Completed reservations = transactions with sale_type = 'reservation' (reservations that were fulfilled/sold)
        $completedReservationsQuery = Transaction::where('sale_type', 'reservation')
            ->whereBetween($dateCol, [$startDateTime, $endDateTime]);
        $completedReservationsQuery = $this->applyBrandFilter($completedReservationsQuery, $brand);
        
        $completedReservations = $completedReservationsQuery->count();
            
        // Cancelled reservations = reservations with status = 'cancelled' 
        $cancelledReservationsQuery = Reservation::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
        
        // Apply brand filter to cancelled reservations if brand is specified (JSON search)
        if ($brand && $brand !== 'all') {
            $cancelledReservationsQuery = $cancelledReservationsQuery->whereRaw(
                "JSON_SEARCH(items, 'one', ?, NULL, '$[*].brand') IS NOT NULL", 
                [$brand]
            );
        }
        
        $cancelledReservations = $cancelledReservationsQuery->count();
            
        // Pending reservations = reservations with status = 'pending'
        $pendingReservationsQuery = Reservation::where('status', 'pending')
            ->whereBetween('created_at', [$startDateTime, $endDateTime]);
            
        // Apply brand filter to pending reservations if brand is specified (JSON search)
        if ($brand && $brand !== 'all') {
            $pendingReservationsQuery = $pendingReservationsQuery->whereRaw(
                "JSON_SEARCH(items, 'one', ?, NULL, '$[*].brand') IS NOT NULL", 
                [$brand]
            );
        }
        
        $pendingReservations = $pendingReservationsQuery->count();
            
        Log::info('Reservation Query Results', [
            'completed_reservations' => $completedReservations . ' (transactions with sale_type=reservation)',
            'cancelled_reservations' => $cancelledReservations . ' (reservations with status=cancelled)',
            'pending_reservations' => $pendingReservations . ' (reservations with status=pending)'
        ]);
        
        // Generate forecast data based on range
        $forecast = $this->generateForecastData($startDate, $endDate, $range, $brand);
        
        // Get popular products for the date range
        $popularProducts = $this->getPopularProductsForDateRange($startDate, $endDate, $brand);
        
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
    private function generateForecastData($startDate, $endDate, $range, $brand = 'all')
    {
        $dateCol = $this->salesDateColumn();
        $amountExpr = $this->salesAmountExpression();
        
        $labels = [];
        $posData = [];
        $reservationData = [];
        
        if ($range === 'day') {
            // Hourly data for the day (Business hours: 10am - 7pm)
            for ($hour = 10; $hour <= 19; $hour++) {
                $hourStart = $startDate . ' ' . sprintf('%02d:00:00', $hour);
                $hourEnd = $startDate . ' ' . sprintf('%02d:59:59', $hour);
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posQuery = Transaction::whereBetween($dateCol, [$hourStart, $hourEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    });
                $posQuery = $this->applyBrandFilter($posQuery, $brand);
                $posRevenue = $posQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvQuery = Transaction::whereBetween($dateCol, [$hourStart, $hourEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    });
                $resvQuery = $this->applyBrandFilter($resvQuery, $brand);
                $resvRevenue = $resvQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                
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
                $posQuery = Transaction::whereBetween($dateCol, [$dayStart, $dayEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    });
                $posQuery = $this->applyBrandFilter($posQuery, $brand);
                $posRevenue = $posQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvQuery = Transaction::whereBetween($dateCol, [$dayStart, $dayEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    });
                $resvQuery = $this->applyBrandFilter($resvQuery, $brand);
                $resvRevenue = $resvQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                
                // Generate proper day labels (e.g., "Mon", "Tue", "Wed")
                $labels[] = $current->format('D');
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addDay();
            }
        } else if ($range === 'monthly') {
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
                
                // Generate proper week labels (e.g., "Week 1", "Week 2")
                $weekOfMonth = $current->weekOfMonth;
                $labels[] = 'Week ' . $weekOfMonth;
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addWeek();
            }
        } else if ($range === 'quarterly') {
            // Monthly data for the quarter
            $current = Carbon::parse($startDate)->startOfMonth();
            $end = Carbon::parse($endDate)->endOfMonth();
            
            while ($current->lte($end)) {
                $monthStart = $current->toDateString() . ' 00:00:00';
                $monthEnd = $current->copy()->endOfMonth()->toDateString() . ' 23:59:59';
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posQuery = Transaction::whereBetween($dateCol, [$monthStart, $monthEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    });
                $posQuery = $this->applyBrandFilter($posQuery, $brand);
                $posRevenue = $posQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvQuery = Transaction::whereBetween($dateCol, [$monthStart, $monthEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    });
                $resvQuery = $this->applyBrandFilter($resvQuery, $brand);
                $resvRevenue = $resvQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                
                // Generate quarter-based labels (e.g., "Q1 Jan", "Q1 Feb", "Q1 Mar")
                $quarter = 'Q' . $current->quarter;
                $monthName = $current->format('M');
                $labels[] = $quarter . ' ' . $monthName;
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addMonth();
            }
        } else if ($range === 'yearly') {
            // Monthly data for the year
            $current = Carbon::parse($startDate)->startOfMonth();
            $end = Carbon::parse($endDate)->endOfMonth();
            
            while ($current->lte($end)) {
                $monthStart = $current->toDateString() . ' 00:00:00';
                $monthEnd = $current->copy()->endOfMonth()->toDateString() . ' 23:59:59';
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posQuery = Transaction::whereBetween($dateCol, [$monthStart, $monthEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    });
                $posQuery = $this->applyBrandFilter($posQuery, $brand);
                $posRevenue = $posQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvQuery = Transaction::whereBetween($dateCol, [$monthStart, $monthEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    });
                $resvQuery = $this->applyBrandFilter($resvQuery, $brand);
                $resvRevenue = $resvQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                
                // Generate proper month labels (e.g., "Jan", "Feb", "Mar")
                $labels[] = $current->format('M');
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addMonth();
            }
        } else {
            // Default: Monthly data (fallback)
            $current = Carbon::parse($startDate)->startOfMonth();
            $end = Carbon::parse($endDate)->endOfMonth();
            
            while ($current->lte($end)) {
                $monthStart = $current->toDateString() . ' 00:00:00';
                $monthEnd = $current->copy()->endOfMonth()->toDateString() . ' 23:59:59';
                
                // Get POS transactions (sale_type = 'pos' or transactions without reservation_id)
                $posQuery = Transaction::whereBetween($dateCol, [$monthStart, $monthEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'pos')
                              ->orWhereNull('reservation_id');
                    });
                $posQuery = $this->applyBrandFilter($posQuery, $brand);
                $posRevenue = $posQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                    
                // Get reservation transactions (sale_type = 'reservation' or transactions with reservation_id)
                $resvQuery = Transaction::whereBetween($dateCol, [$monthStart, $monthEnd])
                    ->where(function($query) {
                        $query->where('sale_type', 'reservation')
                              ->orWhereNotNull('reservation_id');
                    });
                $resvQuery = $this->applyBrandFilter($resvQuery, $brand);
                $resvRevenue = $resvQuery->select(DB::raw("SUM($amountExpr) as total"))->value('total') ?? 0;
                
                $labels[] = $current->format('M Y');
                $posData[] = (float) $posRevenue;
                $reservationData[] = (float) $resvRevenue;
                
                $current->addMonth();
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
    private function getPopularProductsForDateRange($startDate, $endDate, $brand = 'all')
    {
        $dateCol = $this->salesDateColumn();
        
        // Get products sold in the date range using product_name directly from transaction_items
        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.transaction_id')
            ->select(
                'transaction_items.product_name as name',
                DB::raw('LOWER(COALESCE(transaction_items.product_category, "")) as category'),
                DB::raw('COALESCE(transaction_items.product_brand, "Unknown") as brand'),
                DB::raw('COALESCE(transaction_items.product_color, "Unknown") as color'),
                DB::raw('SUM(transaction_items.quantity) as total_sold')
            )
            ->whereBetween("transactions.{$dateCol}", [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
        // Apply brand filter if specified
        if ($brand && $brand !== 'all') {
            $query->where('transaction_items.product_brand', $brand);
        }
        
        $popularProducts = $query->groupBy('transaction_items.product_name', 'transaction_items.product_category', 'transaction_items.product_brand', 'transaction_items.product_color')
            ->orderBy('total_sold', 'desc')
            ->limit(100)
            ->get();

        return $popularProducts->map(function($product) {
            return [
                'name' => $product->name,
                'category' => (string) $product->category,
                'brand' => (string) $product->brand,
                'color' => (string) $product->color,
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
            $brand = $request->get('brand', 'all');

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
            // Use the same date column as KPIs for consistency
            $dateCol = $this->salesDateColumn();
            $itemsQuery = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween("t.$dateCol", [$start, $end]);
                
            // Apply brand filter if specified
            if (!empty($brand) && $brand !== 'all') {
                $itemsQuery->where('ti.product_brand', $brand);
            }
                
            // If a specific category is requested, include category in grouping and selection
            if (!empty($category) && $category !== 'all') {
                $itemsQuery->select(
                    DB::raw('COALESCE(ti.product_name, "Unknown") as name'),
                    DB::raw('LOWER(COALESCE(ti.product_category, "")) as category'),
                    DB::raw('COALESCE(ti.product_brand, "Unknown") as brand'),
                    DB::raw('COALESCE(ti.product_color, "Unknown") as color'),
                    DB::raw('SUM(ti.quantity) as sold')
                )
                ->groupBy('ti.product_name', 'ti.product_category', 'ti.product_brand', 'ti.product_color')
                ->whereRaw('LOWER(COALESCE(ti.product_category, "")) = ?', [$category]);
            } else {
                // For "All Categories", show products from ALL categories
                $itemsQuery->select(
                    DB::raw('COALESCE(ti.product_name, "Unknown") as name'),
                    DB::raw('LOWER(COALESCE(ti.product_category, "")) as category'),
                    DB::raw('COALESCE(ti.product_brand, "Unknown") as brand'),
                    DB::raw('COALESCE(ti.product_color, "Unknown") as color'),
                    DB::raw('SUM(ti.quantity) as sold')
                )
                ->groupBy('ti.product_name', 'ti.product_brand', 'ti.product_color', 'ti.product_category'); // Include category to show different categories
                // No WHERE clause - this should include ALL categories
            }

            $items = $itemsQuery->orderByDesc('sold')->limit($limit)->get();

            // Debug: Check what categories exist in the date range
            $categoriesInRange = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween("t.$dateCol", [$start, $end])
                ->select(
                    DB::raw('LOWER(COALESCE(ti.product_category, "")) as category'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(ti.quantity) as total_quantity')
                )
                ->groupBy('ti.product_category')
                ->orderByDesc('total_quantity')
                ->get();

            // Debug logging for Popular Products
            $productNames = $items->pluck('name')->toArray();
            $totalQuantityFromPopularProducts = $items->sum('sold');
            $dateCol = $this->salesDateColumn();
            
            // Additional debug: Check if there are more products beyond the limit (by name+color combinations)
            $totalProductsInRange = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween("t.$dateCol", [$start, $end])
                ->select(DB::raw('COUNT(DISTINCT CONCAT(ti.product_name, "-", COALESCE(ti.product_color, ""), "-", COALESCE(ti.product_category, ""))) as unique_products'))
                ->value('unique_products');
                
            // Check total quantity sold in the range (should match KPI)
            $totalQuantityInRange = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween("t.$dateCol", [$start, $end])
                ->sum('ti.quantity');
                
            // Check for data quality issues
            $nullProductNames = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween("t.$dateCol", [$start, $end])
                ->whereNull('ti.product_name')
                ->sum('ti.quantity');
                
            $emptyProductNames = DB::table('transaction_items as ti')
                ->join('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereBetween("t.$dateCol", [$start, $end])
                ->where('ti.product_name', '')
                ->sum('ti.quantity');
                
            // Check items that fail the JOIN condition
            $totalQuantityWithoutJoin = DB::table('transaction_items as ti')
                ->whereExists(function($query) use ($dateCol, $start, $end) {
                    $query->select(DB::raw(1))
                          ->from('transactions as t')
                          ->whereColumn('t.transaction_id', 'ti.transaction_id')
                          ->whereBetween("t.$dateCol", [$start, $end]);
                })
                ->sum('ti.quantity');
                
            // Check transaction items without matching transactions
            $orphanedItems = DB::table('transaction_items as ti')
                ->leftJoin('transactions as t', 't.transaction_id', '=', 'ti.transaction_id')
                ->whereNull('t.transaction_id')
                ->count();
                
            // Compare with KPI-style calculation for verification
            $kpiStyleQuantity = DB::table('transaction_items as ti')
                ->whereExists(function($query) use ($dateCol, $start, $end) {
                    $query->select(DB::raw(1))
                          ->from('transactions as t')
                          ->whereColumn('t.transaction_id', 'ti.transaction_id')
                          ->whereBetween("t.$dateCol", [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]);
                })
                ->sum('ti.quantity');
            
            // Get the actual SQL query for debugging
            $actualQuery = $itemsQuery->toSql();
            $queryBindings = $itemsQuery->getBindings();
            
            Log::info('Popular Products Query Results', [
                'date_range' => $start->toDateString() . ' to ' . $end->toDateString(),
                'date_column_used' => $dateCol,
                'range' => $range,
                'month' => $month,
                'year' => $y,
                'category_filter' => $category,
                'actual_sql_query' => $actualQuery,
                'query_bindings' => $queryBindings,
                'products_found' => $items->count(),
                'limit_applied' => $limit,
                'grouping_logic' => 'By product_name + product_category + product_brand + product_color (sizes combined)',
                'categories_in_date_range' => $categoriesInRange->toArray(),
                'returned_categories' => $items->pluck('category')->unique()->values()->toArray(),
                'total_unique_products_in_range' => $totalProductsInRange,
                'total_quantity_from_popular_products' => $totalQuantityFromPopularProducts,
                'total_quantity_in_date_range' => $totalQuantityInRange,
                'total_quantity_without_join_constraint' => $totalQuantityWithoutJoin,
                'quantity_discrepancy' => $totalQuantityInRange - $totalQuantityFromPopularProducts,
                'data_quality_issues' => [
                    'null_product_names_quantity' => $nullProductNames,
                    'empty_product_names_quantity' => $emptyProductNames,
                    'orphaned_transaction_items' => $orphanedItems,
                ],
                'kpi_style_quantity_check' => $kpiStyleQuantity,
                'sample_items' => $items->take(5)->toArray(),
                'product_names' => $productNames,
                'full_results' => $items->toArray()
            ]);

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

    /**
     * Get the date range for transaction data to set date picker limits
     */
    public function getTransactionDateRange()
    {
        try {
            // Get the earliest and latest transaction dates
            $dateRange = DB::table('transactions')
                ->selectRaw('MIN(created_at) as earliest, MAX(created_at) as latest')
                ->first();

            $earliestDate = $dateRange && $dateRange->earliest 
                ? Carbon::parse($dateRange->earliest)->format('Y-m-d')
                : '2022-01-01'; // Fallback if no transactions exist

            $latestDate = $dateRange && $dateRange->latest 
                ? Carbon::parse($dateRange->latest)->format('Y-m-d')
                : Carbon::now()->format('Y-m-d');

            return response()->json([
                'earliest' => $earliestDate,
                'latest' => $latestDate,
                'earliest_week' => Carbon::parse($earliestDate)->format('Y-\WW'),
                'latest_week' => Carbon::parse($latestDate)->format('Y-\WW'),
                'earliest_month' => Carbon::parse($earliestDate)->format('Y-m'),
                'latest_month' => Carbon::parse($latestDate)->format('Y-m')
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting transaction date range: ' . $e->getMessage());
            
            // Return safe defaults
            return response()->json([
                'earliest' => '2022-01-01',
                'latest' => Carbon::now()->format('Y-m-d'),
                'earliest_week' => '2022-W01',
                'latest_week' => Carbon::now()->format('Y-\WW'),
                'earliest_month' => '2022-01',
                'latest_month' => Carbon::now()->format('Y-m')
            ]);
        }
    }

    /**
     * Clear all notifications and notification reads
     */
    public function clearNotifications(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'password' => 'required|string'
            ]);

            // Verify the user's password
            $user = Auth::user();
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_password',
                    'message' => 'Invalid password provided'
                ], 400);
            }

            // Only allow owner role to perform this action
            if ($user->role !== 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only owners can perform this action.'
                ], 403);
            }

            DB::beginTransaction();

            try {
                // Count records before deletion for reporting
                $notificationsCount = DB::table('notifications')->count();
                $readsCount = DB::table('notification_reads')->count();

                // Clear all notification reads first (foreign key constraint)
                // Use DELETE instead of TRUNCATE to work within transactions
                DB::table('notification_reads')->delete();

                // Clear all notifications
                DB::table('notifications')->delete();

                // Note: Auto-increment reset is not critical for this operation
                // so we'll skip it to avoid potential issues

                DB::commit();

                // Log this action
                Log::warning('All notifications cleared by owner', [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'notifications_cleared' => $notificationsCount,
                    'reads_cleared' => $readsCount,
                    'timestamp' => Carbon::now()->toDateTimeString(),
                    'ip_address' => $request->ip()
                ]);



                return response()->json([
                    'success' => true,
                    'message' => 'All notifications have been cleared successfully',
                    'notifications_cleared' => $notificationsCount,
                    'reads_cleared' => $readsCount
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error clearing notifications', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications. Please try again.'
            ], 500);
        }
    }

    /**
     * Get operating hours settings
     */
    public function getOperatingHoursSettings()
    {
        try {
            $settings = [
                'operating_hours_enabled' => \App\Models\SystemSettings::get('operating_hours_enabled', true),
                'operating_hours_start' => \App\Models\SystemSettings::get('operating_hours_start', '10:00'),
                'operating_hours_end' => \App\Models\SystemSettings::get('operating_hours_end', '19:00'),
                'emergency_access_enabled' => \App\Models\SystemSettings::get('emergency_access_enabled', false),
                'emergency_access_expires_at' => \App\Models\SystemSettings::get('emergency_access_expires_at'),
                'emergency_access_duration' => \App\Models\SystemSettings::get('emergency_access_duration', 30)
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting operating hours settings', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get operating hours settings'
            ], 500);
        }
    }

    /**
     * Update operating hours setting
     */
    public function updateOperatingHoursSetting(Request $request)
    {
        try {
            $request->validate([
                'key' => 'required|string|in:operating_hours_enabled,operating_hours_start,operating_hours_end,emergency_access_duration',
                'value' => 'required'
            ]);

            $key = $request->key;
            $value = $request->value;
            $type = 'string';

            // Determine the correct type based on the key
            switch ($key) {
                case 'operating_hours_enabled':
                    $type = 'boolean';
                    $value = (bool) $value;
                    break;
                case 'emergency_access_duration':
                    $type = 'integer';
                    $value = max(1, min(480, (int) $value)); // Clamp between 1 and 480 minutes
                    break;
                case 'operating_hours_start':
                case 'operating_hours_end':
                    // Validate time format
                    if (!preg_match('/^([01]?\d|2[0-3]):([0-5]?\d)$/', $value)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid time format. Use HH:MM format.'
                        ], 422);
                    }
                    break;
            }

            \App\Models\SystemSettings::set($key, $value, $type);

            Log::info('Operating hours setting updated', [
                'key' => $key,
                'value' => $value,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating operating hours setting', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'key' => $request->key ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting'
            ], 500);
        }
    }

    /**
     * Enable emergency access
     */
    public function enableEmergencyAccess(Request $request)
    {
        try {
            $request->validate([
                'duration' => 'required|integer|min:1|max:480'
            ]);

            $duration = $request->duration;
            $expiresAt = Carbon::now()->addMinutes($duration);

            \App\Models\SystemSettings::enableEmergencyAccess($duration);

            Log::info('Emergency access enabled', [
                'duration_minutes' => $duration,
                'expires_at' => $expiresAt->toDateTimeString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Emergency access enabled for {$duration} minutes",
                'expires_at' => $expiresAt->toISOString(),
                'duration' => $duration
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error enabling emergency access', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable emergency access'
            ], 500);
        }
    }

    /**
     * Disable emergency access
     */
    public function disableEmergencyAccess()
    {
        try {
            \App\Models\SystemSettings::disableEmergencyAccess();

            Log::info('Emergency access disabled', [
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Emergency access disabled'
            ]);
        } catch (\Exception $e) {
            Log::error('Error disabling emergency access', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable emergency access'
            ], 500);
        }
    }

    /**
     * Get users who have processed transactions
     */
    public function getUsersWithTransactions()
    {
        try {
            $users = User::with(['employee', 'customer'])
                ->whereIn('user_id', function($query) {
                    $query->select('user_id')
                          ->from('transactions')
                          ->whereNotNull('user_id')
                          ->distinct();
                })
                ->select('user_id', 'username', 'role')
                ->orderBy('username')
                ->get();

            // Transform to include the username as name
            $users = $users->map(function($user) {
                return [
                    'id' => $user->user_id,
                    'name' => $user->username, // Use username since that's the actual column
                    'role' => ucfirst($user->role)
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users with transactions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load users'
            ], 500);
        }
    }

    /**
     * Export sales data with filters
     */
    public function exportSales(Request $request)
    {
        try {
            $query = Transaction::with(['user:user_id,username', 'items']);

            // Apply filters
            $exportAll = $request->get('export_all', '0') === '1';
            
            if (!$exportAll) {
                if ($request->has('start_date') && $request->has('end_date')) {
                    $startDate = Carbon::parse($request->start_date)->startOfDay();
                    $endDate = Carbon::parse($request->end_date)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
            }

            // Sale type filter
            if ($request->has('sale_type') && $request->sale_type !== 'both') {
                if ($request->sale_type === 'pos') {
                    $query->where('sale_type', 'pos');
                } elseif ($request->sale_type === 'reservation') {
                    $query->where('sale_type', 'reservation');
                }
            }

            // Users filter
            if ($request->has('users') && !empty($request->users)) {
                $userIds = explode(',', $request->users);
                $query->whereIn('user_id', $userIds);
            }

            // Get transactions
            $transactions = $query->orderBy('created_at', 'desc')->get();

            // Format data for export
            $formattedTransactions = $transactions->map(function ($transaction) {
                // Handle products list safely
                $products = 'No items';
                if ($transaction->items && $transaction->items->count() > 0) {
                    $products = $transaction->items->map(function ($item) {
                        $productName = $item->product_name ?? 'Unknown Product';
                        $size = $item->size ?? 'N/A';
                        $quantity = $item->quantity ?? 1;
                        return $productName . ' (Size: ' . $size . ', Qty: ' . $quantity . ')';
                    })->join('; ');
                }

                // Get username from the user relationship
                $processedBy = 'Unknown';
                if ($transaction->user && $transaction->user->username) {
                    $processedBy = $transaction->user->username;
                } elseif ($transaction->user_id) {
                    $processedBy = 'User ID: ' . $transaction->user_id;
                }

                return [
                    'transaction_id' => $transaction->transaction_id,
                    'sale_type' => strtoupper($transaction->sale_type ?? 'POS'),
                    'cashier_name' => $processedBy,
                    'products' => $products,
                    'subtotal' => $transaction->subtotal,
                    'discount_amount' => $transaction->discount_amount,
                    'total_amount' => $transaction->total_amount,
                    'amount_paid' => $transaction->amount_paid,
                    'change_given' => $transaction->change_given,
                    'sale_datetime' => $transaction->created_at->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'success' => true,
                'transactions' => $formattedTransactions,
                'total_count' => $formattedTransactions->count(),
                'filters_applied' => [
                    'export_all' => $exportAll,
                    'date_range' => !$exportAll ? [
                        'start' => $request->start_date,
                        'end' => $request->end_date
                    ] : null,
                    'sale_type' => $request->sale_type ?? 'both',
                    'users' => $request->has('users') ? explode(',', $request->users) : []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting sales data', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export sales data: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Export reservation data with filters
     */
    public function exportReservations(Request $request)
    {
        try {
            Log::info('Processing reservation export request', $request->all());

            $exportAll = $request->boolean('export_all', false);

            // Base query: only completed and cancelled reservations
            $query = Reservation::query()
                ->whereIn('status', ['completed', 'cancelled']);

            if (!$exportAll) {
                // Date range filter (reservation date)
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = $request->start_date . ' 00:00:00';
                    $endDate = $request->end_date . ' 23:59:59';
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }

                // Status filter
                if ($request->filled('status') && $request->status !== 'all') {
                    $query->where('status', $request->status);
                }
            }

            // Get reservations
            $reservations = $query->orderBy('created_at', 'desc')->get();

            // Format data for export
            $formattedReservations = $reservations->map(function ($reservation) {
                return [
                    'reservation_id' => $reservation->reservation_id,
                    'reservation_date' => $reservation->created_at,
                    'customer_name' => $reservation->customer_name ?? 'N/A',
                    'pickup_date' => $reservation->pickup_date,
                    'customer_email' => $reservation->customer_email ?? 'N/A',
                    'customer_phone' => $reservation->customer_phone ?? 'N/A',
                    'status' => $reservation->status
                ];
            });

            return response()->json([
                'success' => true,
                'reservations' => $formattedReservations,
                'total_count' => $formattedReservations->count(),
                'filters_applied' => [
                    'export_all' => $exportAll,
                    'date_range' => !$exportAll ? [
                        'start' => $request->start_date,
                        'end' => $request->end_date
                    ] : null,
                    'status' => $request->status ?? 'all'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting reservation data', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export reservation data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers and brands for supply export filters
     */
    public function getSupplyFilters(Request $request)
    {
        try {
            // Get unique suppliers from supply_logs
            $suppliers = DB::table('supply_logs')
                ->join('suppliers', 'supply_logs.supplier_id', '=', 'suppliers.id')
                ->select('suppliers.name')
                ->distinct()
                ->whereNotNull('suppliers.name')
                ->where('suppliers.name', '!=', '')
                ->orderBy('suppliers.name')
                ->get();

            // Get unique brands from supply_logs
            $brands = DB::table('supply_logs')
                ->select('brand as name')
                ->distinct()
                ->whereNotNull('brand')
                ->where('brand', '!=', '')
                ->orderBy('brand')
                ->get();

            return response()->json([
                'success' => true,
                'suppliers' => $suppliers,
                'brands' => $brands
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching supply filters', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch supply filters: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export supply data with filters
     */
    public function exportSupplyLogs(Request $request)
    {
        try {
            Log::info('Processing supply export request', $request->all());

            $exportAll = $request->boolean('export_all', false);

            // Base query using the same structure as supplyLogs method
            $query = DB::table('supply_logs')
                ->leftJoin('suppliers', 'supply_logs.supplier_id', '=', 'suppliers.id')
                ->select([
                    'supply_logs.id',
                    'suppliers.name as supplier_name',
                    'suppliers.country',
                    'supply_logs.brand',
                    'supply_logs.size',
                    'supply_logs.quantity',
                    'supply_logs.received_at'
                ]);

            if (!$exportAll) {
                // Date range filter (received date)
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $startDate = $request->start_date . ' 00:00:00';
                    $endDate = $request->end_date . ' 23:59:59';
                    $query->whereBetween('supply_logs.received_at', [$startDate, $endDate]);
                }

                // Supplier filter
                if ($request->filled('suppliers')) {
                    $supplierNames = explode(',', $request->suppliers);
                    $query->whereIn('suppliers.name', $supplierNames);
                }

                // Brand filter
                if ($request->filled('brands')) {
                    $brandNames = explode(',', $request->brands);
                    $query->whereIn('supply_logs.brand', $brandNames);
                }
            }

            // Get supply logs
            $supplyLogs = $query->orderBy('supply_logs.received_at', 'desc')->get();

            // Format data for export
            $formattedSupplyLogs = $supplyLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'supplier_name' => $log->supplier_name ?? 'N/A',
                    'country' => $log->country ?? 'N/A',
                    'brand' => $log->brand ?? 'N/A',
                    'size' => $log->size ?? 'N/A',
                    'quantity' => $log->quantity ?? 0,
                    'received_at' => $log->received_at
                ];
            });

            return response()->json([
                'success' => true,
                'supply_logs' => $formattedSupplyLogs,
                'total_count' => $formattedSupplyLogs->count(),
                'filters_applied' => [
                    'export_all' => $exportAll,
                    'date_range' => !$exportAll ? [
                        'start' => $request->start_date,
                        'end' => $request->end_date
                    ] : null,
                    'suppliers' => $request->has('suppliers') ? explode(',', $request->suppliers) : [],
                    'brands' => $request->has('brands') ? explode(',', $request->brands) : []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting supply data', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export supply data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all unique brands from transaction items
     */
    public function getBrands()
    {
        try {
            $brands = TransactionItem::distinct()
                ->whereNotNull('product_brand')
                ->where('product_brand', '!=', '')
                ->orderBy('product_brand')
                ->pluck('product_brand')
                ->toArray();

            return response()->json([
                'success' => true,
                'brands' => $brands
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch brands: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch brands'
            ], 500);
        }
    }
}