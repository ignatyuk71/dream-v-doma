<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

    <!-- SEO Meta Tags -->
    <title>@yield('title', 'Dream V Doma')</title>
    <meta name="description" content="Інтернет-магазин Dream V Doma — мода, комфорт і стиль вдома">
    <meta name="keywords" content="тапки, домашній одяг, вʼєтнамки, шльопанці, покупки онлайн, магазин">
    <meta name="author" content="Dream V Doma">

    <!-- Icons / Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/app-icons/icon-32x32.png" sizes="32x32">
    <link rel="apple-touch-icon" href="/assets/app-icons/icon-180x180.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font icons -->
    <link rel="preload" href="/assets/icons/cartzilla-icons.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/assets/icons/cartzilla-icons.min.css">

    <!-- Vendor styles -->
    <link rel="stylesheet" href="/assets/vendor/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/vendor/simplebar/dist/simplebar.min.css">

    <!-- Theme styles -->
    <link rel="preload" href="/assets/css/theme.min.css" as="style">
    <link rel="stylesheet" href="/assets/css/theme.min.css" id="theme-styles">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        @yield('content')
    </div>

    <!-- Vendor Scripts -->
    <script src="/assets/vendor/swiper/swiper-bundle.min.js" defer></script>
    <script src="/assets/vendor/simplebar/dist/simplebar.min.js" defer></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme switcher (залишено для меню/тем) -->
    <script src="/assets/js/theme-switcher.js"></script>




    <!-- Theme JS (залишено, але з глушилкою помилки count-input.js) -->
    <script>
      // ❗ Глушимо помилку count-input.js всередині theme.min.js
      window.addEventListener('error', function (e) {
        if (e.message?.includes('addEventListener') && e.filename?.includes('theme.min.js')) {
          e.preventDefault()
          console.warn('theme.min.js: count-input помилка приглушена')
        }
      })
    </script>
    <script src="/assets/js/theme.min.js" defer></script>

    <!-- Bootstrap components init (tooltip, offcanvas) -->
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
          new bootstrap.Tooltip(el)
        })
      })
    </script>

    @stack('scripts')
</body>
</html>
