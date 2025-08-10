@extends('layouts.app')

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  <div class="container-lg">
    @include('components.navbar')
  </div>

  {{-- Контент категорії --}}
  @include('home.category.category-page')


  {{-- Footer --}}
  @include('home.footer')

@endsection
