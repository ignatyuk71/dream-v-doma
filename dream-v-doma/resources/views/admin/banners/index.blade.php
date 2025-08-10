@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="fw-bold mb-0">Банери</h1>
            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Додати банер
            </a>
        </div>

        @if($banners->count())
            <div class="banner-list">
                @foreach($banners as $banner)
                    <div class="banner-card shadow-sm mb-4 p-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                            <div class="banner-img">
                                @if($banner->image)
                                    <img src="{{ asset('storage/'.$banner->image) }}" alt="preview" style="width:64px;height:44px;object-fit:cover;border-radius:8px;">
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="mb-1">
                                    <span class="text-muted fw-semibold" style="font-size:14px;">ID: {{ $banner->id }}</span>
                                    <span class="badge ms-2 {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $banner->is_active ? 'Активний' : 'Неактивний' }}
                                    </span>
                                    <span class="text-muted ms-2" style="font-size:14px;">Сортування: {{ $banner->sort_order }}</span>
                                </div>
                                <div class="text-muted small" style="max-width:370px;" title="{{ $banner->subtitle }}">
                                    {{ \Illuminate\Support\Str::limit($banner->subtitle, 40) }}
                                </div>
                                <div class="fw-semibold banner-title mb-1" style="font-size:17px;max-width:350px;" title="{{ $banner->title }}">
                                    {{ $banner->title }}
                                </div>
                                
                                @if($banner->button_text || $banner->button_link)
                                    <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                        @if($banner->button_text)
                                            <span class="btn btn-sm btn-light border px-3" style="pointer-events:none;">{{ $banner->button_text }}</span>
                                        @endif
                                        @if($banner->button_link)
                                            <a href="{{ $banner->button_link }}" target="_blank" class="text-primary small" style="text-decoration:underline;">{{ \Illuminate\Support\Str::limit($banner->button_link, 38) }}</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-shrink-0 align-items-end gap-2 ms-3">
                            <a href="{{ route('admin.banners.edit', $banner) }}"
                               class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1"
                               title="Редагувати">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Видалити цей банер?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" title="Видалити">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light border text-muted text-center py-4">Банерів ще немає.</div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.banner-list {
    margin-top: 0;
}
.banner-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #f1f1f5;
    transition: box-shadow .15s;
    min-height: 80px;
    position: relative;
}
.banner-card:hover {
    box-shadow: 0 4px 18px #2221  !important;
}
.banner-title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.banner-img img {
    box-shadow: 0 2px 8px #0001;
}
@media (max-width: 768px) {
    .banner-card { flex-direction: column; align-items: flex-start !important; }
    .banner-title { max-width: 170px; font-size:15px;}
}
</style>
@endpush
