<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Show inventory dashboard
     */
    public function dashboard()
    {
        return view('inventory.dashboard');
    }

    /**
     * Show suppliers management
     */
    public function suppliers()
    {
        return view('inventory.suppliers');
    }

    /**
     * Show settings
     */
    public function settings()
    {
        return view('inventory.settings');
    }

    /**
     * Get inventory data
     */
    public function getInventoryData()
    {
        $products = Product::with('sizes')->active()->get();
        
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
                'min_stock' => $product->min_stock,
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
                'min_stock' => 'required|integer|min:0',
                'sku' => 'nullable|string|unique:products,sku',
                'description' => 'nullable|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            $data = $request->except(['image', 'sizes']);
            
            // Generate unique product ID
            $data['product_id'] = Product::generateUniqueProductId($request->category);
            
            // Handle image upload with custom naming
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                
                // Create a temporary product instance to generate filename
                $tempProduct = new Product(['product_id' => $data['product_id']]);
                $imageName = $tempProduct->generateImageFilename($image->getClientOriginalName());
                
                $image->move(public_path('assets/images/products'), $imageName);
                $data['image_url'] = 'assets/images/products/' . $imageName;
            }

            $product = Product::create($data);

            // Create product sizes
            foreach ($request->sizes as $sizeData) {
                ProductSize::create([
                    'product_id' => $product->id,
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
                'min_stock' => $product->min_stock,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'available_sizes' => $product->sizes->pluck('size')->toArray(),
                'total_stock' => $product->sizes->sum('stock'),
                'stock_status' => $product->sizes->sum('stock') <= 0 ? 'out-of-stock' : 
                               ($product->sizes->sum('stock') <= $product->min_stock ? 'low-stock' : 'in-stock')
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
     * Update product and sizes
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::with('sizes')->findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'brand' => 'required|string|max:255',
                'category' => 'required|in:men,women,accessories',
                'sizes' => 'required|array|min:1',
                'sizes.*.size' => 'required|string',
                'sizes.*.stock' => 'required|integer|min:0',
                'sizes.*.price_adjustment' => 'nullable|numeric',
                'price' => 'required|numeric|min:0',
                'min_stock' => 'required|integer|min:0',
                'sku' => 'nullable|string|unique:products,sku,' . $id,
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            DB::beginTransaction();

            $data = $request->except(['image', 'sizes']);
            
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
                ProductSize::create([
                    'product_id' => $product->id,
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
    public function deleteProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete associated image if exists
            if ($product->image_url && file_exists(public_path($product->image_url))) {
                unlink(public_path($product->image_url));
            }
            
            $product->delete(); // This will cascade delete the sizes too
            
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
}
