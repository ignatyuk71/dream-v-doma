@extends('admin.layouts.vuexy')

@section('content')
  <div id="product-edit-app">
    <product-edit :product-id="{{ $product->id }}"></product-edit>
  </div>
@endsection
