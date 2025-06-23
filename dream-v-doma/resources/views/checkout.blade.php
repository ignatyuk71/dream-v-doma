@extends('layouts.app')

@section('content')

<cart-offcanvas></cart-offcanvas>

<topbar></topbar>

<div class="container-lg">
<navbar></navbar>


    <!-- Vue-компонент CheckoutPage -->
    <div id="checkout-page" data-locale="{{ app()->getLocale() }}"></div>
</div>

<footer-component></footer-component>

@endsection
