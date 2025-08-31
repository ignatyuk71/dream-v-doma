<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

  <title>@yield('title', 'Dream V Doma')</title>
  <meta name="description" content="Інтернет-магазин Dream V Doma — мода, комфорт і стиль вдома">
  <meta name="keywords" content="тапки, домашній одяг, вʼєтнамки, шльопанці, покупки онлайн, магазин">
  <meta name="author" content="Dream V Doma">

  {{-- Meta Pixel (скрипт і init) --}}
  @include('partials.meta-pixel-script')

  <!-- PWA / icons -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <link rel="icon" type="image/png" href="/assets/app-icons/icon-32x32.png" sizes="32x32">
  <link rel="apple-touch-icon" href="/assets/app-icons/icon-180x180.png">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#ff6b6b">
  <!-- Theme switcher має бути рано -->
  <script src="/assets/js/theme-switcher.js"></script>

  <!-- Fonts (твій Manrope) -->
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Icons + vendor CSS -->
  <link rel="stylesheet" href="/assets/icons/cartzilla-icons.min.css">
  <link rel="stylesheet" href="/assets/vendor/swiper/swiper-bundle.min.css">
  <link rel="stylesheet" href="/assets/vendor/simplebar/dist/simplebar.min.css">

  <!-- Theme CSS (без RTL preload) -->
  <link rel="preload" href="/assets/css/theme.min.css" as="style">
  <link rel="stylesheet" href="/assets/css/theme.min.css" id="theme-styles">

  @vite(['resources/css/app-index.css', 'resources/js/app.js'])
  @stack('styles')
</head>
<body>
  {{-- NOSCRIPT — одразу після <body> --}}
  @include('partials.meta-pixel-noscript')



  @yield('content')
  <div id="cart-offcanvas"></div>

  <!-- Vendor JS -->
  <script src="/assets/vendor/swiper/swiper-bundle.min.js" defer></script>
  <script src="/assets/vendor/simplebar/dist/simplebar.min.js" defer></script>

  <!-- У Cartzilla Bootstrap усередині theme.min.js -->
  <script src="/assets/js/theme.min.js" defer></script>

  <!-- Ініт дрібниць -->
  <script defer>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el)
      })
    })
  </script>

  <!-- Глобальний toast -->
  <div id="global-toast-container" class="toast-top-center"></div>

  @stack('scripts')
</body>
</html>
