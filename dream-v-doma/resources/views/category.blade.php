@extends('layouts.app')

{{-- 🔗 Canonical для SEO --}}
@push('meta')
@php
  $canonParts = [];
  if (!empty($filters['sizes']))  $canonParts[] = 'rozmir-'.implode('_', $filters['sizes']);
  if (!empty($filters['colors'])) $canonParts[] = 'kolir-'.implode('_', $filters['colors']);

  $curMin = isset($filters['min_price']) ? (int)$filters['min_price'] : (int)$priceRange['min'];
  $curMax = isset($filters['max_price']) ? (int)$filters['max_price'] : (int)$priceRange['max'];
  $gMin   = (int)$priceRange['min'];
  $gMax   = (int)$priceRange['max'];
  if ($curMin !== $gMin || $curMax !== $gMax) {
      $canonParts[] = 'tsina-'.$curMin.'-'.$curMax;
  }

  $canonical = empty($canonParts)
      ? route('category.show', ['locale'=>app()->getLocale(),'category'=>$slug])
      : route('category.filtered', ['locale'=>app()->getLocale(),'category'=>$slug,'filters'=>implode('/', $canonParts)]);
@endphp
<link rel="canonical" href="{{ $canonical }}">
@endpush

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  <div class="container-lg">
    @include('components.navbar')
  </div>

  <main class="content-wrapper">

    <div class="container-lg">
      {{-- Хлібні крихти --}}
      @include('home.shared.breadcrumbs', ['items' => $items])
    </div>

    {{-- Верхні банери --}}
    @include('home.category.banner')

    {{-- Заголовок категорії --}}
    <div class="container-lg mb-4 mt-3">
      <h1 class="h3">{{ $translation?->name ?? __('Каталог') }}</h1>
    </div>

    {{-- Активні фільтри (обгортка для AJAX) --}}
    <div class="js-filters-active">
      @include('home.category.filters-active')
    </div>

    <section class="container pb-5 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
      <div class="row">

        {{-- Sidebar фільтри --}}
        <aside class="col-lg-2 col-md-3">
          @include('home.category.filters-sidebar')
        </aside>

        {{-- Контент з товарами --}}
        <div class="col-lg-10">

          {{-- Кнопка відкриття фільтрів на мобільних --}}
          @include('home.category.filters-button-mobile')

          {{-- Сітка товарів (обгортка для AJAX) --}}
          <div class="js-products">
            @include('home.category.products-grid')
          </div>

          {{-- Нижній банер --}}
          @include('home.category.banner-bottom')

          {{-- Пагінація (обгортка для AJAX) --}}
          <div class="js-pagination">
            @include('home.category.pagination')
          </div>

          {{-- Блоки опису категорії --}}
          @include('home.category.description-blocks', ['blocks' => $categoryBlocks, 'translation' => $translation])

        </div>
      </div>
    </section>
  </main>

  {{-- Footer --}}
  @include('home.footer')

@endsection
