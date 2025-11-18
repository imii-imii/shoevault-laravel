<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Reservation;
use App\Services\SEOService;

class ReservationController extends Controller
{
    /**
     * Show the reservation home page
     */
    public function index()
    {
        $seoService = app(SEOService::class);
        $meta = $seoService->getHomepageMeta();
        $structuredData = $seoService->getOrganizationStructuredData();
        
        return view('reservation.index', compact('meta', 'structuredData'));
    }

    /**
     * Show the reservation portal page
     */
    public function portal(Request $request)
    {
        // Set session flag to allow access to form page
        session(['can_access_form' => true]);
        
        $query = Product::with('sizes')
            ->active()
            ->reservationInventory()
            ->inStock();

        // Handle category filtering
        $selectedCategory = $request->get('category', 'All');
        if ($selectedCategory && $selectedCategory !== 'All') {
            $query->where('category', $selectedCategory);
        }


        // Handle brand filtering (optional, for future extensibility)
        $selectedBrand = $request->get('brand', 'All');
        if ($selectedBrand && $selectedBrand !== 'All') {
            $query->where('brand', $selectedBrand);
        }

        $products = $query->get();

        // Compute current reservation holds and annotate product availability for the portal
        [$holdsBySize, $holdsByProduct] = $this->computeReservationHoldsMaps();
        $products = $this->annotateProductsWithAvailability($products, $holdsBySize);
        // Filter out products that end up with zero available stock after holds
        $products = $products->filter(function($p){
            return ($p->available_total_stock ?? 0) > 0;
        })->values();


        // Get available categories for the filter buttons
        $categories = Product::active()
            ->reservationInventory()
            ->inStock()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        // Get available brands for the filter (unique, non-null, sorted)
        $brands = Product::active()
            ->reservationInventory()
            ->inStock()
            ->distinct()
            ->pluck('brand')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Get current authenticated customer
        $customer = Auth::guard('customer')->user();

        // SEO meta data
        $seoService = app(SEOService::class);
        $meta = $seoService->getPortalMeta();
        $structuredData = $seoService->getWebsiteStructuredData();

        return view('reservation.portal', compact('products', 'categories', 'brands', 'selectedCategory', 'selectedBrand', 'customer', 'meta', 'structuredData'));
    }

    /**
     * Show the reservation form page
     */
    public function form()
    {
        // Check if user accessed this page from the portal
        if (!session('can_access_form')) {
            return redirect()->route('reservation.portal');
        }
        
        // Clear the session flag after checking (optional: remove this line if you want to allow multiple visits)
        session()->forget('can_access_form');
        
        // Get current authenticated customer
        $customer = Auth::guard('customer')->user();
        
        // SEO meta data
        $seoService = app(SEOService::class);
        $meta = $seoService->getReservationFormMeta();
        
        // Return view with X-Robots-Tag header to prevent indexing
        return response(view('reservation.form', compact('customer', 'meta')))
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Show the shoe size converter page
     */
    public function sizeConverter()
    {
        // SEO meta data
        $seoService = app(SEOService::class);
        $meta = $seoService->getSizeConverterMeta();
        
        return view('reservation.size-converter', compact('meta'));
    }

    /**
     * Show individual product page
     */
    public function showProduct(Product $product)
    {
        // Check if product is active and available for reservation
        if (!$product->is_active || $product->inventory_type !== 'reservation') {
            abort(404);
        }

        $seoService = app(SEOService::class);
        $meta = $seoService->getProductMeta($product);
        $structuredData = $seoService->getProductStructuredData($product);
        
        return view('reservation.product', compact('product', 'meta', 'structuredData'));
    }

    /**
     * Show category page
     */
    public function showCategory(string $category)
    {
        $products = Product::with('availableSizes')
            ->where('is_active', true)
            ->where('inventory_type', 'reservation')
            ->where('category', $category)
            ->get();

        if ($products->isEmpty()) {
            abort(404);
        }

        $seoService = app(SEOService::class);
        $meta = $seoService->getCategoryMeta($category);
        $structuredData = $seoService->getWebsiteStructuredData();
        
        return view('reservation.category', compact('products', 'category', 'meta', 'structuredData'));
    }

    /**
     * Show brand page
     */
    public function showBrand(string $brand)
    {
        $products = Product::with('availableSizes')
            ->where('is_active', true)
            ->where('inventory_type', 'reservation')
            ->where('brand', $brand)
            ->get();

        if ($products->isEmpty()) {
            abort(404);
        }

        $seoService = app(SEOService::class);
        $meta = $seoService->getBrandMeta($brand);
        $structuredData = $seoService->getWebsiteStructuredData();
        
        return view('reservation.brand', compact('products', 'brand', 'meta', 'structuredData'));
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

        // Handle search filtering
        $search = $request->get('search');
        if ($search && !empty(trim($search))) {
            $searchTerm = trim($search);
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('brand', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        // Pagination: 40 items per page
        $perPage = 40;
        $page = $request->get('page', 1);
        $products = $query->paginate($perPage);

        // Annotate availability using reservation holds (operate on the page collection only)
        [$holdsBySize, $holdsByProduct] = $this->computeReservationHoldsMaps();
        $pageCollection = $products->getCollection();
        $pageCollection = $this->annotateProductsWithAvailability($pageCollection, $holdsBySize);
        $pageCollection = $pageCollection->filter(function($p){
            return ($p->available_total_stock ?? 0) > 0;
        })->values();
        $products->setCollection($pageCollection);

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
        
        // Apply reservation holds to size stocks
        [$holdsBySize, $holdsByProduct] = $this->computeReservationHoldsMaps();
        $adjustedSizes = $product->sizes->map(function($size) use ($holdsBySize) {
            $keyName = $size->getKeyName();
            $keyValue = $size->getAttribute($keyName);
            $reserved = (int)($holdsBySize[$keyValue] ?? 0);
            $available = max(0, (int)$size->stock - $reserved);
            return [
                'id' => $keyValue,
                'size' => $size->size,
                'stock' => $available,
                'price_adjustment' => $size->price_adjustment ?? 0,
                'is_available' => $size->is_available && $available > 0,
            ];
        })->filter(function($s){
            return $s['is_available'] && $s['stock'] > 0;
        })->values();

        return response()->json([
            'id' => $product->product_id,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => $product->price,
            'formatted_price' => '₱ ' . number_format($product->price, 0),
            'total_stock' => $adjustedSizes->sum('stock'),
            'image_url' => $product->image_url,
            'color' => $product->color,
            'sizes' => $adjustedSizes
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
                // pickupTime must be in H:i format and within working hours (10:00 - 19:00)
                'customer.pickupTime' => ['required', 'string', function($attribute, $value, $fail) {
                    // Accept HH:MM (24-hour) and enforce between 10:00 and 19:00 inclusive
                    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $value, $m)) {
                        return $fail('The pickup time must be a valid time in 24-hour HH:MM format.');
                    }
                    $hour = intval($m[1]);
                    $minute = intval($m[2]);
                    $totalMinutes = $hour * 60 + $minute;
                    $startMinutes = 10 * 60;  // 10:00
                    $endMinutes = 19 * 60;    // 19:00
                    if ($totalMinutes < $startMinutes || $totalMinutes > $endMinutes) {
                        return $fail('Pickup time must be within working hours (10:00 - 19:00).');
                    }
                }],
                'customer.notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|string|exists:products,product_id',
                // product_sizes primary key is product_size_id (see schema); validate against that column
                'items.*.sizeId' => 'required|integer|exists:product_sizes,product_size_id',
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

            // Check stock availability for all items against live holds
            [$holdsBySize, $holdsByProduct] = $this->computeReservationHoldsMaps();
            foreach ($validated['items'] as $item) {
                $size = ProductSize::findOrFail($item['sizeId']);
                $held = (int)($holdsBySize[$item['sizeId']] ?? 0);
                $available = max(0, (int)$size->stock - $held);
                if ($available < (int)$item['qty']) {
                    throw new \Exception("Insufficient available stock for size ID {$item['sizeId']}. Available now: {$available}, Requested: {$item['qty']}");
                }
            }

            // Prepare items array for JSON storage
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
            // Note: reservation_id will be auto-generated by the model's boot() method
            $reservation = \App\Models\Reservation::create([
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
                'reservation_id' => $reservation->reservation_id,
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
     * Build maps of reserved quantities by size and by product for active holds.
     * Active holds include statuses where inventory should be temporarily unavailable
     * to others (e.g., pending and confirmed).
     *
     * @return array [holdsBySizeId, holdsByProductId]
     */
    private function computeReservationHoldsMaps(): array
    {
        $holdsBySize = [];
        $holdsByProduct = [];

        // Consider reservations that still hold inventory (pending/confirmed)
        $activeReservations = Reservation::query()
            ->whereIn('status', ['pending', 'confirmed'])
            ->select(['items'])
            ->get();

        foreach ($activeReservations as $reservation) {
            $items = (array)($reservation->items ?? []);
            foreach ($items as $it) {
                // Expecting keys: product_id, size_id, quantity
                $pid = $it['product_id'] ?? null;
                $sid = $it['size_id'] ?? null;
                $qty = (int)($it['quantity'] ?? 0);
                if ($sid) {
                    $holdsBySize[$sid] = ($holdsBySize[$sid] ?? 0) + $qty;
                }
                if ($pid) {
                    $holdsByProduct[$pid] = ($holdsByProduct[$pid] ?? 0) + $qty;
                }
            }
        }

        return [$holdsBySize, $holdsByProduct];
    }

    /**
     * Annotate each product model instance with availability derived from holds.
     * Adds per-size available_stock and product-level available_total_stock and available_size_labels.
     *
     * @param \Illuminate\Support\Collection|array $products
     * @param array $holdsBySize map of product_size_id => reservedQty
     * @return \Illuminate\Support\Collection
     */
    private function annotateProductsWithAvailability($products, array $holdsBySize)
    {
        // Normalize to collection
        $collection = collect($products);

        return $collection->map(function($product) use ($holdsBySize) {
            // Ensure sizes relation is loaded
            $sizes = $product->relationLoaded('sizes') ? $product->sizes : collect();
            $availableTotal = 0;
            $availableSizeLabels = [];

            foreach ($sizes as $size) {
                $keyName = $size->getKeyName();
                $keyValue = $size->getAttribute($keyName);
                $reserved = (int)($holdsBySize[$keyValue] ?? 0);
                $available = max(0, (int)$size->stock - $reserved);
                // Attach a transient attribute for views
                $size->setAttribute('available_stock', $available);
                if ($size->is_available && $available > 0) {
                    $availableTotal += $available;
                    $availableSizeLabels[] = $size->size;
                }
            }

            // Product-level computed attributes for the view
            $product->setAttribute('available_total_stock', $availableTotal);
            $product->setAttribute('available_size_labels', implode(',', $availableSizeLabels));

            return $product;
        });
    }

    /**
     * Generate a base reservation ID
     */


    /**
     * Send reservation confirmation email
     */
    public function sendConfirmationEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'reservationData' => 'required|array',
                'receiptNumber' => 'required|string'
            ]);

            $email = $request->input('email');
            $reservationData = $request->input('reservationData');
            $receiptNumber = $request->input('receiptNumber');

            // Send the email
            \Illuminate\Support\Facades\Mail::to($email)->send(
                new \App\Mail\ReservationConfirmationMail($reservationData, $receiptNumber)
            );

            return response()->json([
                'success' => true,
                'message' => 'Confirmation email sent successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send reservation confirmation email: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.'
            ], 500);
        }
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