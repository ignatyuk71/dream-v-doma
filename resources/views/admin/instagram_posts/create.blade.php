@extends('admin.layouts.vuexy')

@section('content')
<div class="row">
    <div class="col-lg-8 col-md-10">
        <h1 class="mb-4 fw-bold">Додати Instagram-пост</h1>
        <form action="{{ route('admin.instagram-posts.store') }}" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
            @csrf

            <div class="mb-4">
                <label class="form-label fw-semibold">Зображення <span class="text-danger">*</span></label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" required onchange="showPreview(event)">
                <div class="form-text">Завантаж файл (jpg, png, webp, 1400×700px).</div>
                <div class="mt-3">
                    <img id="preview-image" src="#" alt="Прев’ю" style="display:none;max-width:60%;height:auto;border-radius:8px;box-shadow:0 2px 8px #0001;">
                </div>
                @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Alt (опис)</label>
                <input type="text" name="alt" class="form-control @error('alt') is-invalid @enderror" value="{{ old('alt') }}" placeholder="Опис зображення">
                @error('alt')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Посилання</label>
                <input type="text" name="link" class="form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" placeholder="https://...">
                @error('link')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Активний</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active" {{ old('active', 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active">Показувати пост</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Позиція</label>
                    <input type="number" name="position" class="form-control @error('position') is-invalid @enderror"
                        value="{{ old('position', 0) }}" min="0" max="99">
                    @error('position')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-success px-5 py-2 fw-semibold">
                <i class="bi bi-check-circle me-2"></i>Зберегти пост
            </button>
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
