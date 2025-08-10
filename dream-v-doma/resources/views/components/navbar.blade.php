@php
  use Illuminate\Support\Facades\App;
  $locale = App::getLocale();
@endphp

@push('styles')
  <style>
    .nav-link {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
    }

    @media (max-width: 576px) {
      .navbar-brand {
        font-size: 1.1rem !important;
      }
      .navbar .container,
      .container.py-1.py-lg-3 {
        padding-left: 8px !important;
        padding-right: 8px !important;
      }
    }
  </style>
@endpush

<header class="navbar navbar-expand-lg navbar-sticky bg-body d-block z-fixed p-0" data-sticky-navbar='{"offset": 500}'>
  <div class="container py-2 py-lg-3">
    <div class="d-flex align-items-center gap-3">
      <!-- Mobile offcanvas menu toggler (Hamburger) -->
      <button type="button" class="navbar-toggler me-4 me-md-2" data-bs-toggle="offcanvas" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <!-- Navbar brand (Logo) -->
    <a class="navbar-brand fs-2 py-0 m-0 me-auto me-sm-n5" href="{{ url($locale) }}">
      DREAM V DOMA
    </a>

    <!-- Button group -->
    <div class="d-flex align-items-center">
      <!-- Navbar stuck nav toggler -->
      <button type="button" class="navbar-toggler d-none navbar-stuck-show me-3" data-bs-toggle="collapse" data-bs-target="#stuckNav" aria-controls="stuckNav" aria-expanded="false" aria-label="Toggle navigation in navbar stuck state">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- Account button (desktop) -->
      <a class="btn btn-icon btn-lg fs-lg btn-outline-secondary border-0 rounded-circle animate-shake d-none d-md-inline-flex" href="">
        <i class="ci-user animate-target"></i>
        <span class="visually-hidden">Account</span>
      </a>
      <!-- Wishlist button (desktop) -->
      <a class="btn btn-icon btn-lg fs-lg btn-outline-secondary border-0 rounded-circle animate-pulse d-none d-md-inline-flex" href="">
        <i class="ci-heart animate-target"></i>
        <span class="visually-hidden">Wishlist</span>
      </a>
      <!-- Cart button -->
      <cart-items></cart-items>
    </div>
  </div>

  <!-- Main navigation -->
  <div class="collapse navbar-stuck-hide" id="stuckNav">
    <nav class="offcanvas offcanvas-start" id="navbarNav" tabindex="-1" aria-labelledby="navbarNavLabel">
      <div class="offcanvas-header py-3">
        <h5 class="offcanvas-title" id="navbarNavLabel">{{ __('Навігація') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body pt-1 pb-3 py-lg-0">
        <div class="container pb-lg-2 px-0 px-lg-3">
          <div class="position-relative d-lg-flex align-items-center justify-content-between">
            <!-- Language switcher (якщо є) -->
            <div class="dropdown pb-lg-2">
              <language-switcher
                :product='@json($product ?? null)'
                :category='@json($category ?? null)'>
              </language-switcher>
            </div>

            <!-- Navbar nav (динаміка з бази) -->
            <ul class="navbar-nav position-relative me-xl-n5">
              @foreach($menuCategories as $category)
                <li class="nav-item dropdown pb-lg-2 me-lg-n1 me-xl-0">
                  @if($category['children'] && count($category['children']))
                    <a class="nav-link dropdown-toggle"  role="button" data-bs-toggle="dropdown" data-bs-trigger="hover" aria-expanded="false">
                      {{ $category['name'] }}
                    </a>
                    <div class="dropdown-menu p-4" style="--cz-dropdown-spacer: .75rem">
                      <div class="d-flex flex-column flex-lg-row gap-4">
                        <div style="min-width: 190px">
                          <div class="mb-2">{{ $category['name'] }}</div>
                          <ul class="nav flex-column gap-2 mt-0">
                            @foreach($category['children'] as $child)
                              <li class="d-flex w-100 pt-1">
                                <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0"
                                   href="{{ url($locale.'/'.$child['slug']) }}">
                                  {{ $child['name'] }}
                                </a>
                              </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                  @else
                    <a class="nav-link" href="{{ url($locale.'/'.$category['slug']) }}">
                      {{ $category['name'] }}
                    </a>
                  @endif
                </li>
              @endforeach

              {{-- Інформ. сторінки, якщо потрібно --}}
              @isset($pages)
                @foreach($pages as $page)
                  <li class="nav-item pb-lg-2 me-lg-n2 me-xl-0">
                    <a class="nav-link" href="{{ url($locale.'/page/'.$page['slug']) }}">
                      {{ $page['title'] }}
                    </a>
                  </li>
                @endforeach
              @endisset
            </ul>

            <!-- Search toggle -->
            <button type="button" class="btn btn-outline-secondary justify-content-start w-100 px-3 mb-lg-2 ms-3 d-none d-lg-inline-flex" style="max-width: 240px" data-bs-toggle="offcanvas" data-bs-target="#searchBox" aria-controls="searchBox">
              <i class="ci-search fs-base ms-n1 me-2"></i>
              <span class="text-body-tertiary fw-normal">{{ __('Пошук') }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Account & Wishlist (mobile) -->
      <div class="offcanvas-header border-top px-0 py-3 mt-3 d-md-none">
        <div class="nav nav-justified w-100">
          <a class="nav-link border-end" href="">
            <i class="ci-user fs-lg opacity-60 me-2"></i>
            {{ __('Акаунт') }}
          </a>
          <a class="nav-link" href="">
            <i class="ci-heart fs-lg opacity-60 me-2"></i>
            {{ __('Улюблене') }}
          </a>
        </div>
      </div>
    </nav>
  </div>
</header>
