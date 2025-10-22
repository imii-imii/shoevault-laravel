<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Sale;

class PosController extends Controller
{
    /**
     * Show POS dashboard
     */
    public function dashboard()
    {
        return view('pos.dashboard');
    }



    /**
     * Show reservations
     */
    public function reservations()
    {
        // Load same reservations and stats as inventory reservation reports
        $reservations = \App\Models\Reservation::orderBy('created_at', 'desc')->get();
        if ($reservations->isEmpty()) {
            $reservations = collect([]);
        }
        $reservationStats = [
            'incomplete' => \App\Models\Reservation::where('status', 'pending')->count(),
            'completed' => \App\Models\Reservation::where('status', 'completed')->count(),
            'cancelled' => \App\Models\Reservation::where('status', 'cancelled')->count()
        ];

        return view('pos.reservations', compact('reservations', 'reservationStats'));
    }

    /**
     * Show settings
     */
    public function settings()
    {
        return view('pos.settings');
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
     * Get products for POS
     */
    public function getProducts(Request $request)
    {
        try {
            $category = $request->get('category', 'all');
            
            $query = Product::with(['sizes' => function($query) {
                $query->where('stock', '>', 0)->where('is_available', true);
            }])->where('is_active', true);
            
            if ($category !== 'all') {
                $query->where('category', $category);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('brand', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            }
            
            $products = $query->get();

            // Transform products to include sizes summary
            $transformed = $products->map(function ($product) {
                $sizes = $product->sizes->map(function ($size) use ($product) {
                    return [
                        'id' => $size->id,
                        'size' => $size->size,
                        'stock' => (int) $size->stock,
                        'price_adjustment' => (float) ($size->price_adjustment ?? 0),
                        'effective_price' => (float) ($product->price + ($size->price_adjustment ?? 0)),
                        'is_available' => $size->stock > 0 && $size->is_available
                    ];
                });

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'category' => $product->category,
                    'color' => $product->color,
                    'price' => (float) $product->price,
                    'total_stock' => (int) $product->sizes->sum('stock'),
                    'sizes' => $sizes,
                ];
            })->filter(function ($product) {
                return $product['total_stock'] > 0; // Only show products with stock
            })->values();

            return response()->json([
                'success' => true,
                'products' => $transformed
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage(),
                'products' => []
            ]);
        }
    }

    /**
     * Process sale transaction with enhanced functionality
     */
    public function processSale(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer',
                'items.*.size' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'subtotal' => 'required|numeric|min:0',
                'tax' => 'numeric|min:0',
                'discount' => 'numeric|min:0',
                'total' => 'required|numeric|min:0',
                'amount_paid' => 'required|numeric|min:0',
                'payment_method' => 'required|string|in:cash,card,gcash,bank_transfer'
            ]);

            $saleItems = [];
            $totalQuantity = 0;
            $totalItems = 0;
            
            // Process each item and verify stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['id']);
                if (!$product) {
                    throw new \Exception("Product not found: {$item['id']}");
                }
                
                $size = ProductSize::where('product_id', $item['id'])
                    ->where('size', $item['size'])
                    ->first();
                    
                if (!$size) {
                    throw new \Exception("Size {$item['size']} not found for product {$product->name}");
                }
                
                if ($size->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name} size {$item['size']}. Available: {$size->stock}");
                }
                
                // Calculate effective price with size adjustment
                $unitPrice = $product->price + ($size->price_adjustment ?? 0);
                $subtotal = $unitPrice * $item['quantity'];
                
                // Build detailed item record for the sale
                $saleItems[] = [
                    'product_id' => $product->id,
                    'size_id' => $size->id,
                    'product_name' => $product->name,
                    'product_brand' => $product->brand,
                    'product_size' => $size->size,
                    'product_color' => $product->color,
                    'product_category' => $product->category,
                    'unit_price' => $unitPrice,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'cost_price' => $product->cost_price ?? 0,
                    'sku' => $product->sku ?? null
                ];
                
                $totalQuantity += $item['quantity'];
                $totalItems++;
            }

            // Validate payment amount
            $change = $validated['amount_paid'] - $validated['total'];
            if ($change < 0) {
                throw new \Exception('Insufficient payment amount');
            }
            
            // Create sale record with enhanced data
            $sale = Sale::create([
                'transaction_id' => Sale::generateTransactionId(),
                'sale_type' => 'pos',
                'reservation_id' => null,
                'user_id' => Auth::id(),
                'subtotal' => $validated['subtotal'],
                'tax_amount' => $validated['tax'] ?? 0,
                'discount_amount' => $validated['discount'] ?? 0,
                'total_amount' => $validated['total'],
                'amount_paid' => $validated['amount_paid'],
                'change_given' => $change,
                'payment_method' => $validated['payment_method'],
                'items' => $saleItems, // Store detailed items array
                'sale_date' => now(),
                'notes' => $request->notes ?? null
            ]);
            
            // Deduct stock for all items
            foreach ($saleItems as $item) {
                $size = ProductSize::find($item['size_id']);
                if ($size) {
                    $size->decrement('stock', $item['quantity']);
                }
            }
            
            DB::commit();
            
            Log::info('POS Sale processed successfully', [
                'transaction_id' => $sale->transaction_id,
                'total_amount' => $sale->total_amount,
                'items_count' => $sale->total_items,
                'total_quantity' => $sale->total_quantity,
                'cashier_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Sale processed successfully',
                'transaction_id' => $sale->transaction_id,
                'change' => $change
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('POS Sale processing failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
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
