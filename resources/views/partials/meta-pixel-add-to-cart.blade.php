@if (config('services.meta_pixel.enabled') && config('services.meta_pixel.id'))
<script>
// Задаємо валюту глобально з бекенда
window.metaPixelCurrency = '{{ config('services.meta_pixel.default_currency', 'UAH') }}';

// Meta Pixel helper: AddToCart
window.trackAddToCart = function ({ id, sku, price, qty = 1, currency }) {
  if (!window.fbq) return;
  const pid = String(sku || id);
  const cur = currency || window.metaPixelCurrency || 'UAH';

  fbq('track', 'AddToCart', {
    content_ids: [pid],
    content_type: 'product',
    contents: [{ id: pid, quantity: Number(qty), item_price: Number(price) }],
    value: Number(price) * Number(qty),
    currency: cur
  });
};
</script>
@endif
