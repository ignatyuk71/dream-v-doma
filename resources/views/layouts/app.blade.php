<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover">

  <title>@yield('title', 'Dream V Doma')</title>
  <meta name="description" content="Інтернет-магазин Dream V Doma — мода, комфорт і стиль вдома">
  <meta name="keywords" content="тапки, домашній одяг, вʼєтнамки, шльопанці, покупки онлайн, магазин">
  <meta name="author" content="Dream V Doma">

  {{-- Google Analytics gtm --}}
  @include('partials.gtm-head')

  {{-- Meta Pixel --}}
  @include('partials.meta-pixel-script')
  @include('partials.meta-pixel-add-to-cart')

  <!-- TikTok Pixel Code Start -->
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


  ttq.load('D3HCCKBC77U8DNMA1VJ0');
  ttq.page();
}(window, document, 'ttq');
</script>
<!-- TikTok Pixel Code End -->
 

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
  <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>

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
  <link rel="stylesheet" href="/assets/icons/cartzilla-icons.min.css">
  
  <!-- Vendor CSS -->
  <link rel="preload" href="/assets/vendor/swiper/swiper-bundle.min.css" as="style">
  <link rel="preload" href="/assets/vendor/simplebar/dist/simplebar.min.css" as="style">
  <link rel="stylesheet" href="/assets/vendor/swiper/swiper-bundle.min.css">
  <link rel="stylesheet" href="/assets/vendor/simplebar/dist/simplebar.min.css">

  <!-- Theme CSS -->
  <link rel="preload" as="style" href="/assets/css/theme.min.css">
  <link rel="stylesheet" href="/assets/css/theme.min.css" id="theme-styles">

  <!-- LCP image preload (optional) -->
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
  {{-- NOSCRIPT Pixel — одразу після <body> --}}
  @include('partials.meta-pixel-noscript')
  @include('partials.gtm-body')

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
