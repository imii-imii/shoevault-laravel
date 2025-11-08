<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Reservation;
use App\Models\Employee;
use Carbon\Carbon;

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
        $reservations = \App\Models\Reservation::with('customer')->orderBy('created_at', 'desc')->get();
        if ($reservations->isEmpty()) {
            $reservations = collect([]);
        }
        $reservationStats = [
            'incomplete' => Reservation::where('status', 'pending')->count(),
            'completed' => Reservation::where('status', 'completed')->count(),
            'cancelled' => Reservation::where('status', 'cancelled')->count()
        ];

    // Reservation cards: Expiring Soon, Expiring Today, Total
    // Assumptions:
    // - "Expiring Today": pending reservations with pickup_date equal to today
    // - "Expiring Soon": pending reservations with pickup_date from today through the next 3 days (inclusive)
        // - "Total": total reservations regardless of status
        $today = Carbon::today();
    $tomorrow = Carbon::tomorrow();
    $soonEnd = Carbon::today()->addDays(3);

        $expiringToday = Reservation::where('status', 'pending')
            ->whereDate('pickup_date', $today)
            ->count();

        $expiringSoon = Reservation::where('status', 'pending')
            ->whereDate('pickup_date', '>=', $today)
            ->whereDate('pickup_date', '<=', $soonEnd)
            ->count();

        $totalReservations = Reservation::count();

        $reservationCards = [
            'expiring_today' => $expiringToday,
            'expiring_soon' => $expiringSoon,
            'total' => $totalReservations,
        ];

        return view('pos.reservations', compact('reservations', 'reservationStats', 'reservationCards'));
    }

    /**
     * Show settings
     */
    public function settings()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        return view('pos.settings', [
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
                    'message' => 'Employee record not found'
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
            }])
            ->where('is_active', true)
            ->posInventory(); // Filter for POS inventory only
            
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

            // Transform products to include sizes summary and image URL
            $transformed = $products->map(function ($product) {
                // Resolve image URL: allow absolute URLs; otherwise prefix with asset()
                $imageUrl = $product->image_url;
                if ($imageUrl && !preg_match('/^https?:\\/\\//', $imageUrl)) {
                    $imageUrl = asset($imageUrl);
                }
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
                    'id' => $product->product_id,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'category' => $product->category,
                    'color' => $product->color,
                    'price' => (float) $product->price,
                    'image_url' => $imageUrl,
                    'inventory_type' => $product->inventory_type,
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
        // Ensure user is authenticated
        if (!Auth::check() || !Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to process a sale'
            ], 401);
        }

        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|string', // Changed from integer to string to support product IDs like "SV-MEN-ABC123"
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
                
                Log::info('Processing POS item', [
                    'product_id' => $item['id'],
                    'product_name' => $product->name,
                    'requested_size' => $item['size'],
                    'size_type' => gettype($item['size'])
                ]);
                
                $size = ProductSize::where('product_id', $item['id'])
                    ->where('size', $item['size'])
                    ->first();
                
                Log::info('Size lookup result', [
                    'product_id' => $item['id'],
                    'requested_size' => $item['size'],
                    'size_found' => $size ? true : false,
                    'size_data' => $size ? $size->toArray() : null
                ]);
                    
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
                    'product_id' => $product->product_id,
                    'size_id' => $size->product_size_id, // Fixed: use product_size_id instead of id
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

                // Debug log the size_id value
                Log::info('Sale item debug', [
                    'product_name' => $product->name,
                    'size' => $size->size,
                    'size_product_size_id' => $size->product_size_id,
                    'size_object' => $size ? $size->toArray() : null
                ]);
                
                $totalQuantity += $item['quantity'];
                $totalItems++;
            }

            // Validate payment amount
            $change = $validated['amount_paid'] - $validated['total'];
            if ($change < 0) {
                throw new \Exception('Insufficient payment amount');
            }
            
            // Debug authentication before creating transaction
            $currentUserId = Auth::id();
            Log::info('POS Transaction creation debug', [
                'auth_check' => Auth::check(),
                'auth_id' => $currentUserId,
                'auth_user' => Auth::user() ? Auth::user()->user_id : null,
                'session_id' => session()->getId()
            ]);
            
            if (!$currentUserId) {
                throw new \Exception('User authentication failed - no user ID available');
            }
            
            // Create transaction record with simplified structure
            $transactionData = [
                'transaction_id' => Transaction::generateTransactionId(),
                'sale_type' => 'pos',
                'reservation_id' => null,
                'user_id' => $currentUserId, // Fixed: changed from cashier_id to user_id
                'subtotal' => $validated['subtotal'],
                'discount_amount' => $validated['discount'] ?? 0,
                'total_amount' => $validated['total'],
                'amount_paid' => $validated['amount_paid'],
                'change_given' => $change,
                'sale_date' => now()
            ];
            
            Log::info('Creating transaction with data', $transactionData);
            
            $transaction = Transaction::create($transactionData);

            // Create individual transaction items
            foreach ($saleItems as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->transaction_id,
                    'product_size_id' => $item['size_id'], // Fixed: changed from size_id to product_size_id
                    'product_name' => $item['product_name'],
                    'product_brand' => $item['product_brand'],
                    'product_color' => $item['product_color'],
                    'product_category' => $item['product_category'],
                    'quantity' => $item['quantity'],
                    'size' => $item['product_size'], // Populate the size field with the same value as product_size
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $item['cost_price'] ?? 0
                ]);
            }
            
            // Deduct stock for all items
            foreach ($saleItems as $item) {
                $size = ProductSize::where('product_size_id', $item['size_id'])->first(); // Fixed: use product_size_id
                if ($size) {
                    $size->decrement('stock', $item['quantity']);
                }
            }
            
            DB::commit();
            
            Log::info('POS Transaction processed successfully', [
                'transaction_id' => $transaction->transaction_id,
                'total_amount' => $transaction->total_amount,
                'items_count' => $transaction->total_items,
                'total_quantity' => $transaction->total_quantity,
                'user_id' => Auth::id() // Fixed: changed from cashier_id to user_id for consistency
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction processed successfully',
                'transaction_id' => $transaction->transaction_id,
                'change' => $change
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('POS Transaction processing failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
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
