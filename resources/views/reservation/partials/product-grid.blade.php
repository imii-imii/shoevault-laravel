@forelse($products as $product)
@php $cat = strtolower($product->category ?? ''); @endphp
<div class="res-portal-product-card" 
     data-product-id="{{ $product->id }}"
     data-product-name="{{ $product->name }}"
     data-product-brand="{{ $product->brand }}"
     data-product-price="{{ number_format($product->price, 2) }}"
     data-product-category="{{ $product->category }}">
  <!-- Category tag top-right -->
  <div class="res-portal-category-tag" style="position:absolute;top:10px;right:10px;color:#fff;font-size:.7rem;font-weight:700;padding:4px 8px;border-radius:999px;letter-spacing:.5px;
    {{ $cat==='men' ? 'background:linear-gradient(135deg,#3b82f6,#1d4ed8)' : ($cat==='women' ? 'background:linear-gradient(135deg,#ec4899,#be185d)' : 'background:linear-gradient(135deg,#10b981,#047857)') }} ">
    {{ strtoupper($product->category) }}
  </div>

  <!-- Top image spanning full width with rounded top corners -->
  <div class="res-portal-product-img card-image-top">
    @if($product->image_url)
      <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" loading="lazy">
    @else
      <i class="fas fa-image" style="font-size:2.2rem;color:#a0aec0;"></i>
    @endif
  </div>

  <div class="res-portal-product-info">
    <div class="res-portal-product-title">{{ $product->name }}</div>
    <div class="res-portal-product-brand">{{ strtoupper($product->brand) }}</div>
    <div class="res-portal-product-color">{{ $product->color ?: '—' }}</div>
    <div class="res-portal-product-category" style="display:none;">{{ ucfirst($product->category) }}</div>
    <span class="res-portal-product-price">₱ {{ number_format((float)$product->price, 0, '.', ',') }}</span>
    <span class="res-portal-product-stock">{{ $product->getTotalStock() }} in stock</span>
    <div class="res-portal-product-sizes">Sizes: {{ $product->sizes->pluck('size')->implode(', ') }}</div>
  </div>

  <button class="res-portal-add-cart-btn" 
          data-available-sizes="{{ $product->sizes->where('is_available', true)->where('stock', '>', 0)->pluck('size')->join(',') }}">
    Add to Cart
  </button>
</div>
@empty
<div class="no-products-message">
  <div class="no-products-icon">
    <i class="fas fa-box-open"></i>
  </div>
  <h3>No Products Available</h3>
  <p>We're currently updating our inventory. Please check back soon!</p>
</div>
@endforelse