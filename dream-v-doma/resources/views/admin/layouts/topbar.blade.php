{{-- resources/views/admin/layouts/topbar.blade.php --}}
<nav id="layout-navbar"
     class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme px-3"
     style="min-height: 64px">

  {{-- Бургер для відкриття/закриття лівого меню (працює на мобайл і десктоп) --}}
  <div class="layout-menu-toggle navbar-nav align-items-center me-3">
    <a class="nav-item nav-link px-0" href="javascript:void(0)">
      <i class="ti ti-menu-2 ti-md"></i>
    </a>
  </div>

  {{-- Пошук --}}
  <div class="navbar-nav align-items-center flex-grow-1">
    <div class="nav-item d-flex align-items-center w-auto">
      <i class="ti ti-search me-2 text-muted"></i>
      <input type="text"
             class="form-control border-0 shadow-none bg-body"
             style="max-width: 240px"
             placeholder="Пошук…">
    </div>
  </div>

  {{-- Правий блок: нотифікації + профіль --}}
  <ul class="navbar-nav flex-row align-items-center ms-auto">

    {{-- Нотифікації --}}
    <li class="nav-item me-3">
      <a class="nav-link position-relative" href="javascript:void(0)">
        <i class="ti ti-bell ti-md"></i>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
      </a>
    </li>

    @if(Auth::check())
      {{-- Профіль користувача --}}
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center"
           href="javascript:void(0);" data-bs-toggle="dropdown">
          <img src="https://randomuser.me/api/portraits/men/75.jpg"
               alt="avatar" class="rounded-circle me-2" width="34" height="34">
          <span class="d-none d-sm-inline fw-semibold">{{ Auth::user()->name }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <h6 class="dropdown-header mb-0">{{ Auth::user()->name }}</h6>
            <small class="dropdown-item-text text-muted">{{ Auth::user()->email }}</small>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item">
                <i class="ti ti-logout me-2"></i>Вийти
              </button>
            </form>
          </li>
        </ul>
      </li>
    @endif
  </ul>
</nav>
