@extends('admin.layouts.vuexy')

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-10">
        <h1 class="mb-4 fw-bold">Редагувати банер</h1>
        <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-semibold">Зображення</label>
                @if($banner->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$banner->image) }}" alt="preview" style="max-width: 360px; border-radius:8px;box-shadow:0 2px 8px #0001;">
                    </div>
                @endif
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" onchange="showPreview(event)">
                <div class="form-text">Завантаж новий файл, щоб змінити (jpg, png).</div>
                <div class="mt-3">
                    <img id="preview-image" src="#" alt="Прев’ю" style="display:none;max-width:60%;height:auto;border-radius:8px;box-shadow:0 2px 8px #0001;">
                </div>
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Заголовок</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}">
                @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Сабтайтл</label>
                <input type="text" name="subtitle" class="form-control @error('subtitle') is-invalid @enderror" value="{{ old('subtitle', $banner->subtitle) }}">
                @error('subtitle')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Текст кнопки</label>
                <input type="text" name="button_text" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $banner->button_text) }}">
                @error('button_text')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Лінк кнопки</label>
                <input type="text" name="button_link" class="form-control @error('button_link') is-invalid @enderror" value="{{ old('button_link', $banner->button_link) }}">
                @error('button_link')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Активний</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Показувати банер</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Сортування</label>
                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                        value="{{ old('sort_order', $banner->sort_order) }}" min="0" max="99">
                    @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-success px-5 py-2 fw-semibold">
                <i class="bi bi-check-circle me-2"></i>Зберегти зміни
            </button>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-link ms-3">Назад до списку</a>
        </form>
    </div>
</div>

<script>
function showPreview(event){
    let input = event.target;
    let reader = new FileReader();
    reader.onload = function(){
        let output = document.getElementById('preview-image');
        output.src = reader.result;
        output.style.display = 'block';
    };
    if(input.files[0]){
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
