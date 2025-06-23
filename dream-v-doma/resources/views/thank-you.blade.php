@extends('layouts.app')

@section('content')
<topbar></topbar>
<cart-offcanvas></cart-offcanvas>
<div class="container-lg">
<!-- Navbar -->
    <navbar></navbar>
    </div>
    <!-- Основний контент -->
    <div id="thank-you"></div>


<!-- Footer -->
<footer-component></footer-component>
@endsection
