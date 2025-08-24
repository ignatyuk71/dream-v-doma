@extends('admin.layouts.vuexy')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-8 col-md-10">
    <h1 class="mb-4 fw-bold">Редагувати спеціальну пропозицію</h1>

    <form action="{{ route('admin.special_offers.update', $specialOffer) }}" method="POST" enctype="multipart/form-data" class="card shadow-sm p-4">
      @csrf
      @method('PUT')

      <!-- Назва -->
      <div class="mb-3">
        <label for="title" class="form-label fw-semibold">Назва <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" 
               class="form-control @error('title') is-invalid @enderror" 
               value="{{ old('title', $specialOffer->title) }}" required>
        @error('title')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Сабтайтл -->
      <div class="mb-3">
        <label for="subtitle" class="form-label">Сабтайтл</label>
        <input type="text" id="subtitle" name="subtitle" 
               class="form-control @error('subtitle') is-invalid @enderror" 
               value="{{ old('subtitle', $specialOffer->subtitle) }}">
        @error('subtitle')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Основне зображення -->
      <div class="mb-4">
        <label for="image_path" class="form-label fw-semibold">Основне зображення (товар) <span class="text-danger">*</span></label>
        <input type="file" id="image_path" name="image_path" accept="image/*"
               class="form-control @error('image_path') is-invalid @enderror" onchange="previewImage(this, 'imagePreview')">
        @error('image_path')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="mt-3">
          <img id="imagePreview"
               src="{{ $specialOffer->image_path ? asset($specialOffer->image_path) : '#' }}"
               alt="Прев'ю"
               style="{{ $specialOffer->image_path ? '' : 'display:none;' }} max-width: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>
      </div>

      <!-- Зображення на ногах (preview) -->
      <div class="mb-4">
        <label for="preview_path" class="form-label fw-semibold">Зображення на ногах (preview)</label>
        <input type="file" id="preview_path" name="preview_path" accept="image/*"
               class="form-control @error('preview_path') is-invalid @enderror" onchange="previewImage(this, 'previewPreview')">
        @error('preview_path')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="mt-3">
          <img id="previewPreview"
               src="{{ $specialOffer->preview_path ? asset($specialOffer->preview_path) : '#' }}"
               alt="Прев'ю"
               style="{{ $specialOffer->preview_path ? '' : 'display:none;' }} max-width: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        </div>
      </div>

      <!-- Ціна -->
      <div class="mb-3">
        <label for="price" class="form-label fw-semibold">Ціна ($) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price"
               class="form-control @error('price') is-invalid @enderror"
               value="{{ old('price', $specialOffer->price) }}" required>
        @error('price')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Стара ціна -->
      <div class="mb-3">
        <label for="old_price" class="form-label">Стара ціна ($)</label>
        <input type="number" step="0.01" min="0" id="old_price" name="old_price"
               class="form-control @error('old_price') is-invalid @enderror"
               value="{{ old('old_price', $specialOffer->old_price) }}">
        @error('old_price')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Знижка (%) -->
      <div class="mb-3">
        <label for="discount" class="form-label">Знижка (%)</label>
        <input type="number" min="0" max="99" id="discount" name="discount"
               class="form-control @error('discount') is-invalid @enderror"
               value="{{ old('discount', $specialOffer->discount) }}">
        @error('discount')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Текст кнопки -->
      <div class="mb-3">
        <label for="button_text" class="form-label">Текст кнопки</label>
        <input type="text" id="button_text" name="button_text"
               class="form-control @error('button_text') is-invalid @enderror"
               value="{{ old('button_text', $specialOffer->button_text) }}">
        @error('button_text')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Лінк кнопки -->
      <div class="mb-3">
        <label for="button_link" class="form-label">Посилання кнопки</label>
        <input type="url" id="button_link" name="button_link"
               class="form-control @error('button_link') is-invalid @enderror"
               value="{{ old('button_link', $specialOffer->button_link) }}">
        @error('button_link')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Дата завершення -->
      <div class="mb-3">
        <label for="expires_at" class="form-label">Дата завершення</label>
        <input type="datetime-local" id="expires_at" name="expires_at"
               class="form-control @error('expires_at') is-invalid @enderror"
               value="{{ old('expires_at', $specialOffer->expires_at ? $specialOffer->expires_at->format('Y-m-d\TH:i') : '') }}">
        @error('expires_at')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Активність -->
      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $specialOffer->is_active) ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="is_active">Активна</label>
      </div>

      <!-- Кнопка збереження -->
      <button type="submit" class="btn btn-success px-5 py-2 fw-semibold">
        <i class="bi bi-check-circle me-2"></i> Зберегти
      </button>
    </form>
  </div>
</div>

<script>
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    }
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = '#';
    preview.style.display = 'none';
  }
}
</script>
@endsection
