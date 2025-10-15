<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}