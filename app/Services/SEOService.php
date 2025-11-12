<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class SEOService
{
    /**
     * Generate meta tags for homepage
     */
    public function getHomepageMeta(): array
    {
        return [
            'title' => 'ShoeVault - Premium Shoe Store & Reservation System',
            'description' => 'Discover premium shoes from top brands like Nike, Adidas, and more. Reserve your favorite pairs online with our easy reservation system. Quality footwear for men, women, and kids.',
            'keywords' => 'shoes, sneakers, nike, adidas, footwear, shoe store, online reservation, premium shoes, athletic shoes, casual shoes',
            'og_title' => 'ShoeVault - Premium Shoe Store & Reservation System',
            'og_description' => 'Discover premium shoes from top brands. Reserve your favorite pairs online.',
            'og_type' => 'website',
            'og_url' => url('/'),
            'og_image' => url('/images/shoevault-logo.png'),
            'canonical' => url('/'),
            'robots' => 'index, follow'
        ];
    }

    /**
     * Generate meta tags for product portal page
     */
    public function getPortalMeta(): array
    {
        $productCount = Product::where('is_active', true)->count();
        $brands = Product::where('is_active', true)->distinct()->pluck('brand')->implode(', ');
        
        return [
            'title' => 'Shop Shoes Online - Reserve Your Favorites | ShoeVault',
            'description' => "Browse our collection of {$productCount}+ premium shoes from {$brands} and more. Easy online reservation system with secure checkout.",
            'keywords' => 'shop shoes online, shoe reservation, nike shoes, adidas shoes, footwear collection, online shoe store',
            'og_title' => 'Shop Shoes Online - Reserve Your Favorites | ShoeVault',
            'og_description' => "Browse our collection of {$productCount}+ premium shoes with easy online reservation.",
            'og_type' => 'website',
            'og_url' => url('/portal'),
            'og_image' => url('/images/shoevault-logo.png'),
            'canonical' => url('/portal'),
            'robots' => 'index, follow'
        ];
    }

    /**
     * Generate meta tags for reservation form
     */
    public function getReservationFormMeta(): array
    {
        return [
            'title' => 'Reserve Your Shoes - Easy Online Form | ShoeVault',
            'description' => 'Complete your shoe reservation with our simple online form. Secure your favorite pairs and choose your preferred pickup time.',
            'keywords' => 'shoe reservation form, reserve shoes online, shoe booking, footwear reservation',
            'og_title' => 'Reserve Your Shoes - Easy Online Form | ShoeVault',
            'og_description' => 'Complete your shoe reservation with our simple online form.',
            'og_type' => 'website',
            'og_url' => url('/form'),
            'canonical' => url('/form'),
            'robots' => 'index, follow'
        ];
    }

    /**
     * Generate meta tags for size converter
     */
    public function getSizeConverterMeta(): array
    {
        return [
            'title' => 'Shoe Size Converter - Find Your Perfect Fit | ShoeVault',
            'description' => 'Convert shoe sizes between US, UK, EU, and other international sizing systems. Find your perfect fit with our accurate size conversion tool.',
            'keywords' => 'shoe size converter, size conversion, us to uk size, eu shoe sizes, international shoe sizes, size chart',
            'og_title' => 'Shoe Size Converter - Find Your Perfect Fit | ShoeVault',
            'og_description' => 'Convert shoe sizes between US, UK, EU, and other international sizing systems.',
            'og_type' => 'website',
            'og_url' => url('/size-converter'),
            'canonical' => url('/size-converter'),
            'robots' => 'index, follow'
        ];
    }

    /**
     * Generate meta tags for individual product
     */
    public function getProductMeta(Product $product): array
    {
        $availableSizes = $product->availableSizes->pluck('size')->implode(', ');
        $priceFormatted = '₱' . number_format($product->price, 0);
        
        return [
            'title' => "{$product->name} - {$product->brand} | ShoeVault",
            'description' => "Shop {$product->name} by {$product->brand} for {$priceFormatted}. Available in sizes: {$availableSizes}. Reserve online for easy pickup.",
            'keywords' => "{$product->name}, {$product->brand}, {$product->category} shoes, {$product->color} shoes, shoe reservation",
            'og_title' => "{$product->name} - {$product->brand} | ShoeVault",
            'og_description' => "Shop {$product->name} by {$product->brand} for {$priceFormatted}.",
            'og_type' => 'product',
            'og_url' => url("/product/{$product->product_id}"),
            'og_image' => $product->image_url ?: url('/images/default-shoe.jpg'),
            'canonical' => url("/product/{$product->product_id}"),
            'robots' => 'index, follow',
            'product:price:amount' => $product->price,
            'product:price:currency' => 'PHP',
            'product:availability' => $product->availableSizes->count() > 0 ? 'in stock' : 'out of stock'
        ];
    }

    /**
     * Generate meta tags for category page
     */
    public function getCategoryMeta(string $category): array
    {
        $productCount = Product::where('is_active', true)
            ->where('category', $category)
            ->count();
            
        $categoryTitle = ucfirst($category);
        
        return [
            'title' => "{$categoryTitle} Shoes - Shop Premium Footwear | ShoeVault",
            'description' => "Shop {$productCount}+ {$category} shoes from top brands. Find the perfect {$category} footwear with our easy reservation system.",
            'keywords' => "{$category} shoes, {$category} footwear, {$category} sneakers, premium {$category} shoes",
            'og_title' => "{$categoryTitle} Shoes - Shop Premium Footwear | ShoeVault",
            'og_description' => "Shop {$productCount}+ {$category} shoes from top brands.",
            'og_type' => 'website',
            'og_url' => url("/category/" . strtolower($category)),
            'canonical' => url("/category/" . strtolower($category)),
            'robots' => 'index, follow'
        ];
    }

    /**
     * Generate meta tags for brand page
     */
    public function getBrandMeta(string $brand): array
    {
        $productCount = Product::where('is_active', true)
            ->where('brand', $brand)
            ->count();
            
        return [
            'title' => "{$brand} Shoes - Authentic Collection | ShoeVault",
            'description' => "Shop authentic {$brand} shoes with {$productCount}+ styles available. Reserve your favorite {$brand} footwear online.",
            'keywords' => "{$brand} shoes, authentic {$brand}, {$brand} sneakers, {$brand} footwear",
            'og_title' => "{$brand} Shoes - Authentic Collection | ShoeVault",
            'og_description' => "Shop authentic {$brand} shoes with {$productCount}+ styles available.",
            'og_type' => 'website',
            'og_url' => url("/brand/" . strtolower(str_replace(' ', '-', $brand))),
            'canonical' => url("/brand/" . strtolower(str_replace(' ', '-', $brand))),
            'robots' => 'index, follow'
        ];
    }

    /**
     * Generate JSON-LD structured data for product
     */
    public function getProductStructuredData(Product $product): array
    {
        $sizes = $product->availableSizes->map(function($size) use ($product) {
            return [
                '@type' => 'Offer',
                'sku' => $product->sku . '-' . $size->size,
                'price' => $product->price,
                'priceCurrency' => 'PHP',
                'availability' => $size->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => url("/product/{$product->product_id}"),
                'additionalProperty' => [
                    '@type' => 'PropertyValue',
                    'name' => 'Size',
                    'value' => $size->size
                ]
            ];
        })->toArray();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->brand
            ],
            'description' => "Premium {$product->name} by {$product->brand}. Available in multiple sizes with online reservation.",
            'sku' => $product->sku,
            'image' => $product->image_url ?: url('/images/default-shoe.jpg'),
            'url' => url("/product/{$product->product_id}"),
            'category' => $product->category,
            'color' => $product->color,
            'offers' => $sizes,
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.5',
                'reviewCount' => '10'
            ]
        ];
    }

    /**
     * Generate JSON-LD structured data for organization
     */
    public function getOrganizationStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'ShoeVault',
            'description' => 'Premium shoe store and online reservation system',
            'url' => url('/'),
            'logo' => url('/images/logo.png'),
            'sameAs' => [
                // Add your social media URLs here
                // 'https://www.facebook.com/shoevault',
                // 'https://www.instagram.com/shoevault',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'url' => url('/form')
            ]
        ];
    }

    /**
     * Generate JSON-LD structured data for website
     */
    public function getWebsiteStructuredData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'ShoeVault',
            'description' => 'Premium shoe store and online reservation system',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/portal?search={search_term_string}'),
                'query-input' => 'required name=search_term_string'
            ]
        ];
    }
}