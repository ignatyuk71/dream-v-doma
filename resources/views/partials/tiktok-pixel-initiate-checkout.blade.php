{{-- resources/views/partials/tiktok-pixel-initiate-checkout.blade.php --}}
<script>
/**
 * TikTok InitiateCheckout — BROWSER pixel only
 * Працює як "обгортка" над window.mpTrackIC, нічого не ламає:
 *  - якщо mpTrackIC вже є → викличе спочатку оригінал, потім TikTok.
 *  - якщо немає → створить сумісну функцію, яку можна викликати з Vue.
 *
 * Очікуваний формат:
 * window.mpTrackIC({
 *   items: [{ variant_sku, price, quantity, name } ...], // price — за одиницю (number)
 *   currency: 'UAH'|'PLN'|...,
 *   value?: number // якщо не вкажеш — порахуємо як Σ(price*quantity)
 * })
 */

(function () {
  const DEFAULT_CURRENCY = (window.metaPixelCurrency || 'UAH');

  // Антидубль від частих викликів (і від випадкових дабл-кліків)
  let _ttLastIcTs = 0;

  // (опц.) щоб не дублювати подію після рефрешу сторінки — ввімкни прапор нижче
  const USE_SESSION_FLAG = false; // ← якщо треба, постав true
  const SESSION_FLAG_KEY = 'tt_ic_fired';

  function toNum(v){ const n = Number(String(v ?? '').replace(',', '.').replace(/[^\d.\-]/g,'')); return Number.isFinite(n)? n : 0; }
  function normItem(x){
    const id  = String(x?.variant_sku ?? x?.content_id ?? '').trim();
    const qty = Math.max(1, toNum(x?.quantity ?? 1));
    const pr  = toNum(x?.price ?? 0);
    const nm  = (x?.name ?? '').toString();
    return { id, qty, pr, nm };
  }

  function buildPayload(data){
    const items = Array.isArray(data?.items) ? data.items.map(normItem).filter(i => i.id) : [];
    const currency = String(data?.currency || DEFAULT_CURRENCY);
    let total = toNum(data?.value);

    if (!total && items.length){
      total = items.reduce((s,i)=> s + i.pr * i.qty, 0);
    }

    return {
      value: total || 0,
      currency,
      contents: items.map(i => ({
        content_id: i.id,
        content_type: 'product',
        content_name: i.nm || undefined,
        quantity: i.qty,
        price: i.pr
      }))
    };
  }

  function trackTikTokIC(data){
    if (!(window.ttq && typeof window.ttq.track === 'function')) {
      console.warn('[TikTok] ttq не знайдений — InitiateCheckout не надіслано');
      return;
    }

    if (USE_SESSION_FLAG && sessionStorage.getItem(SESSION_FLAG_KEY) === '1') {
      return; // вже слали в цій сесії
    }

    const now = Date.now();
    if (now - _ttLastIcTs < 400) return; // антиспам 400мс
    _ttLastIcTs = now;

    const payload = buildPayload(data);
    if (!payload.contents || !payload.contents.length){
      console.warn('[TikTok] InitiateCheckout: порожній contents — подію пропущено', data);
      return;
    }

    ttq.track('InitiateCheckout', payload);
    console.log('[TikTok] InitiateCheckout payload', payload);

    if (USE_SESSION_FLAG) sessionStorage.setItem(SESSION_FLAG_KEY, '1');
  }

  // Обгортаємо mpTrackIC рівно один раз
  (function wrapMpTrackICOnce(){
    if (window.__ttWrappedMpIC) return;
    window.__ttWrappedMpIC = true;

    const original = (typeof window.mpTrackIC === 'function') ? window.mpTrackIC : null;

    window.mpTrackIC = function(data){
      try { if (original) original.call(this, data); } catch(e){ console.warn('[mpTrackIC original error]', e); }
      try { trackTikTokIC(data || {}); }             catch(e){ console.warn('[TikTok IC error]', e); }
    };
  })();

  // (опц.) Додатковий делегат на кнопки з атрибутом data-tt-initiate-checkout
  document.addEventListener('click', function(e){
    const btn = e.target.closest('[data-tt-initiate-checkout]');
    if (!btn) return;

    // Якщо у кнопки є JSON у data-tt-initiate-checkout — беремо його
    let payload = null;
    try {
      const raw = btn.getAttribute('data-tt-initiate-checkout');
      if (raw && raw.trim() && raw.trim() !== 'true') {
        const parsed = JSON.parse(raw);
        if (parsed && typeof parsed === 'object') payload = parsed;
      }
    } catch(_) {}

    // Інакше — якщо на сторінці є глобальний __CART__ (можеш покласти його з Vue)
    if (!payload && window.__CART__ && Array.isArray(window.__CART__?.items)) {
      payload = {
        items: window.__CART__.items,
        value: toNum(window.__CART__.total),
        currency: window.__CART__.currency || DEFAULT_CURRENCY
      };
    }

    if (payload) trackTikTokIC(payload);
  }, { passive: true });

})();
</script>
