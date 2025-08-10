@extends('layouts.app')

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  <div class="container-lg">
    @include('components.navbar')
  </div>

  <div class="container-lg">
        <!-- Vue-компонент CheckoutPage -->
        <div id="checkout-page" data-locale="{{ app()->getLocale() }}"></div>
    </div>


  {{-- Footer --}}
  @include('home.footer')

@endsection
