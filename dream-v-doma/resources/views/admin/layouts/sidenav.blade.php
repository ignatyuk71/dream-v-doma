<nav id="sidebarMenu" class="sidebar d-lg-block bg-gray-800 text-white collapse" data-simplebar>
  <div class="sidebar-inner px-2 pt-3">
    <ul class="nav flex-column pt-3 pt-md-0">
      <li class="nav-item">
        <a href="/dashboard" class="nav-link d-flex align-items-center">
          <span class="sidebar-icon me-3">
            <img src="/assets/img/brand/light.svg" height="20" width="20" alt="Volt Logo">
          </span>
          <span class="mt-1 ms-1 sidebar-text">
            Volt Laravel
          </span>
        </a>
      </li>
      <li class="nav-item {{ Request::segment(1) == 'dashboard' ? 'active' : '' }}">
        <a href="/dashboard" class="nav-link">
          <span class="sidebar-icon"> <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
            </svg></span></span>
          <span class="sidebar-text">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
          data-bs-target="#submenu-laravel" aria-expanded="true">
          <span>
            <span class="sidebar-icon"><i class="fab fa-laravel me-2" style="color: #fb503b;"></i></span>
            <span class="sidebar-text" style="color: #fb503b;">🛍️ Товари та послуги</span>
          </span>
          <span class="link-arrow"><svg class="icon icon-sm" fill="currentColor" viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"></path>
            </svg></span>
        </span>
        <div class="multi-level collapse show" role="list" id="submenu-laravel" aria-expanded="false">
          <ul class="flex-column nav">
            <li class="nav-item {{ Request::segment(1) == 'profile' ? 'active' : '' }}">
              <a href="{{ route('admin.products.index') }}" class="nav-link">
                <span class="sidebar-text">Позиції </span>
              </a>
            </li>
            <li class="nav-item {{ Request::segment(1) == 'users' ? 'active' : '' }}">
              <a href="{{ route('admin.categories.index') }}" class="nav-link">
                <span class="sidebar-text">Категорії	</span>
              </a>
            </li>
            
            <li class="nav-item {{ Request::segment(1) == 'users' ? 'active' : '' }}">
              <a href="{{ route('admin.categories.index') }}" class="nav-link">
                <span class="sidebar-text">Акції та промокоди		</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item {{ Request::segment(1) == 'transactions' ? 'active' : '' }}">
        <a href="/transactions" class="nav-link">
          <span class="sidebar-icon"><svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
              <path fill-rule="evenodd"
                d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                clip-rule="evenodd"></path>
            </svg></span>
          <span class="sidebar-text">Замовлення</span>
        </a>
      </li>
      <li class="nav-item">
        <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
          data-bs-target="#submenu-pages">
          <span>
            <span class="sidebar-icon"><svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                  d="M2 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 002 2H4a2 2 0 01-2-2V5zm3 1h6v4H5V6zm6 6H5v2h6v-2z"
                  clip-rule="evenodd"></path>
                <path d="M15 7h1a2 2 0 012 2v5.5a1.5 1.5 0 01-3 0V7z"></path>
              </svg></span>
            <span class="sidebar-text">Page examples</span>
          </span>
          <span class="link-arrow"><svg class="icon icon-sm" fill="currentColor" viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"></path>
            </svg></span>
        </span>
        <div class="multi-level collapse" role="list" id="submenu-pages" aria-expanded="false">
          <ul class="flex-column nav">
            <li class="nav-item">
              <a class="nav-link" href="1">
                <span class="sidebar-text">Відгуки</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="1">
                <span class="sidebar-text">Клієнти	</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="1">
                <span class="sidebar-text">1111</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="1">
                <span class="sidebar-text">11111</span>
              </a>
            </li>
            
          </ul>
        </div>
      </li>

      
    </ul>
  </div>
</nav>