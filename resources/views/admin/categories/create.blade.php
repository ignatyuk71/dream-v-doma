@extends('admin.layouts.vuexy')

@section('content')
<div id="category-add-app">
    <category-add :categories='@json($categories)'></category-add>
</div>
@endsection