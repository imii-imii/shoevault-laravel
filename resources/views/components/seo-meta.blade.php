@props(['meta' => [], 'structuredData' => null])

<!-- SEO Meta Tags -->
<title>{{ $meta['title'] ?? 'ShoeVault - Premium Shoe Store' }}</title>
<meta name="description" content="{{ $meta['description'] ?? 'Premium shoe store with online reservation system' }}">
<meta name="keywords" content="{{ $meta['keywords'] ?? 'shoes, sneakers, footwear, online store' }}">
<meta name="robots" content="{{ $meta['robots'] ?? 'index, follow' }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">

<!-- Canonical URL -->
@if(isset($meta['canonical']))
<link rel="canonical" href="{{ $meta['canonical'] }}">
@endif

<!-- Open Graph Meta Tags -->
<meta property="og:title" content="{{ $meta['og_title'] ?? $meta['title'] ?? 'ShoeVault' }}">
<meta property="og:description" content="{{ $meta['og_description'] ?? $meta['description'] ?? 'Premium shoe store with online reservation system' }}">
<meta property="og:type" content="{{ $meta['og_type'] ?? 'website' }}">
<meta property="og:url" content="{{ $meta['og_url'] ?? request()->url() }}">
<meta property="og:site_name" content="ShoeVault">
@if(isset($meta['og_image']))
<meta property="og:image" content="{{ $meta['og_image'] }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
@endif

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta['og_title'] ?? $meta['title'] ?? 'ShoeVault' }}">
<meta name="twitter:description" content="{{ $meta['og_description'] ?? $meta['description'] }}">
@if(isset($meta['og_image']))
<meta name="twitter:image" content="{{ $meta['og_image'] }}">
@endif

<!-- Product-specific meta tags -->
@if(isset($meta['product:price:amount']))
<meta property="product:price:amount" content="{{ $meta['product:price:amount'] }}">
<meta property="product:price:currency" content="{{ $meta['product:price:currency'] }}">
<meta property="product:availability" content="{{ $meta['product:availability'] }}">
@endif

<!-- Additional meta tags -->
<meta name="author" content="ShoeVault">
<meta name="theme-color" content="#3498db">
<meta name="msapplication-TileColor" content="#3498db">

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('images/shoevault-logo.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/shoevault-logo.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/shoevault-logo.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/shoevault-logo.png') }}">
<link rel="shortcut icon" href="{{ asset('images/shoevault-logo.png') }}" type="image/png">

<!-- Structured Data (JSON-LD) -->
@if($structuredData)
<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif

<!-- Preconnect to external domains for performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://images.unsplash.com">

<!-- DNS prefetch for external resources -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link rel="dns-prefetch" href="//images.unsplash.com">