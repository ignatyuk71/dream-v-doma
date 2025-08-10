@extends('admin.layouts.app')

@section('content')
<div id="category-add-app">
    <category-add :categories='@json($categories)'></category-add>
</div>
@endsection