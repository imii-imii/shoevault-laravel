<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Reservation;

class ReservationController extends Controller
{
    /**
     * Show the reservation home page
     */
    public function index()
    {
        return view('reservation.index');
    }

    /**
     * Show the reservation portal page
     */
    public function portal(Request $request)
    {
        $query = Product::with('sizes')
            ->active()
            ->reservationInventory()
            ->inStock();

        // Handle category filtering
        $selectedCategory = $request->get('category', 'All');
        if ($selectedCategory && $selectedCategory !== 'All') {
            $query->where('category', $selectedCategory);
        }

        $products = $query->get();

        // Get available categories for the filter buttons
        $categories = Product::active()
            ->reservationInventory()
            ->inStock()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        // Get current authenticated customer
        $customer = Auth::guard('customer')->user();

        return view('reservation.portal', compact('products', 'categories', 'selectedCategory', 'customer'));
    }

    /**
     * Show the reservation form page
     */
    public function form()
    {
        // Get current authenticated customer
        $customer = Auth::guard('customer')->user();
        
        return view('reservation.form', compact('customer'));
    }

    /**
     * Show the shoe size converter page
     */
    public function sizeConverter()
    {
        return view('reservation.size-converter');
    }

    /**
     * Get filtered products via AJAX
     */
    public function getFilteredProducts(Request $request)
    {
        $query = Product::with('sizes')
            ->active()
            ->reservationInventory()
            ->inStock();

        // Handle category filtering
        $category = $request->get('category');
        if ($category && $category !== 'All') {
            $query->where('category', $category);
        }

        // Handle price range filtering
        $minPrice = $request->get('minPrice');
        $maxPrice = $request->get('maxPrice');
        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $query->where('price', '>=', (float)$minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $query->where('price', '<=', (float)$maxPrice);
        }

        // Pagination: 40 items per page
        $perPage = 40;
        $page = $request->get('page', 1);
        $products = $query->paginate($perPage);

        // Return HTML partial for AJAX requests with pagination metadata
        if ($request->ajax()) {
            $html = view('reservation.partials.product-grid', ['products' => $products->items()])->render();
            return response()->json([
                'html' => $html,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ]);
        }

        return response()->json(['products' => $products]);
    }

    /**
     * Get product details with sizes for modal
     */
    public function getProductDetails($id)
    {
        $product = Product::with(['sizes' => function($query) {
            $query->where('is_available', true)->where('stock', '>', 0);
        }])
        ->reservationInventory()
        ->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => $product->price,
            'formatted_price' => '₱ ' . number_format($product->price, 0),
            'total_stock' => $product->sizes->sum('stock'),
            'image_url' => $product->image_url,
            'color' => $product->color,
            'sizes' => $product->sizes->map(function($size) {
                return [
                    'id' => $size->id,
                    'size' => $size->size,
                    'stock' => $size->stock,
                    'price_adjustment' => $size->price_adjustment ?? 0,
                    'is_available' => $size->is_available && $size->stock > 0
                ];
            })
        ]);
    }

    /**
     * Check if customer has pending reservations
     */
    public function checkPendingReservations(Request $request)
    {
        try {
            // Get the currently authenticated customer
            $customer = Auth::guard('customer')->user();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required. Please log in to continue.'
                ], 401);
            }

            $customerEmail = $customer->email;
            
            if (Reservation::customerHasPendingReservations($customerEmail)) {
                $pendingReservations = Reservation::getCustomerPendingReservations($customerEmail);
                $reservationIds = $pendingReservations->pluck('reservation_id')->toArray();
                
                return response()->json([
                    'success' => false,
                    'hasPending' => true,
                    'message' => 'You already have pending reservation(s). Please wait for your current reservation(s) to be completed or cancelled before making a new one.',
                    'pendingReservations' => $reservationIds
                ]);
            }

            return response()->json([
                'success' => true,
                'hasPending' => false,
                'message' => 'No pending reservations found. You can proceed with your reservation.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking pending reservations:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking for pending reservations. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a new reservation
     */
    public function store(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Reservation request data:', $request->all());

            $validated = $request->validate([
                'customer.fullName' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.phone' => 'required|string|max:20',
                // pickupDate must be strictly after today (cannot pick present date)
                'customer.pickupDate' => 'required|date|after:today',
                // pickupTime must be in H:i format and within working hours (08:00 - 18:00)
                'customer.pickupTime' => ['required', 'string', function($attribute, $value, $fail) {
                    // Accept HH:MM (24-hour) and enforce between 08:00 and 18:00 inclusive
                    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $value, $m)) {
                        return $fail('The pickup time must be a valid time in 24-hour HH:MM format.');
                    }
                    $hour = intval($m[1]);
                    $minute = intval($m[2]);
                    $totalMinutes = $hour * 60 + $minute;
                    $startMinutes = 8 * 60;   // 08:00
                    $endMinutes = 18 * 60;    // 18:00
                    if ($totalMinutes < $startMinutes || $totalMinutes > $endMinutes) {
                        return $fail('Pickup time must be within working hours (08:00 - 18:00).');
                    }
                }],
                'customer.notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:products,id',
                'items.*.sizeId' => 'required|integer|exists:product_sizes,id',
                'items.*.qty' => 'required|integer|min:1',
                'total' => 'required|numeric|min:0'
            ]);

            // Custom validation: Check total quantity across all items (max 5 total)
            $totalQuantity = collect($validated['items'])->sum('qty');
            if ($totalQuantity > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 5 items allowed in cart total',
                    'errors' => ['items' => ['Total quantity cannot exceed 5 items']]
                ], 422);
            }

            Log::info('Reservation validation passed:', $validated);

            DB::beginTransaction();

            // Check stock availability for all items
            foreach ($validated['items'] as $item) {
                $size = ProductSize::findOrFail($item['sizeId']);
                if ($size->stock < $item['qty']) {
                    throw new \Exception("Insufficient stock for product size ID {$item['sizeId']}. Available: {$size->stock}, Requested: {$item['qty']}");
                }
            }

            // Prepare items array for JSON storage
            $reservationId = $this->generateBaseReservationId();
            $itemsData = [];
            
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['id']);
                $size = ProductSize::findOrFail($item['sizeId']);
                
                // Calculate final price with size adjustment
                $finalPrice = $product->price + ($size->price_adjustment ?? 0);
                
                $itemsData[] = [
                    'product_id' => $item['id'],
                    'size_id' => $item['sizeId'], // Store size ID for stock management
                    'product_name' => $product->name,
                    'product_brand' => $product->brand,
                    'product_size' => $size->size,
                    'product_color' => $product->color ?? 'Default',
                    'product_price' => $finalPrice,
                    'quantity' => $item['qty'],
                    'subtotal' => $finalPrice * $item['qty']
                ];
            }

            // Get the current authenticated customer
            $customer = Auth::guard('customer')->user();
            if (!$customer) {
                throw new \Exception('Customer authentication required');
            }

            // Update customer phone number if provided (in case it's changed)
            if (!empty($validated['customer']['phone']) && $customer->phone_number !== $validated['customer']['phone']) {
                $customer->phone_number = $validated['customer']['phone'];
                $customer->save();
                Log::info('Updated customer phone number', [
                    'customer_id' => $customer->customer_id,
                    'new_phone' => $validated['customer']['phone']
                ]);
            }

            // Create single reservation with JSON items and customer_id foreign key
            $reservation = \App\Models\Reservation::create([
                'reservation_id' => $reservationId,
                'customer_id' => $customer->customer_id, // Use primary key field directly
                'items' => $itemsData,
                'total_amount' => $validated['total'],
                'pickup_date' => $validated['customer']['pickupDate'],
                'pickup_time' => $validated['customer']['pickupTime'],
                'notes' => $validated['customer']['notes'],
                'status' => 'pending',
                'reserved_at' => now()
            ]);

            // Stock will be held (not deducted) until reservation is completed
            // This allows for better inventory management and prevents stock lockup
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully!',
                'reservation_id' => $reservationId,
                'reservation' => $reservation
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Reservation creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a base reservation ID
     */
    private function generateBaseReservationId()
    {
        do {
            // Generate a reservation ID with timestamp and random string for uniqueness
            $timestamp = now()->format('ymdHis'); // YYMMDDHHMMSS format for better uniqueness
            $randomString = strtoupper(\Illuminate\Support\Str::random(4));
            $baseReservationId = "RSV-{$timestamp}-{$randomString}";
        } while (\App\Models\Reservation::where('reservation_id', 'LIKE', $baseReservationId . '%')->exists());
        
        return $baseReservationId;
    }

    /**
     * Generate a unique reservation ID (legacy method, kept for compatibility)
     */
    private function generateUniqueReservationId()
    {
        do {
            // Generate a reservation ID with timestamp and random string for uniqueness
            $timestamp = now()->format('ymdHis'); // YYMMDDHHMMSS format
            $randomString = strtoupper(\Illuminate\Support\Str::random(6));
            $reservationId = "RSV-{$timestamp}-{$randomString}";
        } while (\App\Models\Reservation::where('reservation_id', $reservationId)->exists());
        
        return $reservationId;
    }
}