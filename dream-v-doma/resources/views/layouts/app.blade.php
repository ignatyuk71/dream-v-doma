<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dream V Doma')</title>

  <meta name="description" content="Інтернет-магазин Dream V Doma — мода, комфорт і стиль вдома">
  <meta name="keywords" content="тапки, домашній одяг, вʼєтнамки, шльопанці, покупки онлайн, магазин">
  <meta name="author" content="Dream V Doma">

  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/png" href="/assets/app-icons/icon-32x32.png">
  <link rel="apple-touch-icon" href="/assets/app-icons/icon-180x180.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">

  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/icons/cartzilla-icons.min.css">
  <link rel="stylesheet" href="/assets/vendor/swiper/swiper-bundle.min.css">
  <link rel="stylesheet" href="/assets/vendor/simplebar/dist/simplebar.min.css">
  <link rel="stylesheet" href="/assets/css/theme.min.css" id="theme-styles">

  @vite(['resources/css/app-index.css', 'resources/js/app.js'])
  @stack('styles')
</head>
<body>
<div id="app">
  @yield('content')
  <div id="cart-offcanvas"></div>
</div>

<script src="/assets/vendor/swiper/swiper-bundle.min.js" defer></script>
<script src="/assets/vendor/simplebar/dist/simplebar.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/theme-switcher.js"></script>

<script>
  window.addEventListener('error', function (e) {
    if (e.message?.includes('addEventListener') && e.filename?.includes('theme.min.js')) {
      e.preventDefault()
      //console.warn('theme.min.js: count-input помилка приглушена')
    }
  })
</script>
<script src="/assets/js/theme.min.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
      new bootstrap.Tooltip(el)
    })
  })
</script>

<!-- === GLOBAL TOAST === -->
<div id="global-toast-container" class="toast-top-center"></div>


@stack('scripts')
</body>
</html>
