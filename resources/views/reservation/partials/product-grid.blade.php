@forelse($products as $product)
<div class="res-portal-product-card" 
     data-product-id="{{ $product->id }}"
     data-product-name="{{ $product->name }}"
     data-product-brand="{{ $product->brand }}"
     data-product-price="{{ number_format($product->price, 2) }}"
     data-product-category="{{ $product->category }}">
  <div class="res-portal-product-img">
    @if($product->image_url)
      <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
    @endif
  </div>
  <div class="res-portal-product-title">{{ $product->name }}</div>
  <div class="res-portal-product-brand">{{ $product->brand }}</div>
  <div class="res-portal-product-price">₱ {{ number_format($product->price, 0) }}</div>
  <div class="res-portal-product-stock">Stocks: {{ $product->getTotalStock() }}</div>
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