@extends('layouts.app')

@section('content')
<topbar></topbar>
<div class="container-lg">
<cart-offcanvas></cart-offcanvas>
    <!-- Topbar -->
    

    <!-- Navbar -->
    <navbar></navbar>

    <!-- Main content -->
    
        <!-- Vue-продукт -->
        <div id="product-page"
             data-product='@json($product)'
             data-locale="{{ app()->getLocale() }}">
        </div>
    </div>

    <!-- Footer -->
    <footer-component></footer-component>

@endsection
