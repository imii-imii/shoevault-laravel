<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ReservationProduct;
use App\Models\ReservationProductSize;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InventoryController extends Controller
{
    /**
     * Show inventory dashboard
     */
    public function dashboard(Request $request)
    {
        $inventoryType = $request->get('type', 'pos'); // Default to POS
        
        if ($inventoryType === 'reservation') {
            // Get reservation products from database
            $products = ReservationProduct::with('sizes')->active()->get();
        } else {
            // Get POS products from database
            $products = Product::with('sizes')->active()->get();
        }
        
        // Get suppliers from database (or empty collection if not implemented yet)
        $suppliers = Supplier::all() ?? collect([]);
        
        // Mock reservations data for now (implement when reservation system is ready)
        $reservations = collect([]);
        
        $reservationStats = [
            'incomplete' => 0,
            'expiring_soon' => 0,
            'expiring_today' => 0
        ];
        
        return view('inventory.dashboard', compact('products', 'suppliers', 'reservations', 'reservationStats', 'inventoryType'));
    }

    /**
     * Show suppliers management
     */
    public function suppliers()
    {
        // Get suppliers from database or provide mock data
        $suppliers = Supplier::all();
        
        // If no suppliers exist, provide mock data for UI testing
        if ($suppliers->isEmpty()) {
            $suppliers = collect([
                (object)[
                    'id' => 1,
                    'name' => 'Nike Philippines',
                    'contact_person' => 'John Smith',
                    'brand' => 'Nike',
                    'total_stock' => 100,
                    'country' => 'Philippines',
                    'available_sizes' => '7-12',
                    'email' => 'supplier@nike.com.ph',
                    'phone' => '+63 2 123 4567',
                    'status' => 'active'
                ],
                (object)[
                    'id' => 2,
                    'name' => 'Adidas Distributor',
                    'contact_person' => 'Jane Doe',
                    'brand' => 'Adidas',
                    'total_stock' => 85,
                    'country' => 'Philippines',
                    'available_sizes' => '6-11',
                    'email' => 'contact@adidas-ph.com',
                    'phone' => '+63 2 987 6543',
                    'status' => 'active'
                ]
            ]);
        }

        return view('inventory.suppliers', compact('suppliers'));
    }

    /**
     * Show reservation reports
     */
    public function reservationReports()
    {
        // Get real reservations from database
        $reservations = \App\Models\Reservation::orderBy('created_at', 'desc')->get();
        
        // If no reservations exist, show empty state with proper message
        if ($reservations->isEmpty()) {
            $reservations = collect([]);
        }

        // Calculate real reservation statistics
        $reservationStats = [
            'incomplete' => \App\Models\Reservation::where('status', 'pending')->count(),
            'completed' => \App\Models\Reservation::where('status', 'completed')->count(),
            'cancelled' => \App\Models\Reservation::where('status', 'cancelled')->count()
        ];

        return view('inventory.reservation-reports', compact('reservations', 'reservationStats'));
    }

    /**
     * Show settings
     */
    public function settings()
    {
        return view('inventory.settings');
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

    /**
     * Get inventory data
     */
    public function getInventoryData(Request $request)
    {
        $inventoryType = $request->get('type', 'pos'); // Default to POS
        
        if ($inventoryType === 'reservation') {
            $products = ReservationProduct::with('sizes')->active()->get();
        } else {
            $products = Product::with('sizes')->active()->get();
        }
        
        // Transform products to include size and stock information
        $transformedProducts = $products->map(function($product) {
            return [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $product->name,
                'brand' => $product->brand,
                'category' => $product->category,
                'color' => $product->color,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'total_stock' => $product->getTotalStock(),
                'available_sizes' => $product->sizes->pluck('size')->toArray(),
                'sizes_with_stock' => $product->sizesWithStock,
                'stock_status' => $product->stock_status,
                'is_active' => $product->is_active
            ];
        });
        
        $stats = [
            'total_products' => $products->count(),
            'low_stock_items' => $products->filter(function($product) {
                return $product->isLowStock();
            })->count(),
            'total_categories' => $products->pluck('category')->unique()->count(),
            'inventory_value' => $products->sum(function($product) {
                return $product->price * $product->getTotalStock();
            })
        ];
        
        return response()->json([
            'products' => $transformedProducts,
            'stats' => $stats
        ]);
    }

    /**
     * Add new product with sizes
     */
    public function addProduct(Request $request)
    {
        try {
            $inventoryType = $request->get('inventory_type', 'pos'); // Default to POS
            
            $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'category' => 'required|in:men,women,accessories',
                'color' => 'required|string|max:100',
                'sizes' => 'required|array|min:1',
                'sizes.*.size' => 'required|string',
                'sizes.*.stock' => 'required|integer|min:0',
                'sizes.*.price_adjustment' => 'nullable|numeric',
                'price' => 'required|numeric|min:0',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'inventory_type' => 'required|in:pos,reservation'
            ]);

            DB::beginTransaction();

            $data = $request->except(['image', 'sizes', 'inventory_type']);
            
            if ($inventoryType === 'reservation') {
                // Generate unique reservation product ID and SKU
                $data['product_id'] = ReservationProduct::generateUniqueProductId($request->category);
                $data['sku'] = ReservationProduct::generateUniqueSku($request->category);
                $productModel = ReservationProduct::class;
                $sizeModel = ReservationProductSize::class;
                $relationKey = 'reservation_product_id';
            } else {
                // Generate unique POS product ID and SKU
                $data['product_id'] = Product::generateUniqueProductId($request->category);
                $data['sku'] = Product::generateUniqueSku($request->category);
                $productModel = Product::class;
                $sizeModel = ProductSize::class;
                $relationKey = 'product_id';
            }
            
            // Handle image upload with custom naming
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Create a temporary product instance to generate filename
                $tempProduct = new $productModel(['product_id' => $data['product_id']]);
                $imageName = $tempProduct->generateImageFilename($image->getClientOriginalName());
                
                $image->move(public_path('assets/images/products'), $imageName);
                $data['image_url'] = 'assets/images/products/' . $imageName;
            }

            $product = $productModel::create($data);

            // Create product sizes
            foreach ($request->sizes as $sizeData) {
                $sizeModel::create([
                    $relationKey => $product->id,
                    'size' => $sizeData['size'],
                    'stock' => $sizeData['stock'],
                    'price_adjustment' => $sizeData['price_adjustment'] ?? 0,
                    'is_available' => true
                ]);
            }

            DB::commit();

            // Load the product with sizes and format it like the getInventoryData method
            $product = $product->load('sizes');
            
            // Format the product data for frontend
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'product_id' => $product->product_id,
                'brand' => $product->brand,
                'category' => $product->category,
                'color' => $product->color,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'available_sizes' => $product->sizes->pluck('size')->toArray(),
                'total_stock' => $product->sizes->sum('stock'),
                'stock_status' => $product->sizes->sum('stock') <= 0 ? 'out-of-stock' : 
                               ($product->sizes->sum('stock') <= 5 ? 'low-stock' : 'in-stock')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully',
                'product' => $formattedProduct
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single product with sizes for editing
     */
    public function getProduct(Request $request, $id)
    {
        try {
            $inventoryType = $request->get('type', 'pos'); // Default to POS
            
            // Determine which model to use based on inventory type
            if ($inventoryType === 'reservation') {
                $product = ReservationProduct::with('sizes')->findOrFail($id);
            } else {
                $product = Product::with('sizes')->findOrFail($id);
            }
            
            // Format the product data for frontend
            $formattedProduct = [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'category' => $product->category,
                'color' => $product->color,
                'price' => $product->price,
                'image_url' => $product->image_url,
                'sizes' => $product->sizes->map(function($size) {
                    return [
                        'id' => $size->id,
                        'size' => $size->size,
                        'stock' => $size->stock,
                        'price_adjustment' => $size->price_adjustment ?? 0,
                        'is_available' => $size->is_available
                    ];
                })
            ];
            
            return response()->json([
                'success' => true,
                'product' => $formattedProduct
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update product and sizes
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $inventoryType = $request->get('inventory_type', 'pos'); // Default to POS
            
            // Determine which model to use based on inventory type
            if ($inventoryType === 'reservation') {
                $product = ReservationProduct::with('sizes')->findOrFail($id);
                $productModel = ReservationProduct::class;
                $sizeModel = ReservationProductSize::class;
                $relationKey = 'reservation_product_id';
            } else {
                $product = Product::with('sizes')->findOrFail($id);
                $productModel = Product::class;
                $sizeModel = ProductSize::class;
                $relationKey = 'product_id';
            }
            
            $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'category' => 'required|in:men,women,accessories',
                'sizes' => 'required|array|min:1',
                'sizes.*.size' => 'required|string',
                'sizes.*.stock' => 'required|integer|min:0',
                'sizes.*.price_adjustment' => 'nullable|numeric',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'inventory_type' => 'required|in:pos,reservation'
            ]);

            DB::beginTransaction();

            $data = $request->except(['image', 'sizes', 'inventory_type']);
            
            // Generate new SKU if it doesn't exist or if category changed
            if (empty($product->sku) || $product->category !== $request->category) {
                if ($inventoryType === 'reservation') {
                    $data['sku'] = ReservationProduct::generateUniqueSku($request->category);
                } else {
                    $data['sku'] = Product::generateUniqueSku($request->category);
                }
            }
            
            // Handle image upload for update
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image_url && file_exists(public_path($product->image_url))) {
                    unlink(public_path($product->image_url));
                }
                
                $image = $request->file('image');
                $imageName = $product->generateImageFilename($image->getClientOriginalName());
                $image->move(public_path('assets/images/products'), $imageName);
                $data['image_url'] = 'assets/images/products/' . $imageName;
            }

            $product->update($data);

            // Update sizes - delete old ones and create new ones
            $product->sizes()->delete();
            foreach ($request->sizes as $sizeData) {
                $sizeModel::create([
                    $relationKey => $product->id,
                    'size' => $sizeData['size'],
                    'stock' => $sizeData['stock'],
                    'price_adjustment' => $sizeData['price_adjustment'] ?? 0,
                    'is_available' => true
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'product' => $product->load('sizes')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete product
     */
    public function deleteProduct(Request $request, $id)
    {
        try {
            // Determine which inventory type to delete from (default POS)
            $inventoryType = $request->get('type', $request->get('inventory_type', 'pos'));

            if ($inventoryType === 'reservation') {
                $product = \App\Models\ReservationProduct::findOrFail($id);
            } else {
                $product = Product::findOrFail($id);
            }
            
            // Delete associated image if exists
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                unlink(public_path($product->image_url));
            }
            
            $product->delete(); // This should cascade delete the sizes too if configured
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available sizes for a category
     */
    public function getSizesByCategory($category)
    {
        $sizes = Product::getSizeOptionsByCategory($category);
        
        return response()->json([
            'success' => true,
            'sizes' => $sizes
        ]);
    }

    /**
     * Update reservation status
     */
    public function updateReservationStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pending,completed,cancelled'
            ]);

            // Find and update the reservation
            $reservation = \App\Models\Reservation::findOrFail($id);
            $reservation->status = $request->status;
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation status updated successfully',
                'reservation' => [
                    'id' => $reservation->id,
                    'status' => $reservation->status
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating reservation status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation status: ' . $e->getMessage()
            ], 500);
        }
    }
}
