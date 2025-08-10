<div class="d-flex justify-content-between align-items-center mb-4 px-3 py-2 bg-white rounded shadow-sm">
  <div class="d-flex align-items-center">
    <i class="bi bi-search text-muted me-2"></i>
    <input type="text" class="form-control border-0 shadow-none" placeholder="Пошук..." style="max-width: 200px;">
  </div>

  <div class="d-flex align-items-center gap-3">
    <div class="position-relative">
      <i class="bi bi-bell fs-5 text-muted"></i>
      <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
    </div>

    @if(Auth::check())
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="avatar" class="rounded-circle me-2" width="32" height="32">
        <span class="fw-semibold">{{ Auth::user()->name }}</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end shadow">
        <li><h6 class="dropdown-header">{{ Auth::user()->email }}</h6></li>
        <li>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item">Вийти</button>
          </form>
        </li>
      </ul>
    </div>
    @endif
  </div>
</div>
