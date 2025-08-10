@extends('layouts.app')

@section('content')

  {{-- Topbar --}}
  @include('home.topbar')

  {{-- Navbar --}}
  <div class="container-lg">
    @include('components.navbar')
  </div>

  <div id="thank-you"></div>


  {{-- Footer --}}
  @include('home.footer')

@endsection
