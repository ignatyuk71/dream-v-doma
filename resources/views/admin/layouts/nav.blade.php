<!-- Navbar -->
<nav
  id="layout-navbar"
  class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme">

  <!-- burger (мобільне відкриття сайдбару) -->
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
      <i class="icon-base ti tabler-menu-2 icon-md"></i>
    </a>
  </div>

  <div id="navbar-collapse" class="navbar-nav-right d-flex align-items-center justify-content-end">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item navbar-search-wrapper px-md-0 px-2 mb-0">
        <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
          <span id="autocomplete" class="d-inline-block text-body-secondary fw-normal">Пошук…</span>
        </a>
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-md-auto">

      <!-- Language -->
      <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <i class="icon-base ti tabler-language icon-22px text-heading"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="javascript:void(0);" data-language="uk" data-text-direction="ltr"><span>Українська</span></a></li>
          <li><a class="dropdown-item" href="javascript:void(0);" data-language="en" data-text-direction="ltr"><span>English</span></a></li>
        </ul>
      </li>
      <!-- /Language -->

      <!-- Theme switcher -->
      <li class="nav-item dropdown">
        <a
          id="nav-theme"
          class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
          href="javascript:void(0);"
          data-bs-toggle="dropdown">
          <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
          <span id="nav-theme-text" class="d-none ms-2">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
          <li>
            <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light">
              <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark">
              <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Dark</span>
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system">
              <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3" data-icon="device-desktop-analytics"></i>System</span>
            </button>
          </li>
        </ul>
      </li>
      <!-- /Theme switcher -->

      <!-- Quick links -->
      <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
        <a
          class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside">
          <i class="icon-base ti tabler-layout-grid-add icon-22px text-heading"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0">
          <div class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="mb-0 me-auto">Швидкі дії</h6>
            </div>
          </div>
          <div class="dropdown-shortcuts-list scrollable-container">
            <div class="row row-bordered overflow-visible g-0">
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="icon-base ti tabler-calendar icon-26px text-heading"></i>
                </span>
                <a href="" class="stretched-link">Замовлення</a>
                <small>Список</small>
              </div>
              <div class="dropdown-shortcuts-item col">
                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                  <i class="icon-base ti tabler-file-dollar icon-26px text-heading"></i>
                </span>
                <a href="" class="stretched-link">Новий товар</a>
                <small>Додати</small>
              </div>
            </div>
          </div>
        </div>
      </li>
      <!-- /Quick links -->

      <!-- Notifications -->
      <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
        <a
          class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
          href="javascript:void(0);"
          data-bs-toggle="dropdown"
          data-bs-auto-close="outside">
          <span class="position-relative">
            <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
            <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
          </span>
        </a>

        <ul class="dropdown-menu dropdown-menu-end p-0">
          <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
              <h6 class="mb-0 me-auto">Сповіщення</h6>
              <div class="d-flex align-items-center h6 mb-0">
                <span class="badge bg-label-primary me-2">Нові</span>
              </div>
            </div>
          </li>

          <li class="dropdown-notifications-list scrollable-container">
            <ul class="list-group list-group-flush">
              <li class="list-group-item list-group-item-action dropdown-notifications-item">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar">
                      <img src="{{ asset('vendor/vuexy/assets/img/avatars/1.png') }}" class="rounded-circle" alt="">
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <h6 class="small mb-1">Нове замовлення 🛒</h6>
                    <small class="mb-1 d-block text-body">Створено нове замовлення</small>
                    <small class="text-body-secondary">щойно</small>
                  </div>
                </div>
              </li>
            </ul>
          </li>

          <li class="border-top">
            <div class="d-grid p-4">
              <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
                <small class="align-middle">Усі сповіщення</small>
              </a>
            </div>
          </li>
        </ul>
      </li>
      <!-- /Notifications -->

<!-- User -->
<li class="nav-item navbar-dropdown dropdown-user dropdown">
  <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
    <div class="avatar avatar-online">
      <img src="{{ asset('vendor/vuexy/assets/img/avatars/1.png') }}" alt="avatar" class="rounded-circle" />
    </div>
  </a>

  <ul class="dropdown-menu dropdown-menu-end">
    {{-- Header --}}
    <li>
      <a class="dropdown-item mt-0" href="#">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0 me-2">
            <div class="avatar avatar-online">
              <img src="{{ asset('vendor/vuexy/assets/img/avatars/1.png') }}" alt="avatar" class="rounded-circle" />
            </div>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ Auth::user()->name ?? 'Користувач' }}</h6>
            <small class="text-body-secondary">{{ Auth::user()->role_name ?? 'Адмін' }}</small>
          </div>
        </div>
      </a>
    </li>

    <li><div class="dropdown-divider my-1 mx-n2"></div></li>

    {{-- Items --}}
    <li>
      <a class="dropdown-item" href="#">
        <i class="icon-base ti tabler-user me-3 icon-md"></i>
        <span class="align-middle">Мій профіль</span>
      </a>
    </li>

    <li>
      <a class="dropdown-item" href="#">
        <i class="icon-base ti tabler-settings me-3 icon-md"></i>
        <span class="align-middle">Налаштування</span>
      </a>
    </li>

    <li>
      <a class="dropdown-item" href="#">
        <span class="d-flex align-items-center align-middle">
          <i class="flex-shrink-0 icon-base ti tabler-file-dollar me-3 icon-md"></i>
          <span class="flex-grow-1 align-middle">Білінг</span>
          <span class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center">
            4
          </span>
        </span>
      </a>
    </li>

    <li><div class="dropdown-divider my-1 mx-n2"></div></li>

    <li>
      <a class="dropdown-item" href="#">
        <i class="icon-base ti tabler-currency-dollar me-3 icon-md"></i>
        <span class="align-middle">Тарифи</span>
      </a>
    </li>

    <li>
      <a class="dropdown-item" href="#">
        <i class="icon-base ti tabler-question-mark me-3 icon-md"></i>
        <span class="align-middle">Довідка</span>
      </a>
    </li>

    {{-- Logout --}}
    <li>
      <div class="d-grid px-2 pt-2 pb-1">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-sm btn-danger d-flex">
            <small class="align-middle">Вийти</small>
            <i class="icon-base ti tabler-logout ms-2 icon-14px"></i>
          </button>
        </form>
      </div>
    </li>
  </ul>
</li>
<!-- /User -->


    </ul>
  </div>
</nav>
<!-- /Navbar -->
