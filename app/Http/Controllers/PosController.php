<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
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
     * Show sales history
     */
    public function salesHistory()
    {
        $sales = Sale::with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
        
        return view('pos.sales-history', compact('sales'));
    }

    /**
     * Show reservations
     */
    public function reservations()
    {
        return view('pos.reservations');
    }

    /**
     * Get products for POS
     */
    public function getProducts(Request $request)
    {
        $query = Product::active();
        
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
        
        return response()->json([
            'success' => true,
            'products' => $products
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
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
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
            $product->decrement('stock', $item['quantity']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sale processed successfully',
            'transaction_id' => $sale->transaction_id,
            'change' => $change
        ]);
    }
}
