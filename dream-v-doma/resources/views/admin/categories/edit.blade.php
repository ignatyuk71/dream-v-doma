@extends('admin.layouts.app')

@section('content')
<div id="category-edit-app">
    <category-edit :category='@json($category)' :categories='@json($categories)'></category-edit>
</div>
@endsection