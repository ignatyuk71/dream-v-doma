@extends('admin.layouts.vuexy')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold">Банери</h4>
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Додати банер
    </a>
  </div>

  @if($banners->count())
    <div class="d-flex flex-column gap-3">
      @foreach($banners as $banner)
        <div class="card shadow-sm border-0 p-3">
          <div class="d-flex align-items-center gap-3">
            {{-- Preview --}}
            <div class="flex-shrink-0">
              @if($banner->image)
                <img src="{{ asset('storage/'.$banner->image) }}"
                     alt="preview"
                     class="rounded-2 shadow-sm"
                     style="width:64px;height:44px;object-fit:cover;">
              @else
                <div class="bg-body-tertiary rounded-2 d-flex align-items-center justify-content-center"
                     style="width:64px;height:44px;">
                  <span class="text-muted small">—</span>
                </div>
              @endif
            </div>

            {{-- Main --}}
            <div class="d-flex flex-column flex-grow-1 min-w-0">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <span class="text-muted fw-semibold">ID: {{ $banner->id }}</span>

                <span class="badge {{ $banner->is_active ? 'bg-label-success text-success' : 'bg-label-secondary text-body' }}">
                  {{ $banner->is_active ? 'Активний' : 'Неактивний' }}
                </span>

                <span class="text-muted">Сортування: {{ $banner->sort_order }}</span>
              </div>

              <div class="text-truncate text-muted small" title="{{ $banner->subtitle }}">
                {{ \Illuminate\Support\Str::limit($banner->subtitle, 80) }}
              </div>

              <div class="fw-semibold text-truncate" style="max-width: 560px" title="{{ $banner->title }}">
                {{ $banner->title }}
              </div>

              @if($banner->button_text || $banner->button_link)
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                  @if($banner->button_text)
                    <span class="badge rounded-pill bg-label-secondary">
                      {{ $banner->button_text }}
                    </span>
                  @endif
                  @if($banner->button_link)
                    <a href="{{ $banner->button_link }}" target="_blank"
                       class="link-primary small text-truncate"
                       style="max-width: 420px;"
                       title="{{ $banner->button_link }}">
                      {{ \Illuminate\Support\Str::limit($banner->button_link, 50) }}
                    </a>
                  @endif
                </div>
              @endif
            </div>

            {{-- Actions --}}
            <div class="d-flex flex-column flex-shrink-0 align-items-end gap-2 ms-2">
              <a href="{{ route('admin.banners.edit', $banner) }}"
                 class="btn btn-sm btn-icon btn-outline-secondary"
                 title="Редагувати">
                <i class="bi bi-pencil-square"></i>
              </a>
              <form action="{{ route('admin.banners.destroy', $banner) }}"
                    method="POST"
                    onsubmit="return confirm('Видалити цей банер?')"
                    class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="btn btn-sm btn-icon btn-outline-danger"
                        title="Видалити">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="card border-0 shadow-none">
      <div class="card-body text-center text-muted py-5">
        Банерів ще немає.
      </div>
    </div>
  @endif
</div>
@endsection

@push('styles')
<style>
.card {
  border-radius: 0.75rem;
}
.card:hover {
  box-shadow: 0 4px 18px rgba(34, 34, 34, 0.08);
}
</style>
@endpush
