@extends('admin.layouts.app')

@section('content')
  <div id="product-list" data-products="{{ json_encode($products) }}"></div>
@endsection