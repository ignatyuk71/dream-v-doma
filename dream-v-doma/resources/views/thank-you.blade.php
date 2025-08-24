@extends('layouts.app')

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  <div class="container-lg">
    @include('components.navbar')

  {{-- Breadcrumbs --}}
    @php
      $locale = app()->getLocale();
      $items = [
        ['text' => __('Головна'), 'href' => url("/$locale"), 'active' => false],
        ['text' => __('Дякуємо'), 'href' => '', 'active' => true],
      ];
    @endphp
    @include('home.shared.breadcrumbs', ['items' => $items])


  {{-- Thank you (Vue) --}}
    <div id="thank-you"></div>
  </div>

  {{-- Footer --}}
  @include('home.footer')

@endsection
