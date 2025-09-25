@if(isset($product))
<script>
  window.dataLayer = window.dataLayer || [];

  // запобіжник від повторного пушу
  if (!window._ga4ViewItemFired) {
    window._ga4ViewItemFired = true;

    window.dataLayer.push({
      event: "view_item",
      ecommerce: {
        currency: "{{ $currency ?? 'UAH' }}",
        value: {{ (float)($product->price ?? 0) }},
        items: [{
          item_id: "{{ $product->sku ?? $product->id }}",
          item_name: @json(optional($product->translations)->firstWhere('locale', app()->getLocale())->name ?? $product->name),
          // необов'язково, але корисно:
          // item_category: @json($product->category->name ?? null),
          // item_brand: @json($product->brand->name ?? 'Dream V Doma'),
          // item_variant: @json($product->color->name ?? null),
          price: {{ (float)($product->price ?? 0) }},
          quantity: 1
        }]
      }
    });
  }
</script>
@endif
