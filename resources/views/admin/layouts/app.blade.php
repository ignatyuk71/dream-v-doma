<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Адмінка')</title>


  <!-- Стилі Bootstrap та шрифти -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 

  <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">



  @stack('styles')
</head>
<body>
    <div class="d-flex min-vh-100">

      {{-- Бокове меню --}}
      @include('admin.layouts.nav')

      {{-- Основний контент --}}
      <main class="content-wrapper">
        @include('admin.layouts.topbar')

     
        @yield('content')
       

        @include('admin.layouts.footer')
      </main>

    </div>

  <!-- Скрипти Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
