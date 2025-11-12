<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Models\Product;
use App\Models\ProductSize;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * Generate XML sitemap for the website
     */
    public function sitemap()
    {
        // Get all active products
        $products = Product::where('is_active', true)
            ->with('availableSizes')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Define static pages with their priorities and change frequencies
        $staticPages = [
            [
                'url' => '/',
                'lastmod' => Carbon::now()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '1.0'
            ],
            [
                'url' => '/portal',
                'lastmod' => Carbon::now()->format('Y-m-d'),
                'changefreq' => 'daily',
                'priority' => '0.9'
            ],
            [
                'url' => '/form',
                'lastmod' => Carbon::now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ],
            [
                'url' => '/size-converter',
                'lastmod' => Carbon::now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.6'
            ],
            [
                'url' => '/customer/login',
                'lastmod' => Carbon::now()->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ]
        ];

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add static pages
        foreach ($staticPages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url($page['url']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $page['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Add product pages (if you have individual product pages)
        foreach ($products as $product) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url('/product/' . $product->product_id) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $product->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Add category pages
        $categories = Product::where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->unique();

        foreach ($categories as $category) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url('/category/' . strtolower($category)) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Add brand pages
        $brands = Product::where('is_active', true)
            ->distinct()
            ->pluck('brand')
            ->filter()
            ->unique();

        foreach ($brands as $brand) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url('/brand/' . strtolower(str_replace(' ', '-', $brand))) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /pos/\n";
        $content .= "Disallow: /inventory/\n";
        $content .= "Disallow: /owner/\n";
        $content .= "Disallow: /debug/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /customer/login\n";
        $content .= "Disallow: /customer/register\n";
        $content .= "Disallow: /test-*\n";
        $content .= "\n";
        $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}