@extends('layouts.app')

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  @include('components.navbar')

  {{-- Breadcrumbs --}}
  <div class="container-lg">
    @php
      $locale = app()->getLocale();
      $items = [
        ['text' => __('Головна'), 'href' => url("/$locale"), 'active' => false],
        ['text' => __('Оформлення замовлення'), 'href' => '', 'active' => true],
      ];
    @endphp
    @include('home.shared.breadcrumbs', ['items' => $items])

    <!-- Vue-компонент CheckoutPage -->
    <div id="checkout-page" data-locale="{{ app()->getLocale() }}"></div>
  </div>

  {{-- Footer --}}
  @include('home.footer')

@endsection
