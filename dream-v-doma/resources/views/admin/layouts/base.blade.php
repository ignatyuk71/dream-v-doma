<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>

    <link href="{{ asset('/css/volt.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/sweetalert2/sweetalert2.min..css') }}" rel="stylesheet">
    <link href="{{ asset('/vendor/notyf/notyf.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  
   
    @include('admin.layouts.nav')
    @include('admin.layouts.sidenav')
    <main class="content">
    @include('admin.layouts.topbar')
    @yield('content')
    
    </main>
    <script src="{{ asset('/assets/js/volt.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    @include('admin.layouts.footer2')
</body>
</html>
