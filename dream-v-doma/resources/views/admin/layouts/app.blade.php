<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Адмінка')</title>

    {{-- Стили --}}
    <link href="{{ asset('/css/volt.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/notyf/notyf.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/app.js')
</head>
<body>
    {{-- Vue-контейнер тільки для Toast --}}
    <div id="app">
        <Toast />
    </div>
    <div id="toast-root"></div>

    <div class="d-flex">
        @include('admin.layouts.sidenav')

        <main class="content flex-grow-1">
            @include('admin.layouts.nav')
            @include('admin.layouts.topbar')
            @yield('content')
            @include('admin.layouts.footer2')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
