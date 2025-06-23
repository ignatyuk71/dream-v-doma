@extends('layouts.app')

@section('title', __('About Us'))

@section('content')
<div class="container py-5">
    <h1>{{ __('category_men') }}</h1>
    <p>{{ __('about_demo_text') }}</p>
    <p>{{ __('current_language') }} <strong>{{ $locale }}</strong></p>

    <a href="{{ route('about', ['locale' => 'ua']) }}" class="btn btn-primary me-2">UA</a>
    <a href="{{ route('about', ['locale' => 'ru']) }}" class="btn btn-secondary">RU</a>
</div>
@endsection
