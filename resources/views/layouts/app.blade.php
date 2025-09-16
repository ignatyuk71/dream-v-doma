<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

  <!-- Заборона індексації (стейджинг) -->
  <meta name="robots" content="noindex, nofollow, noarchive">

  <title>@yield('title', 'Dream V Doma')</title>
  <meta name="description" content="Інтернет-магазин Dream V Doma — мода, комфорт і стиль вдома">
  <meta name="keywords" content="тапки, домашній одяг, вʼєтнамки, шльопанці, покупки онлайн, магазин">
  <meta name="author" content="Dream V Doma">

  {{-- Meta Pixel --}}
  @include('partials.meta-pixel-script')
  @include('partials.meta-pixel-add-to-cart')

  <!-- PWA / icons -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <link rel="icon" type="image/png" href="/assets/app-icons/icon-32x32.png" sizes="32x32">
  <link rel="apple-touch-icon" href="/assets/app-icons/icon-180x180.png">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#ff6b6b">

  <!-- Tiny inline theme to avoid FOUC -->
  <script>
    try{var t=localStorage.getItem('theme'); if(t){document.documentElement.setAttribute('data-bs-theme', t)}}catch(e){}
  </script>
  <script src="/assets/js/theme-switcher.js" defer></script>

  <!-- Preconnects -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://connect.facebook.net" crossorigin>

  <!-- Google Fonts (async) -->
  <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap">
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap"
        media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap">
  </noscript>

  <!-- ICONS (Cartzilla) — ПОВЕРНУЛИ -->
  <link rel="preload" href="/assets/icons/cartzilla-icons.min.css" as="style">
  <link rel="stylesheet" href="/assets/icons/cartzilla-icons.min.css">

  <!-- Vendor CSS -->
  <link rel="preload" href="/assets/vendor/swiper/swiper-bundle.min.css" as="style">
  <link rel="preload" href="/assets/vendor/simplebar/dist/simplebar.min.css" as="style">
  <link rel="stylesheet" href="/assets/vendor/swiper/swiper-bundle.min.css">
  <link rel="stylesheet" href="/assets/vendor/simplebar/dist/simplebar.min.css">

  <!-- Theme CSS -->
  <link rel="preload" as="style" href="/assets/css/theme.min.css">
  <link rel="stylesheet" href="/assets/css/theme.min.css" id="theme-styles">

  <!-- LCP image preload (підстав свій шлях) -->
  {{-- 
  <link rel="preload" as="image"
        href="/storage/hero-1200.webp"
        imagesrcset="/storage/hero-800.webp 800w, /storage/hero-1200.webp 1200w"
        imagesizes="(max-width:768px) 100vw, 50vw"
        fetchpriority="high">
  --}}

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
