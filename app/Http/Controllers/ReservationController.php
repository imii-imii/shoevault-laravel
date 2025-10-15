<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ReservationProduct;

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
        $query = ReservationProduct::with('sizes')
            ->where('is_active', true)
            ->inStock();

        // Handle category filtering
        $selectedCategory = $request->get('category', 'All');
        if ($selectedCategory && $selectedCategory !== 'All') {
            $query->where('category', $selectedCategory);
        }

        $products = $query->get();

        // Get available categories for the filter buttons
        $categories = ReservationProduct::where('is_active', true)
            ->inStock()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('reservation.portal', compact('products', 'categories', 'selectedCategory'));
    }

    /**
     * Show the reservation form page
     */
    public function form()
    {
        return view('reservation.form');
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
        $query = ReservationProduct::with('sizes')
            ->where('is_active', true)
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

        $products = $query->get();

        // Return HTML partial for AJAX requests
        if ($request->ajax()) {
            return view('reservation.partials.product-grid', compact('products'))->render();
        }

        return response()->json(['products' => $products]);
    }

    /**
     * Get product details with sizes for modal
     */
    public function getProductDetails($id)
    {
        $product = ReservationProduct::with(['sizes' => function($query) {
            $query->where('is_available', true)->where('stock', '>', 0);
        }])->findOrFail($id);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => $product->price,
            'formatted_price' => '₱ ' . number_format($product->price, 0),
            'total_stock' => $product->getTotalStock(),
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
                'customer.pickupDate' => 'required|date|after_or_equal:today',
                'customer.pickupTime' => 'required|string',
                'customer.notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:reservation_products,id',
                'items.*.sizeId' => 'required|integer|exists:reservation_product_sizes,id',
                'items.*.qty' => 'required|integer|min:1|max:10',
                'total' => 'required|numeric|min:0'
            ]);

            Log::info('Reservation validation passed:', $validated);

            DB::beginTransaction();

            // Check stock availability for all items
            foreach ($validated['items'] as $item) {
                $size = \App\Models\ReservationProductSize::findOrFail($item['sizeId']);
                if ($size->stock < $item['qty']) {
                    throw new \Exception("Insufficient stock for product size ID {$item['sizeId']}. Available: {$size->stock}, Requested: {$item['qty']}");
                }
            }

            // Create reservations for each item
            $baseReservationId = $this->generateBaseReservationId();
            $reservations = [];
            $itemIndex = 1;

            foreach ($validated['items'] as $item) {
                $product = ReservationProduct::findOrFail($item['id']);
                $size = \App\Models\ReservationProductSize::findOrFail($item['sizeId']);
                
                // Calculate final price with size adjustment
                $finalPrice = $product->price + ($size->price_adjustment ?? 0);

                // Create unique reservation ID for each item
                $uniqueReservationId = $baseReservationId . '-' . str_pad($itemIndex, 2, '0', STR_PAD_LEFT);

                $reservation = \App\Models\Reservation::create([
                    'reservation_id' => $uniqueReservationId,
                    'product_id' => $item['id'],
                    'product_name' => $product->name,
                    'product_brand' => $product->brand,
                    'product_size' => $size->size,
                    'product_color' => $product->color ?? 'Default',
                    'product_price' => $finalPrice,
                    'customer_name' => $validated['customer']['fullName'],
                    'customer_email' => $validated['customer']['email'],
                    'customer_phone' => $validated['customer']['phone'],
                    'quantity' => $item['qty'],
                    'total_amount' => $finalPrice * $item['qty'],
                    'pickup_date' => $validated['customer']['pickupDate'],
                    'pickup_time' => $validated['customer']['pickupTime'],
                    'notes' => $validated['customer']['notes'],
                    'status' => 'pending',
                    'reserved_at' => now()
                ]);

                $reservations[] = $reservation;
                $itemIndex++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully!',
                'reservation_id' => $baseReservationId,
                'reservations' => $reservations
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