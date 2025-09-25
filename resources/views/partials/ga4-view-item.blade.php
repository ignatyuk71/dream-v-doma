@if(isset($product))
<script>
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    event: "view_item",
    ecommerce: {
      currency: "{{ $currency ?? 'UAH' }}",
      value: {{ (float)($product->price ?? 0) }},
      items: [{
        item_id: "{{ $product->sku ?? $product->id }}",
        item_name: @json($product->translations->firstWhere('locale', app()->getLocale())->name ?? $product->name),
        price: {{ (float)($product->price ?? 0) }},
        quantity: 1
      }]
    }
  });
</script>
@endif
