<aside class="sidebar p-3">
  <h5 class="fw-bold mb-4">DREAM V DOMA</h5>
  <ul class="nav flex-column">

    <!-- Головна -->
    <li class="nav-item mb-1">
      <a href="{{ route('admin.dashboard') }}"
         class="nav-link sidebar-link @if(request()->routeIs('admin.dashboard')) active @endif">
        <i class="bi bi-bar-chart-line me-2"></i> Головна
      </a>
    </li>

    <!-- Товари -->
    @php 
      $productOpen = request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*'); 
    @endphp
    <li class="nav-item mb-1">
      <button class="nav-link sidebar-link d-flex justify-content-between align-items-center w-100 text-start {{ $productOpen ? '' : 'collapsed' }}"
              data-bs-toggle="collapse" data-bs-target="#productMenu"
              aria-expanded="{{ $productOpen ? 'true' : 'false' }}">
        <span><i class="bi bi-box me-2"></i> Товари</span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="collapse list-unstyled ps-4 {{ $productOpen ? 'show' : '' }}" id="productMenu">
        <li>
          <a href="{{ route('admin.products.index') }}"
             class="nav-link sidebar-sub @if(request()->routeIs('admin.products.index')) active @endif">Список</a>
        </li>
        <li>
          <a href="{{ route('admin.products.create') }}"
             class="nav-link sidebar-sub @if(request()->routeIs('admin.products.create')) active @endif">Додати</a>
        </li>
        <li>
          <a href="{{ route('admin.categories.index') }}"
             class="nav-link sidebar-sub @if(request()->routeIs('admin.categories.*')) active @endif">Категорії</a>
        </li>
      </ul>
    </li>

    <!-- Замовлення -->
    @php
      $orderOpen = request()->routeIs('admin.orders.*');
    @endphp
    <li class="nav-item mb-1">
      <button class="nav-link sidebar-link d-flex justify-content-between align-items-center w-100 text-start {{ $orderOpen ? '' : 'collapsed' }}"
              data-bs-toggle="collapse" data-bs-target="#orderMenu"
              aria-expanded="{{ $orderOpen ? 'true' : 'false' }}">
        <span><i class="bi bi-cart me-2"></i> Замовлення</span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="collapse list-unstyled ps-4 {{ $orderOpen ? 'show' : '' }}" id="orderMenu">
        <li><a href="#" class="nav-link sidebar-sub">Список</a></li>
        <li><a href="#" class="nav-link sidebar-sub">Деталі</a></li>
      </ul>
    </li>

    <!-- Клієнти -->
    @php
      $customerOpen = request()->routeIs('admin.customers.*');
    @endphp
    <li class="nav-item mb-1">
      <button class="nav-link sidebar-link d-flex justify-content-between align-items-center w-100 text-start {{ $customerOpen ? '' : 'collapsed' }}"
              data-bs-toggle="collapse" data-bs-target="#customerMenu"
              aria-expanded="{{ $customerOpen ? 'true' : 'false' }}">
        <span><i class="bi bi-people me-2"></i> Клієнти</span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="collapse list-unstyled ps-4 {{ $customerOpen ? 'show' : '' }}" id="customerMenu">
        <li><a href="#" class="nav-link sidebar-sub">Список</a></li>
        <li><a href="#" class="nav-link sidebar-sub">Профіль</a></li>
      </ul>
    </li>

    <!-- Банери та Inst-пости -->
    @php 
      $bannerOpen = request()->routeIs('admin.banners.*') || request()->routeIs('admin.instagram-posts.*') || request()->routeIs('admin.special_offers.*');
    @endphp
    <li class="nav-item mb-1">
      <button class="nav-link sidebar-link d-flex justify-content-between align-items-center w-100 text-start {{ $bannerOpen ? '' : 'collapsed' }}"
              data-bs-toggle="collapse" data-bs-target="#bannerMenu"
              aria-expanded="{{ $bannerOpen ? 'true' : 'false' }}">
        <span><i class="bi bi-image me-2"></i> Банери</span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="collapse list-unstyled ps-4 {{ $bannerOpen ? 'show' : '' }}" id="bannerMenu">
        <li>
          <a href="{{ route('admin.instagram-posts.index') }}" class="nav-link sidebar-sub @if(request()->routeIs('admin.instagram-posts.*')) active @endif">
            <i class="bi bi-instagram me-2"></i> Inst-пости
          </a>
        </li>
        <li>
          <a href="{{ route('admin.banners.index') }}" class="nav-link sidebar-sub @if(request()->routeIs('admin.banners.*')) active @endif">
            <i class="bi bi-image me-2"></i> Банер головна
          </a>
        </li>
        <li>
          <a href="{{ route('admin.special_offers.index') }}" class="nav-link sidebar-sub @if(request()->routeIs('admin.special_offers.*')) active @endif">
            <i class="bi bi-tags me-2"></i> Акційні банери
          </a>
        </li>
      </ul>
    </li>

    <!-- Інше -->
    <li class="nav-item mb-1"><a href="#" class="nav-link sidebar-link"><i class="bi bi-chat-left-text me-2"></i> Відгуки</a></li>
    <li class="nav-item mb-1"><a href="#" class="nav-link sidebar-link"><i class="bi bi-gear me-2"></i> Налаштування</a></li>
    <li class="nav-item mb-1"><a href="#" class="nav-link sidebar-link"><i class="bi bi-envelope me-2"></i> Пошта</a></li>
    <li class="nav-item mb-1"><a href="#" class="nav-link sidebar-link"><i class="bi bi-chat-dots me-2"></i> Чат</a></li>
    <li class="nav-item mb-1"><a href="#" class="nav-link sidebar-link"><i class="bi bi-person me-2"></i> Користувачі</a></li>
  </ul>
</aside>
