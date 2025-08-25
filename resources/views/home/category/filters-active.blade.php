@php
  $LIST_SEP = '_';
  $baseUrl  = route('category.show', ['locale'=>app()->getLocale(),'category'=>$slug]);

  $cur = [
    'sizes'     => $filters['sizes']  ?? [],
    'colors'    => $filters['colors'] ?? [],
    'min_price' => $filters['min_price'] ?? null,
    'max_price' => $filters['max_price'] ?? null,
  ];

  $gMin = (int)$priceRange['min'];
  $gMax = (int)$priceRange['max'];
  $curMin = (int)($cur['min_price'] ?? $gMin);
  $curMax = (int)($cur['max_price'] ?? $gMax);
  $hasPrice = ($curMin !== $gMin || $curMax !== $gMax);

  $buildUrl = function(array $f) use ($baseUrl, $LIST_SEP, $gMin, $gMax) {
    $parts = [];
    if (!empty($f['sizes']))  $parts[] = 'rozmir-'.implode($LIST_SEP, $f['sizes']);
    if (!empty($f['colors'])) $parts[] = 'kolir-'.implode($LIST_SEP, $f['colors']);
    $min = $f['min_price']; $max = $f['max_price'];
    if ($min !== null && $max !== null && ((int)$min !== (int)$gMin || (int)$max !== (int)$gMax)) {
      $parts[] = 'tsina-'.(int)$min.'-'.(int)$max;
    }
    return $parts ? ($baseUrl.'/'.implode('/', $parts)) : $baseUrl;
  };

  $chips = [];

  foreach ($cur['sizes'] as $s) {
    $nf = $cur;
    $nf['sizes'] = array_values(array_filter($cur['sizes'], fn($x) => $x !== $s));
    $chips[] = ['label' => __('Розмір').": {$s}", 'href' => $buildUrl($nf)];
  }

  foreach ($cur['colors'] as $c) {
    $nf = $cur;
    $nf['colors'] = array_values(array_filter($cur['colors'], fn($x) => $x !== $c));
    $chips[] = ['label' => __('Колір').": {$c}", 'href' => $buildUrl($nf)];
  }

  if ($hasPrice) {
    $nf = $cur;
    $nf['min_price'] = null; $nf['max_price'] = null;
    $chips[] = ['label' => __('Ціна').": {$curMin}–{$curMax} грн", 'href' => $buildUrl($nf)];
  }

  $clearAllUrl = $baseUrl;
@endphp

<section class="container mb-3">
  <div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center gap-2">
      @if(!empty($chips))
        @foreach($chips as $chip)
          <a href="{{ $chip['href'] }}" class="btn btn-sm btn-secondary">
            <i class="ci-close fs-sm ms-n1 me-1"></i> {{ $chip['label'] }}
          </a>
        @endforeach

        {{-- ВАЖЛИВО: цей лінк має повністю перезавантажити сторінку --}}
        <a href="{{ $clearAllUrl }}"
           class="btn btn-sm btn-secondary bg-transparent border-0 text-danger text-decoration-underline px-0 ms-2 js-clear-all">
          {{ __('Очистити всі') }}
        </a>
      @else
        <span class="text-body-secondary fs-sm">{{ __('Фільтри не застосовано') }}</span>
      @endif
    </div>
  </div>
  <hr class="d-lg-none my-3" />
</section>
