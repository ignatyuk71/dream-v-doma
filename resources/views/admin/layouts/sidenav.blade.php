<!-- Menu -->
@php
  $isDashboard  = request()->routeIs('admin.dashboard');

  $isProducts   = request()->routeIs('admin.products.*');
  $isCategories = request()->routeIs('admin.categories.*');
  $isProductsGrp= $isProducts || $isCategories;

  $isOrders     = request()->routeIs('admin.orders.*');
  $isCustomers  = request()->routeIs('admin.customers.*');

  $isBannersInsta = request()->routeIs('admin.instagram-posts.*');
  $isBannersMain  = request()->routeIs('admin.banners.*');
  $isSpecial      = request()->routeIs('admin.special_offers.*');
  $isBannersGrp   = $isBannersInsta || $isBannersMain || $isSpecial;

  $isReviews   = request()->routeIs('admin.reviews.*');
  $isSettings  = request()->routeIs('admin.settings.*');
  $isMail      = request()->routeIs('admin.mail.*');
  $isChat      = request()->routeIs('admin.chat.*');
  $isUsers     = request()->routeIs('admin.users.*');
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu">
  <div class="app-brand demo">
    <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        <span class="text-primary">
          <!-- SVG як у демо -->
          <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor"/>
            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z" fill="#161616"/>
            <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd" d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z" fill="#161616"/>
            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor"/>
          </svg>
        </span>
      </span>
      <span class="app-brand-text demo menu-text fw-bold ms-2">DREAM V DOMA</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
      <i class="icon-base ti tabler-x d-block d-xl-none"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    <!-- Головна -->
    <li class="menu-item {{ $isDashboard ? 'active' : '' }}">
      <a href="{{ route('admin.dashboard') }}" class="menu-link">
        <i class="menu-icon icon-base ti tabler-smart-home"></i>
        <div data-i18n="Dashboard">Головна</div>
      </a>
    </li>

    <!-- Товари -->
    <li class="menu-item {{ $isProductsGrp ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base ti tabler-shopping-cart"></i>
        <div data-i18n="Products">Товари</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
          <a href="{{ route('admin.products.index') }}" class="menu-link">
            <div data-i18n="Product List">Список</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
          <a href="{{ route('admin.products.create') }}" class="menu-link">
            <div data-i18n="Add Product">Додати</div>
          </a>
        </li>
        <li class="menu-item {{ $isCategories ? 'active' : '' }}">
          <a href="{{ route('admin.categories.index') }}" class="menu-link">
            <div data-i18n="Category List">Категорії</div>
          </a>
        </li>
      </ul>
    </li>

<!-- Замовлення -->
<li class="menu-item {{ $isOrders ? 'active open' : '' }}">
  <a href="javascript:void(0);" class="menu-link menu-toggle">
    <i class="menu-icon icon-base ti tabler-file-dollar"></i>
    <div data-i18n="Orders">Замовлення</div>
  </a>

  @php
    $routeOrder    = request()->route('order'); // може бути ID або модель
    $currentOrderId = is_object($routeOrder) ? ($routeOrder->id ?? null) : (is_numeric($routeOrder) ? $routeOrder : null);
  @endphp

  <ul class="menu-sub">
    <li class="menu-item {{ request()->routeIs('admin.orders.index') ? 'active' : '' }}">
      <a href="{{ route('admin.orders.index') }}" class="menu-link">
        <div data-i18n="Order List">Список</div>
      </a>
    </li>
    <li class="menu-item {{ request()->routeIs('admin.orders.show') ? 'active' : '' }}">
      <a href="{{ $currentOrderId ? route('admin.orders.show', $currentOrderId) : route('admin.orders.index') }}" class="menu-link">
        <div data-i18n="Order Details">Деталі</div>
      </a>
    </li>
  </ul>
</li>



    <!-- Банери -->
    <li class="menu-item {{ $isBannersGrp ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
      <i class="bi bi-image me-2"></i> 
        <div data-i18n="Banners">Банери</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ $isBannersInsta ? 'active' : '' }}">
          <a href="{{ route('admin.instagram-posts.index') }}" class="menu-link">
            <div data-i18n="Instagram Posts">Inst-пости</div>
          </a>
        </li>
        <li class="menu-item {{ $isBannersMain ? 'active' : '' }}">
          <a href="{{ route('admin.banners.index') }}" class="menu-link">
            <div data-i18n="Home Banner">Банер головна</div>
          </a>
        </li>
        <li class="menu-item {{ $isSpecial ? 'active' : '' }}">
          <a href="{{ route('admin.special_offers.index') }}" class="menu-link">
            <div data-i18n="Special Offers">Акційні банери</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Окремі пункти -->
    <li class="menu-item {{ $isReviews ? 'active' : '' }}">
      <a href="" class="menu-link">
        <i class="menu-icon icon-base ti tabler-messages"></i>
        <div data-i18n="Reviews">Відгуки</div>
      </a>
    </li>

    <li class="menu-item {{ $isSettings ? 'active' : '' }}">
      <a href="" class="menu-link">
        <i class="menu-icon icon-base ti tabler-settings"></i>
        <div data-i18n="Settings">Налаштування</div>
      </a>
    </li>


        <!-- Клієнти -->
    <li class="menu-item {{ $isCustomers ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon icon-base ti tabler-users"></i>
        <div data-i18n="Customers">Клієнти</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
          <a href="" class="menu-link">
            <div data-i18n="All Customers">Список</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.customers.show') ? 'active' : '' }}">
          <a href="" class="menu-link">
            <div data-i18n="Profile">Профіль</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="menu-item {{ $isMail ? 'active' : '' }}">
      <a href="" class="menu-link">
        <i class="menu-icon icon-base ti tabler-mail"></i>
        <div data-i18n="Mail">Пошта</div>
      </a>
    </li>



    <li class="menu-item {{ $isUsers ? 'active' : '' }}">
      <a href="" class="menu-link">
        <i class="menu-icon icon-base ti tabler-user"></i>
        <div data-i18n="Users">Користувачі</div>
      </a>
    </li>

  </ul>
</aside>

<div class="menu-mobile-toggler d-xl-none rounded-1">
  <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
    <i class="ti tabler-menu icon-base"></i>
    <i class="ti tabler-chevron-right icon-base"></i>
  </a>
</div>
<!-- / Menu -->
