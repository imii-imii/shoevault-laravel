<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $query = Product::with('sizes')->active();
        
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
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

        // Transform products to include sizes summary and avoid exposing unnecessary fields
        $transformed = $products->map(function ($product) {
            $sizes = $product->sizes->map(function ($size) use ($product) {
                return [
                    'id' => $size->id,
                    'size' => $size->size,
                    'stock' => (int) $size->stock,
                    'is_available' => (bool) $size->is_available,
                    'price_adjustment' => (float) $size->price_adjustment,
                    // effective price = base price + adjustment
                    'effective_price' => (float) ($product->price + $size->price_adjustment),
                ];
            });

            return [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'name' => $product->name,
                'brand' => $product->brand,
                'category' => $product->category,
                'color' => $product->color,
                'price' => (float) $product->price,
                'image_url' => $product->image_url,
                'is_active' => (bool) $product->is_active,
                'total_stock' => (int) $product->sizes->sum('stock'),
                'sizes' => $sizes,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'products' => $transformed
        ]);
    }

    /**
     * Process sale transaction
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.size' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,gcash,maya'
        ]);

        // Verify stock availability
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $size = ProductSize::where('product_id', $product->id)
                ->where('size', $item['size'])
                ->first();

            if (!$size) {
                return response()->json([
                    'success' => false,
                    'message' => "Size {$item['size']} not found for {$product->name}"
                ], 400);
            }

            if (!$size->is_available || $size->stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$product->name} size {$item['size']}. Available: {$size->stock}"
                ], 400);
            }
        }

        // Calculate change
        $change = $request->amount_paid - $request->total;
        if ($change < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient payment amount'
            ], 400);
        }

        // Create sale record
        $sale = Sale::create([
            'transaction_id' => Sale::generateTransactionId(),
            'user_id' => Auth::id(),
            'subtotal' => $request->subtotal,
            'tax' => $request->tax,
            'total' => $request->total,
            'amount_paid' => $request->amount_paid,
            'change_amount' => $change,
            'payment_method' => $request->payment_method,
            'items' => $request->items,
            'notes' => $request->notes ?? null
        ]);

        // Update product stock
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);
            $size = ProductSize::where('product_id', $product->id)
                ->where('size', $item['size'])
                ->first();
            if ($size) {
                $size->decrement('stock', $item['quantity']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale processed successfully',
            'transaction_id' => $sale->transaction_id,
            'change' => $change
        ]);
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
