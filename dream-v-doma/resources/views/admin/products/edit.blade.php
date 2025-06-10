@extends('admin.layouts.app')

@section('content')
    <div id="product-edit"
         data-product='@json($product)'
         data-categories='@json($categories)'></div>
@endsection
