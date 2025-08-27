@php
  use Illuminate\Support\Str;

  // Хелпер: нормалізує шлях до публічного URL
  $toPublicUrl = function ($path) {
      if (empty($path)) {
          return asset('assets/img/placeholder.svg');
      }
      if (Str::startsWith($path, ['http://', 'https://', '//'])) {
          return $path; // вже абсолютний URL
      }
      $p = ltrim($path, '/');

      // якщо вже /storage/...
      if (Str::startsWith($p, 'storage/')) {
          return asset($p);
      }

      // прибираємо префікси "public/" або "app/public/"
      $p = preg_replace('#^(?:app/)?public/#', '', $p);

      // повертаємо як /storage/{p}
      return asset('storage/'.$p);
  };

  $currentLocale = app()->getLocale();

  // Перше зображення / плейсхолдер
  $imageUrl = $toPublicUrl($product->images->first()?->url ?? null);

  // Назва поточною мовою
  $name = $product->translations->firstWhere('locale', $currentLocale)?->name
          ?? ($product->translations->first()->name ?? '—');

  // Дані для кнопки
  $payload = [
    'id'    => $product->id,
    'slug'  => $product->slug,
    'price' => $product->price,
    'name'  => $name,
    'images' => $product->images->map(fn($img) => [
      'full_url' => $toPublicUrl($img->url),
    ])->values(),
    'translations' => $product->translations->map(fn($t) => [
      'locale' => $t->locale,
      'name'   => $t->name,
    ])->values(),
    'variants' => $product->variants->map(fn($v) => [
      'size'           => $v->size,
      'price_override' => $v->price_override,
      'old_price'      => $v->old_price ?? null,
    ])->values(),
  ];
@endphp

<section class="custom-sticky-float-bar position-fixed bottom-0 start-50 translate-middle-x w-100 d-md-none"
         style="max-width:420px; z-index:55;">
  <div class="d-flex align-items-center px-3 py-2">
    <!-- Фото -->
    <div class="ratio ratio-1x1 flex-shrink-0 bg-light rounded" style="width: 55px;">
      <img src="{{ $imageUrl }}" alt="{{ $name }}" class="rounded"
           style="object-fit:cover; width: 100%; height: 100%;">
    </div>

    <!-- Назва -->
    <div class="ms-3 flex-grow-1 min-w-0">
      <div class="fw-semibold text-truncate" style="max-width:170px;">
        {{ $name }}
      </div>
    </div>

    <!-- Кнопка sticky -->
    <div id="sticky-add-to-cart" data-product='@json($payload)'></div>
  </div>
</section>

@push('styles')
  <style>
    .custom-sticky-float-bar{
      border-radius: 5px 5px 0 0 !important;
      box-shadow: 0 8px 28px -10px rgba(20,20,60,0.16), 0 -2px 16px 0 rgba(0,0,0,0.07);
      background:#fff; overflow:visible; border:none;
    }
  </style>
@endpush
