<!doctype html>
@php
  $locale   = app()->getLocale() ?: 'uk';
  $dir      = in_array($locale, ['ar','he','fa']) ? 'rtl' : 'ltr';
  $assets   = asset('vendor/vuexy/assets') . '/'; // ОБОВʼЯЗКОВО з /
  $title    = trim($__env->yieldContent('title')) ?: 'Адмінка';
@endphp
<html lang="{{ $locale }}"
      dir="{{ $dir }}"
      class="layout-navbar-fixed layout-menu-fixed layout-compact"
      data-bs-theme="light"
      data-assets-path="{{ $assets }}"
      data-template="vertical-menu-template">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <meta name="robots" content="noindex, nofollow" />

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ $assets }}img/favicon/favicon.ico" />

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons (Tabler/Iconify) --}}
    <link rel="stylesheet" href="{{ $assets }}vendor/fonts/iconify-icons.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


    {{-- Core CSS (порядок важливий) --}}
    {{-- build:css assets/vendor/css/theme.css --}}
    <link rel="stylesheet" href="{{ $assets }}vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="{{ $assets }}vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="{{ $assets }}vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="{{ $assets }}vendor/css/core.css" />
    <link rel="stylesheet" href="{{ $assets }}css/demo.css" />
    {{-- endbuild --}}

    {{-- Сюди сторінки можуть пушити додаткові вендорні стилі (apexcharts, datatables, тощо) --}}
    @stack('vendor-styles')
    @stack('styles')
    {{-- Сюди сторінки можуть пушити свої локальні стилі --}}
    @stack('page-styles')
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css"/>

    {{-- Helpers + Customizer + Config (ВАЖЛИВО: в head і саме в такому порядку) --}}
    <script src="{{ $assets }}vendor/js/helpers.js"></script>
    <script src="{{ $assets }}vendor/js/template-customizer.js"></script>
    <script src="{{ $assets }}js/config.js"></script>

    {{-- Vite-бандл адмінки (без Bootstrap з CDN) --}}
    @vite(['resources/js/admin/index.js', 'resources/js/admin/index.css'])

    {{-- Якщо треба щось вставити у <head> з конкретної сторінки --}}
    @stack('head-scripts')
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        {{-- Sidebar --}}
        @includeIf('admin.layouts.sidenav')

        <!-- Layout container -->
        <div class="layout-page">
          {{-- Topbar --}}
          @includeIf('admin.layouts.nav')

          <!-- Content wrapper -->

            {{-- Основний контент --}}
          
              @yield('content')
          

            {{-- Footer --}}
            @includeIf('admin.layouts.footer')

            {{-- Місце для модалок сторінок --}}
            @stack('modals')
          <!-- / Content wrapper -->
        </div>
        <!-- / Layout container -->

        {{-- Мобільний тоглер меню --}}
        <div class="menu-mobile-toggler d-xl-none rounded-1">
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
            <i class="ti tabler-menu icon-base"></i>
            <i class="ti tabler-chevron-right icon-base"></i>
          </a>
        </div>
      </div>
    </div>
    <!-- / Layout wrapper -->

    {{-- Core JS (порядок важливий) --}}
    <script src="{{ $assets }}vendor/libs/jquery/jquery.js"></script>
    <script src="{{ $assets }}vendor/js/bootstrap.js"></script>
    <script src="{{ $assets }}vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ $assets }}vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ $assets }}vendor/js/menu.js"></script>
    <script src="{{ $assets }}js/main.js"></script>

    {{-- Вендорні скрипти, які потрібні конкретним сторінкам (apexcharts, datatables і т.п.) --}}
    @stack('vendor-scripts')

    {{-- Скрипти поточної сторінки --}}
    @stack('page-scripts')
  </body>
</html>
