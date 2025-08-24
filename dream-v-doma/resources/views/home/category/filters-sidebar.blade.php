<!-- Filters offcanvas (mobile) + static sidebar (desktop) -->
<div class="offcanvas-lg offcanvas-start" id="filterSidebar" tabindex="-1" aria-labelledby="filterSidebarLabel">
  <div class="offcanvas-header py-3 d-lg-none">
    <h5 class="offcanvas-title" id="filterSidebarLabel">{{ __('Фільтри та сортування') }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filterSidebar" aria-label="Close"></button>
  </div>

  <div class="offcanvas-body flex-column pt-2 py-lg-0">
    <form id="filtersForm" class="d-flex flex-column gap-3">

      {{-- Ціна --}}
      <div class="w-100 border rounded p-3 p-xl-4">
        <h4 class="h6 mb-3">{{ __('Ціна') }}</h4>
        @php
          $gMin   = (int)$priceRange['min'];
          $gMax   = (int)$priceRange['max'];
          $curMin = (int)($filters['min_price'] ?? $gMin);
          $curMax = (int)($filters['max_price'] ?? $gMax);

          $priceOptions = [
            ['id'=>'price-all','label'=>__('Без обмежень'),'min'=>$gMin,'max'=>$gMax],
            ['id'=>'price-1','label'=>'100 – 250 грн','min'=>100,'max'=>250],
            ['id'=>'price-2','label'=>'250 – 400 грн','min'=>250,'max'=>400],
            ['id'=>'price-3','label'=>'400 – 700 грн','min'=>400,'max'=>700],
          ];
          $isChecked = fn($min,$max,$curMin,$curMax) =>
            ((int)$min === (int)$curMin) && ((int)$max === (int)$curMax);
        @endphp
        <div class="d-flex flex-column gap-2">
          @foreach($priceOptions as $opt)
            <div class="form-check">
              <input
                type="radio"
                class="form-check-input js-price-radio"
                name="price-range"
                id="{{ $opt['id'] }}"
                data-min="{{ (int)$opt['min'] }}"
                data-max="{{ (int)$opt['max'] }}"
                {{ $isChecked($opt['min'],$opt['max'],$curMin,$curMax) ? 'checked' : '' }}
              >
              <label for="{{ $opt['id'] }}" class="form-check-label">{{ $opt['label'] }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Розміри --}}
      <div class="w-100 border rounded p-3 p-xl-4">
        <h4 class="h6">{{ __('Розмір') }}</h4>
        <div class="d-flex flex-column gap-1">
          @foreach(($facets['sizes'] ?? collect()) as $i => $size)
            @php $id = 'size-'.$i; @endphp
            <div class="form-check">
              <input
                type="checkbox"
                class="form-check-input"
                id="{{ $id }}"
                name="sizes[]"
                value="{{ $size }}"
                {{ in_array($size, $filters['sizes'] ?? []) ? 'checked' : '' }}
              >
              <label for="{{ $id }}" class="form-check-label">{{ $size }}</label>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Кольори --}}
      <div class="w-100 border rounded p-3 p-xl-4">
        <h4 class="h6">{{ __('Колір') }}</h4>
        <div class="d-flex flex-column gap-1">
          @foreach(($facets['colors'] ?? collect()) as $i => $color)
            @php $id = 'color-'.$i; @endphp
            <div class="form-check d-flex align-items-center gap-2">
              <input
                type="checkbox"
                class="form-check-input"
                id="{{ $id }}"
                name="colors[]"
                value="{{ $color }}"
                {{ in_array($color, $filters['colors'] ?? []) ? 'checked' : '' }}
              >
              <label for="{{ $id }}" class="form-check-label d-flex align-items-center gap-2">
                <span class="rounded-circle"
                      style="width:.875rem;height:.875rem;
                             background-color: {{ $color === 'білий' || strtolower($color)==='white' ? '#fff' : (str_starts_with($color,'#') ? $color : '#000') }};
                             {{ ($color === 'білий' || strtolower($color)==='white') ? 'border:1px solid #ddd' : '' }}"></span>
                {{ $color }}
              </label>
            </div>
          @endforeach
        </div>
      </div>

    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('filtersForm');
  if (!form) return;

  const LIST_SEP = '_';
  const base = @json(route('category.show', ['locale'=>app()->getLocale(),'category'=>$slug]));
  const gMin = {{ (int)$priceRange['min'] }};
  const gMax = {{ (int)$priceRange['max'] }};

  function buildUrl() {
    const sizes  = Array.from(form.querySelectorAll('input[name="sizes[]"]:checked')).map(i=>i.value);
    const colors = Array.from(form.querySelectorAll('input[name="colors[]"]:checked')).map(i=>i.value);

    const priceRadio = form.querySelector('.js-price-radio:checked');
    let min = '', max = '';
    if (priceRadio) {
      min = parseInt(priceRadio.dataset.min, 10);
      max = parseInt(priceRadio.dataset.max, 10);
    }

    const parts = [];
    if (sizes.length)  parts.push('rozmir-' + sizes.join(LIST_SEP));
    if (colors.length) parts.push('kolir-' + colors.join(LIST_SEP));
    if (min !== '' && max !== '' && (min !== gMin || max !== gMax)) {
      parts.push('tsina-' + min + '-' + max);
    }

    const qs = window.location.search || '';
    return (parts.length ? (base + '/' + parts.join('/')) : base) + qs;
  }

  function closeOffcanvasSafely() {
    const ocEl = document.getElementById('filterSidebar');
    if (!ocEl || typeof bootstrap === 'undefined') return;

    const oc = bootstrap.Offcanvas.getOrCreateInstance(ocEl);

    ocEl.addEventListener('hidden.bs.offcanvas', () => {
      document.querySelectorAll('.offcanvas-backdrop').forEach(b => b.remove());
      document.body.classList.remove('offcanvas-open', 'modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
      document.body.style.removeProperty('touch-action');
    }, { once: true });

    if (ocEl.classList.contains('show')) {
      oc.hide();
    }
  }

  async function ajaxGo(url) {
    history.pushState({}, '', url);
    document.body.classList.add('is-loading');

    try {
      const res = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-Partial': '1',
          'Accept': 'application/json'
        }
      });
      if (!res.ok) { window.location.href = url; return; }
      const data = await res.json();

      const chips = document.querySelector('.js-filters-active');
      const grid  = document.querySelector('.js-products');
      const pag   = document.querySelector('.js-pagination');

      if (chips && data.chips !== undefined) chips.innerHTML = data.chips;
      if (grid  && data.products !== undefined) grid.innerHTML = data.products;
      if (pag   && data.pagination !== undefined) pag.innerHTML = data.pagination;

      closeOffcanvasSafely();
    } catch (e) {
      window.location.href = url;
    } finally {
      document.body.classList.remove('is-loading');
    }
  }

  // Автозастосування при зміні будь-якого фільтра
  form.addEventListener('change', function (e) {
    if (
      e.target.matches('input[name="sizes[]"]') ||
      e.target.matches('input[name="colors[]"]') ||
      e.target.matches('.js-price-radio')
    ) {
      ajaxGo(buildUrl());
    }
  });

  // Делегування кліків: пагінація та чіпси — AJAX,
  // АЛЕ "Очистити всі" (class="js-clear-all") — повний reload
  document.addEventListener('click', function (e) {
    const link = e.target.closest('.js-pagination a, .js-filters-active a');
    if (!link) return;

    // Дозволяємо звичайний перехід для "Очистити всі"
    if (link.classList.contains('js-clear-all')) {
      return; // не чіпаємо — нехай перезавантажує сторінку
    }

    const url = link.getAttribute('href');
    if (!url) return;
    if (url.startsWith('http')) {
      const u = new URL(url);
      if (u.origin !== window.location.origin) return;
    }
    e.preventDefault();
    ajaxGo(url);
  });

  // Підтримка back/forward
  window.addEventListener('popstate', function () {
    ajaxGo(window.location.href);
  });
});
</script>
@endpush
