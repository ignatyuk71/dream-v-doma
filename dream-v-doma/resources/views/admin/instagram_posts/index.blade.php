@extends('admin.layouts.vuexy')

@section('content')
<div class="filters mb-4 card p-4" style="border-radius:8px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">Instagram-пости</h2>
        <a href="{{ route('admin.instagram-posts.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-plus-lg"></i> <span>Додати пост</span>
        </a>
    </div>

    <div class="d-flex flex-wrap gap-3">
        @forelse($posts as $post)
        <div class="banner-card ig-post-card d-flex align-items-center gap-4 p-3 mb-2 w-100" style="border-radius:10px; box-shadow:0 2px 8px #0001; background:#fff;">
            <div style="width: 100px; flex-shrink:0;">
                @if($post->image)
                    <img src="{{ asset($post->image) }}" alt="{{ $post->alt }}" class="ig-img" style="max-width:100px;max-height:70px;border-radius:8px;object-fit:cover;">
                @else
                    <div class="ig-img-placeholder">—</div>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="ig-id text-muted small">ID: <b>{{ $post->id }}</b></div>
                    <span class="badge px-2 {{ $post->active ? 'status-publish' : 'status-inactive' }}">
                        {{ $post->active ? 'Опубліковано' : 'Неактивний' }}
                    </span>
                    <span class="ig-pos text-muted">Позиція: {{ $post->position }}</span>
                </div>
                <div class="ig-alt mb-1 text-dark fw-semibold">
                    {{ $post->alt ?? '— Без підпису —' }}
                </div>
                <div class="ig-link">
                    @if($post->link)
                        <a href="{{ $post->link }}" target="_blank" class="text-muted small">
                            {{ \Illuminate\Support\Str::limit($post->link, 38) }}
                        </a>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </div>
            </div>
            <div class="ig-actions d-flex align-items-center gap-2 ms-auto">
                <a href="#" class="btn btn-light btn-sm border edit-btn" title="Редагувати">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('admin.instagram-posts.destroy', $post) }}" method="POST" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-light btn-sm border dots-menu" title="Видалити"
                        onclick="return confirm('Видалити цей пост?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="w-100 text-center text-muted py-4">Instagram-постів ще немає.</div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.banner-card:hover {
    box-shadow: 0 4px 30px #2221  !important;
}
.ig-post-card {
    transition: box-shadow .18s;
}
.ig-post-card:hover {
    box-shadow: 0 4px 32px #292b7717;
}
.ig-img {
    border-radius: 8px;
    object-fit: cover;
    background: #f6f6fa;
    width: 100px;
    height: 70px;
    display: block;
}
.ig-img-placeholder {
    width: 100px;
    height: 70px;
    background: #f5f6f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 32px;
    font-weight: 700;
}
.ig-alt {
    font-size: 16px;
    line-height: 1.2;
    font-weight: 600;
}
.ig-link {
    font-size: 13px;
    line-height: 1.1;
}
.ig-id {
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
.ig-actions .btn {
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
    .ig-post-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }
    .ig-actions {
        width: 100%;
        justify-content: flex-end;
    }
    .filters.mb-4.card.p-4 {
        padding: 1rem !important;
    }
}
</style>
@endpush
