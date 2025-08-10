@extends('admin.layouts.app')

@section('content')
<div class="filters mb-4 card p-4" style="border-radius:8px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">Спеціальні пропозиції</h2>
        <a href="{{ route('admin.special_offers.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> <span>Додати пропозицію</span>
        </a>
    </div>

    <div class="d-flex flex-wrap gap-3">
        @forelse($special_offers as $offer)
        <div class="banner-card special-offer-card d-flex align-items-center gap-4 p-3 mb-2 w-100" style="border-radius:10px; box-shadow:0 2px 8px #0001; background:#fff;">
          <div style="width: 200px; flex-shrink:0;">
                @if($offer->preview_path)
                    <img src="{{ asset($offer->preview_path) }}" alt="На ногах" class="offer-img" style="border-radius:4px;object-fit:cover;">
                @else
                    <div class="offer-img-placeholder">—</div>
                @endif
            </div>
            <div style="width: 200px; flex-shrink:0;">
                @if($offer->image_path)
                    <img src="{{ asset($offer->image_path) }}" alt="{{ $offer->title }}" class="offer-img" style="border-radius:4px;object-fit:cover;">
                @else
                    <div class="offer-img-placeholder">—</div>
                @endif
            </div>



            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                    <div class="offer-id text-muted small">ID: <b>{{ $offer->id }}</b></div>
                    <span class="badge px-2 {{ $offer->is_active ? 'status-publish' : 'status-inactive' }}">
                        {{ $offer->is_active ? 'Опубліковано' : 'Неактивний' }}
                    </span>
                    <span class="offer-price text-muted ms-3">
                        Ціна: {{ number_format($offer->price, 2) }} $
                    </span>
                    @if($offer->old_price)
                        <span class="text-decoration-line-through text-muted ms-2">
                            {{ number_format($offer->old_price, 2) }} $
                        </span>
                    @endif
                    @if($offer->discount)
                        <span class="badge bg-danger ms-2">{{ $offer->discount }}%</span>
                    @endif
                    @if($offer->expires_at)
                        <span class="badge bg-info text-dark ms-2">
                            До завершення: {{ $offer->remaining_time }}
                        </span>
                    @endif
                </div>

                <div class="offer-title mb-1 text-dark fw-semibold">
                    {{ $offer->title ?? '— Без назви —' }}
                </div>

                <div class="offer-subtitle text-muted small mb-1">
                    {{ $offer->subtitle ?? '—' }}
                </div>

                <div class="offer-button-link">
                    @if($offer->button_text)
                        <span class="btn btn-sm btn-light border px-3 me-2" style="pointer-events:none;">{{ $offer->button_text }}</span>
                    @endif
                    @if($offer->button_link)
                        <a href="{{ $offer->button_link }}" target="_blank" class="text-muted small" style="text-decoration:underline;">
                            {{ \Illuminate\Support\Str::limit($offer->button_link, 38) }}
                        </a>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </div>
            </div>

            <div class="offer-actions d-flex align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.special_offers.edit', $offer) }}" class="btn btn-light btn-sm border edit-btn" title="Редагувати">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('admin.special_offers.destroy', $offer) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-light btn-sm border dots-menu" title="Видалити"
                        onclick="return confirm('Видалити цю пропозицію?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>

        </div>
        @empty
        <div class="w-100 text-center text-muted py-4">Спеціальних пропозицій ще немає.</div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.banner-card:hover {
    box-shadow: 0 4px 30px #2221 !important;
}
.special-offer-card {
    transition: box-shadow .18s;
}
.special-offer-card:hover {
    box-shadow: 0 4px 32px #292b7717;
}
.offer-img {
    width: 200px !important;
    height: auto;
    max-height: 350px;
    border-radius: 8px;
    object-fit: cover;
    display: block;
}
.offer-img-placeholder {
    width: 100%;
   
    background: #f5f6f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 32px;
    font-weight: 700;
}
.offer-title {
    font-size: 16px;
    line-height: 1.2;
    font-weight: 600;
}
.offer-subtitle {
    font-size: 13px;
    line-height: 1.1;
}
.offer-id {
    letter-spacing: 1px;
}
.status-publish {
    background-color: #e9fdf0 !important;
    color: #22c55e !important;
}
.status-inactive {
    background-color: #ffecec !important;
    color: #ef4444 !important;
}
.offer-actions .btn {
    border-radius: 8px !important;
    padding: 5px 12px !important;
    font-size: 16px;
    transition: background 0.12s;
}
.edit-btn:hover, .dots-menu:hover {
    background: #f2f2f5 !important;
    color: #1f2937 !important;
}
@media (max-width: 768px) {
    .special-offer-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }
    .offer-actions {
        width: 100%;
        justify-content: flex-end;
    }
    .filters.mb-4.card.p-4 {
        padding: 1rem !important;
    }
}
</style>
@endpush
