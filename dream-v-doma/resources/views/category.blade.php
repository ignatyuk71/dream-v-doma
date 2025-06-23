@extends('layouts.app')

@section('content')

<topbar></topbar>
<cart-offcanvas></cart-offcanvas>

<div class="container-lg">
    <navbar></navbar>
</div>
<div id="category-page"
     data-category='@json($category)'
     data-products='@json($category->products)'
     data-slug="{{ $category->translations->firstWhere('locale', app()->getLocale())?->slug }}">
</div>

<footer-component></footer-component>

@endsection
