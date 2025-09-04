@extends('admin.layouts.vuexy')

@section('content')
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Акційні банери</h4>
    <a href="{{ route('admin.special_offers.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Додати банер
    </a>
  </div>

  @if($special_offers->isEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center text-muted py-5">
        Акційних банерів ще немає.
      </div>
    </div>
  @else
    <div class="d-flex flex-column gap-3">
      @foreach($special_offers as $offer)
        <div class="d-flex align-items-center gap-3 p-3 rounded border bg-white shadow-sm hover-shadow-sm transition">

          {{-- Дві картинки (в один рядок) --}}
          <div class="d-flex align-items-center gap-2" style="width:260px; flex-shrink:0;">
            <div class="border rounded overflow-hidden" style="width:150px; height:150px;">
              <img src="{{ $offer->preview_path 
                            ? asset('storage/'.$offer->preview_path) 
                            : asset('storage/'.$offer->image_path) }}"
                  alt="{{ $offer->title }}"
                  style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div class="border rounded overflow-hidden" style="width:100px; height:100px;">
              <img src="{{ asset('storage/'.$offer->image_path) }}"
                  alt="{{ $offer->title }}"
                  style="width:100%; height:100%; object-fit:cover;">
            </div>
          </div>


          {{-- Інфо --}}
          <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
              <span class="fw-semibold">ID: {{ $offer->id }}</span>
              @if($offer->is_active)
                <span class="badge bg-success">Опубліковано</span>
              @else
                <span class="badge bg-secondary">Чернетка</span>
              @endif

              <span class="fw-bold text-primary">{{ number_format($offer->price, 2) }} $</span>

              @if($offer->old_price)
                <span class="text-muted text-decoration-line-through">
                  {{ number_format($offer->old_price, 2) }} $
                </span>
              @endif

              @if($offer->discount)
                <span class="badge bg-danger">{{ $offer->discount }}%</span>
              @endif
            </div>

            @if($offer->expires_at)
              <div class="text-success small mb-1">
                <i class="bi bi-clock me-1"></i>
                До завершення: {{ $offer->expires_at->diffForHumans() }}
              </div>
            @endif

            <div class="text-muted small mb-1">
              {{ $offer->title }}
            </div>

            @if($offer->button_link)
              <div class="small">
                <a href="{{ $offer->button_link }}" target="_blank" class="text-decoration-underline">
                  {{ $offer->button_link }}
                </a>
              </div>
            @endif
          </div>

          {{-- Дії (олівець + три крапки) --}}
          <div class="ms-auto d-flex align-items-center gap-2">
            {{-- Edit --}}
            <a href="{{ route('admin.special_offers.edit', $offer) }}"
               class="btn btn-light btn-icon rounded-circle shadow-sm" title="Редагувати">
               <i class="bi bi-pencil-square"></i>
            </a>

            {{-- Dropdown --}}
            <div class="dropdown">
              <button class="btn btn-light btn-icon rounded-circle shadow-sm" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots-vertical"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-end shadow border-0 py-2">
                {{-- Download основного/превʼю зображення --}}
                <a href="{{ asset($offer->preview_path ?? $offer->image_path) }}" download
                   class="dropdown-item d-flex align-items-center gap-2">
                  <i class="bi bi-download fs-5"></i>
                  <span>Download</span>
                </a>

                {{-- Delete --}}
                <form action="{{ route('admin.special_offers.destroy', $offer) }}" method="POST"
                      onsubmit="return confirm('Видалити банер?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                    <i class="bi bi-trash fs-5"></i>
                    <span>Delete</span>
                  </button>
                </form>

                {{-- Duplicate (показуємо, якщо є маршрут) --}}
                @if(\Illuminate\Support\Facades\Route::has('admin.special_offers.duplicate'))
                  <form action="{{ route('admin.special_offers.duplicate', $offer) }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                      <i class="bi bi-files fs-5"></i>
                      <span>Duplicate</span>
                    </button>
                  </form>
                @endif
              </div>
            </div>
          </div>

        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection

@push('page-styles')
<style>
  .btn-icon{width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%!important}
  .btn-icon .bi{font-size:1.1rem;line-height:1}
  .dropdown-menu .dropdown-item{padding:.5rem .75rem}
  .dropdown-menu .bi{width:1.25rem;text-align:center}
  .transition{transition:box-shadow .15s ease, background-color .15s ease, color .15s ease}
  .hover-shadow-sm:hover{box-shadow:0 .5rem 1rem rgba(0,0,0,.06)!important}
</style>
@endpush
