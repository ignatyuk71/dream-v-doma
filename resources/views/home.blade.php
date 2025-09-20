@extends('layouts.app')

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  <div class="container-lg">
    @include('components.navbar')
  </div>

  <main class="content-wrapper">
    {{-- Hero Banner --}}
    @include('home.hero-banner')

   
    @include('home.featured-products')

      {{-- Special Offers --}}
      {{-- @include('home.special-offers') --}}
   

    {{-- Product Carousel --}}
    @include('home.shared.product-carousel')


      


    {{-- Instagram Feed --}}

        @include('home.shared.instagram-feed')



  </main>
  

  {{-- Back to top --}}
  @include('home.back-to-top')

  {{-- Footer --}}
  @include('home.footer')
@endsection
