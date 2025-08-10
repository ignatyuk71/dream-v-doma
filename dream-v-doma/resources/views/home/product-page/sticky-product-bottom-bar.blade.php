@php
    // Перше зображення з галереї, з перевіркою наявності
    $image = $product->images->first()->url ?? asset('assets/img/placeholder.svg');
    // Додаємо asset() і ltrim, якщо шлях відносний (як у галереї)
    $imageUrl = $product->images->first()
        ? asset(ltrim($product->images->first()->url, '/'))
        : asset('assets/img/placeholder.svg');
    $name = $product->translations->firstWhere('locale', app()->getLocale())?->name ?? '—';
@endphp

<section class="custom-sticky-float-bar position-fixed bottom-0 start-50 translate-middle-x w-100 d-md-none"
         style="max-width:420px; z-index:55;">
  <div class="d-flex align-items-center px-3 py-2">
    <!-- Фото -->
    <div class="ratio ratio-1x1 flex-shrink-0 bg-light rounded" style="width: 55px;">
      <img src="{{ $imageUrl }}"
           alt="{{ $name }}" class="rounded"
           style="object-fit:cover; width: 100%; height: 100%;">
    </div>
    <!-- Назва -->
    <div class="ms-3 flex-grow-1 min-w-0">
      <div class="fw-semibold text-truncate" style="max-width:170px;">
        {{ $name }}
      </div>
    </div>
  
    <div id="sticky-add-to-cart" data-product='@json($product)'></div>
  </div>
</section>
@push('styles')
    <style>
.custom-sticky-float-bar {
  border-radius: 5px 5px 0 0 !important;  /* округлений ВЕРХ */
  box-shadow: 0 8px 28px -10px rgba(20,20,60,0.16), 0 -2px 16px 0 rgba(0,0,0,0.07);
  background: #fff;
  overflow: visible; /* Щоб тінь не обрізалась */
  border: none;
}
    </style>
@endpush
