<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Supplier;
use App\Models\SupplyLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Show inventory dashboard
     */
    public function dashboard(Request $request)
    {
        Log::info('Inventory dashboard accessed', [
            'user_id' => Auth::check() ? Auth::user()->user_id : 'not authenticated',
            'user_role' => Auth::check() ? Auth::user()->role : 'not authenticated',
            'inventory_type' => $request->get('type', 'pos')
        ]);

        $inventoryType = $request->get('type', 'pos'); // Default to POS
        
        if ($inventoryType === 'reservation') {
            // Get reservation products from database
            $products = Product::with('sizes')->reservationInventory()->active()->get();
        } else {
            // Get POS products from database
            $products = Product::with('sizes')->posInventory()->active()->get();
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
        // Get suppliers from database only (no mock fallback)
        $suppliers = Supplier::orderBy('name')->get();
        return view('inventory.suppliers', compact('suppliers'));
    }

    /**
     * Store a new supplier
     */
    public function storeSupplier(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'brands' => 'nullable|array',
                'brands.*' => 'string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'country' => 'nullable|string|max:120',
                'available_sizes' => 'nullable|string|max:255',
                'total_stock' => 'nullable|integer|min:0',
                'status' => 'nullable|in:active,inactive',
            ]);

            $supplier = Supplier::create([
                'name' => $validated['name'],
                'contact_person' => $validated['contact_person'] ?? null,
                'brands' => $validated['brands'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'] ?? null,
                'available_sizes' => $validated['available_sizes'] ?? null,
                'total_stock' => $validated['total_stock'] ?? 0,
                'status' => $validated['status'] ?? 'active',
                'is_active' => ($validated['status'] ?? 'active') === 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Supplier added successfully',
                'supplier' => $supplier,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add supplier: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a supplier (minimal fields used by UI)
     */
    public function updateSupplier(Request $request, Supplier $supplier)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:120',
                'email' => 'nullable|email|max:255',
                'status' => 'nullable|in:active,inactive',
            ]);

            $supplier->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully',
                'supplier' => $supplier->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a supplier
     */
    public function deleteSupplier(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete supplier: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get supply logs for a supplier
     */
    public function getSupplierLogs(Supplier $supplier)
    {
        $logs = SupplyLog::where('supplier_id', $supplier->id)
            ->orderByDesc('received_at')
            ->orderByDesc('created_at')
            ->limit(300)
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Add a supply log to a supplier
     */
    public function addSupplierLog(Request $request, Supplier $supplier)
    {
        try {
            $validated = $request->validate([
                'brand' => 'required|string|max:120',
                'size' => 'nullable|string|max:40',
                'quantity' => 'required|integer|min:1',
                'received_at' => 'nullable|date',
            ]);

            $log = SupplyLog::create([
                'supplier_id' => $supplier->id,
                'brand' => $validated['brand'],
                'size' => $validated['size'] ?? null,
                'quantity' => $validated['quantity'],
                'received_at' => $validated['received_at'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Log added',
                'log' => $log,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add log: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show reservation reports
     */
    public function reservationReports()
    {
        // Get real reservations from database
        $reservations = \App\Models\Reservation::with('customer')->orderBy('created_at', 'desc')->get();
        
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
            $products = Product::with('sizes')->reservationInventory()->active()->get();
        } else {
            $products = Product::with('sizes')->posInventory()->active()->get();
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
                $data['product_id'] = Product::generateUniqueProductId($request->category);
                $data['sku'] = Product::generateUniqueSku($request->category);
                $data['inventory_type'] = 'reservation';
                $productModel = Product::class;
                $sizeModel = ProductSize::class;
                $relationKey = 'product_id';
            } else {
                // Generate unique POS product ID and SKU
                $data['product_id'] = Product::generateUniqueProductId($request->category);
                $data['sku'] = Product::generateUniqueSku($request->category);
                $data['inventory_type'] = 'pos';
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
                $product = Product::with('sizes')->reservationInventory()->findOrFail($id);
            } else {
                $product = Product::with('sizes')->posInventory()->findOrFail($id);
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
                'inventory_type' => $product->inventory_type,
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
                $product = Product::with('sizes')->reservationInventory()->findOrFail($id);
                $productModel = Product::class;
                $sizeModel = ProductSize::class;
                $relationKey = 'product_id';
            } else {
                $product = Product::with('sizes')->posInventory()->findOrFail($id);
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
            
            // Set inventory type
            $data['inventory_type'] = $inventoryType;
            
            // Generate new SKU if it doesn't exist or if category changed
            if (empty($product->sku) || $product->category !== $request->category) {
                $data['sku'] = Product::generateUniqueSku($request->category);
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
                $product = Product::reservationInventory()->findOrFail($id);
            } else {
                $product = Product::posInventory()->findOrFail($id);
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
        // Add debugging
        Log::info('updateReservationStatus called', [
            'reservation_id' => $id,
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'user_role' => Auth::user() ? Auth::user()->role : 'unknown',
            'is_authenticated' => Auth::check()
        ]);

        // Ensure user is authenticated before proceeding
        if (!Auth::check() || !Auth::id()) {
            Log::error('Unauthenticated user attempted to update reservation status', [
                'reservation_id' => $id,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to perform this action'
            ], 401);
        }

        // Check if user has the right role (cashier, manager, or admin)
        $user = Auth::user();
        if (!in_array($user->role, ['cashier', 'manager', 'admin'])) {
            Log::error('User with insufficient privileges attempted to update reservation status', [
                'reservation_id' => $id,
                'user_id' => $user->user_id,
                'user_role' => $user->role
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action'
            ], 403);
        }

        try {
            $request->validate([
                'status' => 'required|string|in:pending,completed,cancelled',
                'amount_paid' => 'nullable|numeric|min:0', // Add validation for amount_paid
                'change_given' => 'nullable|numeric|min:0' // Add validation for change_given
            ]);

            // Find the reservation
            $reservation = \App\Models\Reservation::with('customer')->findOrFail($id);
            $oldStatus = $reservation->status;
            $newStatus = $request->status;

            Log::info('Reservation status change', [
                'reservation_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            DB::beginTransaction();

            // Handle stock changes based on status transitions
            if ($oldStatus !== $newStatus) {
                $this->handleStockChanges($reservation, $oldStatus, $newStatus);
            }

            // Create sale record when reservation is completed
            if ($oldStatus !== 'completed' && $newStatus === 'completed') {
                $this->createSaleFromReservation($reservation, $request);
            }

            // Delete sale record when reservation is reverted from completed
            if ($oldStatus === 'completed' && ($newStatus === 'pending' || $newStatus === 'cancelled')) {
                $this->deleteSaleFromReservation($reservation);
            }

            // Update the reservation status
            $reservation->status = $newStatus;
            $reservation->save();

            DB::commit();

            Log::info('Reservation status updated successfully', [
                'reservation_id' => $id,
                'new_status' => $newStatus
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reservation status updated successfully',
                'reservation' => [
                    'id' => $reservation->id,
                    'status' => $reservation->status
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Reservation not found', [
                'reservation_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating reservation status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating reservation status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle stock changes based on reservation status transitions
     */
    private function handleStockChanges($reservation, $oldStatus, $newStatus)
    {
        // Only process if reservation has items
        if (!$reservation->items || !is_array($reservation->items)) {
            return;
        }

        // Status transition: pending -> completed (deduct stock)
        if ($oldStatus === 'pending' && $newStatus === 'completed') {
            foreach ($reservation->items as $item) {
                $sizeId = $item['size_id'] ?? null;
                if (!$sizeId) {
                    throw new \Exception("Size ID not found for item: {$item['product_name']}");
                }
                
                $size = ProductSize::find($sizeId);
                if ($size && $size->stock >= $item['quantity']) {
                    $size->decrement('stock', $item['quantity']);
                    Log::info("Stock deducted: Size ID {$sizeId}, Product: {$item['product_name']}, Quantity: {$item['quantity']}");
                } else {
                    throw new \Exception("Insufficient stock for {$item['product_name']} (Size: {$item['product_size']}). Available: " . ($size ? $size->stock : 0) . ", Required: {$item['quantity']}");
                }
            }
        }

        // Status transition: completed -> pending (restore stock)
        elseif ($oldStatus === 'completed' && $newStatus === 'pending') {
            foreach ($reservation->items as $item) {
                $sizeId = $item['size_id'] ?? null;
                if ($sizeId) {
                    $size = ProductSize::find($sizeId);
                    if ($size) {
                        $size->increment('stock', $item['quantity']);
                        Log::info("Stock restored: Size ID {$sizeId}, Product: {$item['product_name']}, Quantity: {$item['quantity']}");
                    }
                }
            }
        }

        // Status transition: completed -> cancelled (restore stock)
        elseif ($oldStatus === 'completed' && $newStatus === 'cancelled') {
            foreach ($reservation->items as $item) {
                $sizeId = $item['size_id'] ?? null;
                if ($sizeId) {
                    $size = ProductSize::find($sizeId);
                    if ($size) {
                        $size->increment('stock', $item['quantity']);
                        Log::info("Stock restored due to cancellation: Size ID {$sizeId}, Product: {$item['product_name']}, Quantity: {$item['quantity']}");
                    }
                }
            }
        }

        // Status transition: pending -> cancelled (no stock change needed since stock wasn't deducted)
        // Status transition: cancelled -> pending (no stock change needed)
        // Status transition: cancelled -> completed (deduct stock like pending -> completed)
        elseif ($oldStatus === 'cancelled' && $newStatus === 'completed') {
            foreach ($reservation->items as $item) {
                $sizeId = $item['size_id'] ?? null;
                if (!$sizeId) {
                    throw new \Exception("Size ID not found for item: {$item['product_name']}");
                }
                
                $size = ProductSize::find($sizeId);
                if ($size && $size->stock >= $item['quantity']) {
                    $size->decrement('stock', $item['quantity']);
                    Log::info("Stock deducted from cancelled to completed: Size ID {$sizeId}, Product: {$item['product_name']}, Quantity: {$item['quantity']}");
                } else {
                    throw new \Exception("Insufficient stock for {$item['product_name']} (Size: {$item['product_size']}). Available: " . ($size ? $size->stock : 0) . ", Required: {$item['quantity']}");
                }
            }
        }
    }

    /**
     * Create a transaction record from a completed reservation
     */
    private function createSaleFromReservation($reservation, $request = null)
    {
        try {
            // Ensure user is authenticated
            if (!Auth::check() || !Auth::id()) {
                throw new \Exception('User must be authenticated to complete a reservation transaction');
            }

            // Validate that reservation has items
            if (!$reservation->items || !is_array($reservation->items) || empty($reservation->items)) {
                Log::warning("Cannot create sale from reservation {$reservation->id}: No items found");
                return;
            }

            // Calculate totals
            $subtotal = 0;
            $totalQuantity = 0;
            $itemCount = 0;

            // Process items and ensure they have all required fields
            $saleItems = [];
            foreach ($reservation->items as $item) {
                $quantity = (int) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? $item['price'] ?? $item['product_price'] ?? 0); // Added product_price
                $itemSubtotal = $quantity * $unitPrice;

                // Log the item details for debugging
                Log::info("Processing reservation item", [
                    'item_data' => $item,
                    'calculated_quantity' => $quantity,
                    'calculated_unit_price' => $unitPrice,
                    'calculated_subtotal' => $itemSubtotal
                ]);

                // Find the size_id from the consolidated tables using product_id and size
                $productId = $item['product_id'] ?? null;
                $productSize = $item['product_size'] ?? null;
                $sizeId = null;
                $product = null;
                
                // Get the actual product to fetch correct category
                if ($productId) {
                    $product = Product::find($productId);
                }
                
                if ($productId && $productSize) {
                    $size = ProductSize::where('product_id', $productId)
                                      ->where('size', $productSize)
                                      ->first();
                    $sizeId = $size ? $size->product_size_id : null; // Fixed: use product_size_id instead of id
                }

                $saleItems[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'size_id' => $item['size_id'] ?? $sizeId, // Use the mapped size_id or the looked-up one
                    'product_name' => $item['product_name'] ?? ($product ? $product->name : 'Unknown Product'),
                    'product_brand' => $item['product_brand'] ?? ($product ? $product->brand : ''),
                    'product_size' => $item['product_size'] ?? '',
                    'product_color' => $item['product_color'] ?? ($product ? $product->color : ''),
                    'product_category' => $product ? $product->category : ($item['product_category'] ?? 'uncategorized'),
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'cost_price' => $item['cost_price'] ?? null, // For future profit analysis
                ];

                $subtotal += $itemSubtotal;
                $totalQuantity += $quantity;
                $itemCount++;
            }

            // Get payment information from request or use defaults
            $amountPaid = $request ? (float) ($request->amount_paid ?? $subtotal) : $subtotal;
            $changeGiven = $request ? (float) ($request->change_given ?? max(0, $amountPaid - $subtotal)) : 0;
            
            // Get the current authenticated user
            $currentUserId = Auth::id();
            
            // Log payment details for debugging
            Log::info("Payment calculation and user info", [
                'subtotal' => $subtotal,
                'amount_paid_input' => $request ? $request->amount_paid : null,
                'calculated_amount_paid' => $amountPaid,
                'calculated_change_given' => $changeGiven,
                'current_user_id' => $currentUserId,
                'authenticated_user_name' => Auth::user() ? Auth::user()->name : null,
                'authenticated_user_role' => Auth::user() ? Auth::user()->role : null
            ]);

            // Create the transaction record with correct model
            $transaction = \App\Models\Transaction::create([
                'transaction_id' => \App\Models\Transaction::generateTransactionId(), // Use same auto-generated format as POS
                'sale_type' => 'reservation', // Distinguish from POS sales
                'reservation_id' => $reservation->reservation_id,
                'user_id' => $currentUserId, // Current authenticated user who marked it complete
                'subtotal' => $subtotal,
                'discount_amount' => 0, // No discount for reservation completions
                'total_amount' => $subtotal,
                'amount_paid' => $amountPaid, // Use the actual amount paid from request
                'change_given' => $changeGiven, // Calculate change properly
                'sale_date' => now()
            ]);

            // Create individual transaction items
            foreach ($saleItems as $item) {
                \App\Models\TransactionItem::create([
                    'transaction_id' => $transaction->transaction_id,
                    'product_size_id' => $item['size_id'], // Updated to match new structure
                    'product_name' => $item['product_name'],
                    'product_brand' => $item['product_brand'],
                    'product_color' => $item['product_color'],
                    'product_category' => $item['product_category'],
                    'quantity' => $item['quantity'],
                    'size' => $item['product_size'], // Size information
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'] ?? 0
                    // Removed fields that are no longer in the new structure
                ]);
            }

            Log::info("Transaction record created from reservation completion", [
                'reservation_id' => $reservation->reservation_id,
                'transaction_id' => $transaction->transaction_id,
                'total_amount' => $subtotal,
                'items_count' => $itemCount
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to create sale from reservation {$reservation->id}: " . $e->getMessage());
            // Don't throw the exception to avoid breaking the reservation completion
            // The reservation status update should still succeed even if sale creation fails
        }
    }

    /**
     * Delete sale record when a reservation is reverted from completed status
     */
    private function deleteSaleFromReservation($reservation)
    {
        try {
            // Find and delete the transaction record associated with this reservation
            $transaction = \App\Models\Transaction::where('reservation_id', $reservation->reservation_id)
                                                 ->where('sale_type', 'reservation')
                                                 ->first();
            
            if ($transaction) {
                Log::info("Deleting transaction record for reverted reservation", [
                    'reservation_id' => $reservation->reservation_id,
                    'transaction_id' => $transaction->transaction_id,
                    'original_total' => $transaction->total_amount
                ]);

                // Delete associated transaction items first (foreign key constraint)
                \App\Models\TransactionItem::where('transaction_id', $transaction->transaction_id)->delete();
                
                // Delete the main transaction record
                $transaction->delete();
                
                Log::info("Successfully deleted transaction record and items for reverted reservation", [
                    'reservation_id' => $reservation->reservation_id,
                    'transaction_id' => $transaction->transaction_id
                ]);
            } else {
                Log::warning("No transaction record found for reservation {$reservation->reservation_id} when attempting to delete");
            }

        } catch (\Exception $e) {
            Log::error("Failed to delete sale record for reservation {$reservation->id}: " . $e->getMessage());
            // Don't throw the exception to avoid breaking the reservation status update
        }
    }

    /**
     * Get a reservation with items for modal details
     */
    public function getReservationDetails(Request $request, $id)
    {
        try {
            Log::info('Getting reservation details', [
                'id_parameter' => $id,
                'request_path' => $request->path(),
                'user_id' => Auth::check() ? Auth::user()->user_id : 'not authenticated'
            ]);

            $reservation = \App\Models\Reservation::with('customer')->findOrFail($id);

            Log::info('Reservation found', [
                'reservation_id' => $reservation->reservation_id,
                'customer_id' => $reservation->customer_id,
                'status' => $reservation->status,
                'customer_loaded' => $reservation->customer ? true : false,
                'customer_data' => $reservation->customer ? [
                    'customer_id' => $reservation->customer->customer_id,
                    'fullname' => $reservation->customer->fullname,
                    'email' => $reservation->customer->email,
                    'phone_number' => $reservation->customer->phone_number,
                ] : null
            ]);

            // Get items from JSON field
            $items = [];
            if ($reservation->items && is_array($reservation->items)) {
                $items = collect($reservation->items)->map(function($item) {
                    // Get the actual product to fetch correct details
                    $product = null;
                    if (isset($item['product_id'])) {
                        $product = Product::find($item['product_id']);
                    }
                    
                    return [
                        'name' => $item['product_name'] ?? ($product ? $product->name : 'Product'),
                        'brand' => $item['product_brand'] ?? ($product ? $product->brand : null),
                        'color' => $item['product_color'] ?? ($product ? $product->color : null),
                        'size' => $item['product_size'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'price' => (float)($item['product_price'] ?? ($product ? $product->price : 0)),
                        'category' => $product ? $product->category : ($item['product_category'] ?? null)
                    ];
                })->toArray();
            } else if ($reservation->items && is_string($reservation->items)) {
                // Handle case where items is a JSON string
                $decodedItems = json_decode($reservation->items, true);
                if ($decodedItems && is_array($decodedItems)) {
                    $items = collect($decodedItems)->map(function($item) {
                        // Get the actual product to fetch correct details
                        $product = null;
                        if (isset($item['product_id'])) {
                            $product = Product::find($item['product_id']);
                        }
                        
                        return [
                            'name' => $item['product_name'] ?? ($product ? $product->name : 'Product'),
                            'brand' => $item['product_brand'] ?? ($product ? $product->brand : null),
                            'color' => $item['product_color'] ?? ($product ? $product->color : null),
                            'size' => $item['product_size'] ?? null,
                            'quantity' => $item['quantity'] ?? 1,
                            'price' => (float)($item['product_price'] ?? ($product ? $product->price : 0)),
                            'category' => $product ? $product->category : ($item['product_category'] ?? null)
                        ];
                    })->toArray();
                }
            }

            Log::info('Processed items for API response', [
                'raw_items_type' => gettype($reservation->items),
                'raw_items_value' => $reservation->items,
                'processed_items_count' => count($items),
                'processed_items' => $items
            ]);

            $total = $reservation->total_amount ?? collect($items)->sum(function($i){
                return ($i['price'] ?? 0) * ($i['quantity'] ?? 1);
            });

            $response = [
                'success' => true,
                'reservation' => [
                    'id' => $reservation->id,
                    'reservation_id' => $reservation->reservation_id,
                    'customer_name' => $reservation->customer->fullname ?? 'N/A',
                    'customer_email' => $reservation->customer->email ?? 'N/A',
                    'customer_phone' => $reservation->customer->phone_number ?? 'N/A',
                    'pickup_date' => optional($reservation->pickup_date)->format('M d, Y'),
                    'pickup_time' => $reservation->pickup_time,
                    'status' => $reservation->status,
                    'created_at' => optional($reservation->created_at)->format('M d, Y h:i A'),
                    'total_amount' => (float)$total
                ],
                'items' => $items
            ];

            Log::info('API Response being sent', [
                'response' => $response,
                'items_count' => count($items)
            ]);

            return response()->json($response);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting reservation details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting reservation details'
            ], 500);
        }
    }
}
