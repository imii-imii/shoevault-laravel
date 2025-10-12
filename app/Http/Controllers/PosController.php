<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return view('pos.reservations');
    }

    /**
     * Show settings
     */
    public function settings()
    {
        return view('pos.settings');
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
}
