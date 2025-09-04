@extends('admin.layouts.vuexy')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-8 col-md-10">
    <h1 class="mb-4 fw-bold">Редагувати спеціальну пропозицію</h1>

    <form id="special-offer-form"
          action="{{ route('admin.special_offers.update', $specialOffer) }}"
          method="POST"
          enctype="multipart/form-data"
          class="card shadow-sm p-4">
      @csrf
      @method('PUT')

      <!-- Назва -->
      <div class="mb-3">
        <label for="title" class="form-label fw-semibold">Назва <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title"
               class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $specialOffer->title) }}" required>
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Сабтайтл -->
      <div class="mb-3">
        <label for="subtitle" class="form-label">Сабтайтл</label>
        <input type="text" id="subtitle" name="subtitle"
               class="form-control @error('subtitle') is-invalid @enderror"
               value="{{ old('subtitle', $specialOffer->subtitle) }}">
        @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Основне зображення -->
      <div class="mb-4">
        <label for="image_path" class="form-label fw-semibold">Основне зображення (товар) <span class="text-danger">*</span></label>
        <input type="file" id="image_path" name="image_path" accept="image/*"
               class="form-control @error('image_path') is-invalid @enderror"
               onchange="window.previewImage(this, 'imagePreview')">
        @error('image_path') <div class="invalid-feedback">{{ $message }}</div> @enderror

        <div class="mt-3">
          <div id="imagePreviewSpinner" class="d-flex align-items-center gap-2 d-none">
            <div class="spinner-border text-purple" role="status" style="width:1.25rem;height:1.25rem;"></div>
            <small class="text-muted">Завантаження попереднього перегляду…</small>
          </div>

          @php $imgStyle = 'max-width:220px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);'; @endphp
          <img id="imagePreview"
               src="{{ $specialOffer->image_path ? asset('storage/'.$specialOffer->image_path) : '' }}"
               alt="Прев'ю"
               style="{{ $imgStyle }} @if(!$specialOffer->image_path) display:none; @endif">

          <div id="imagePreviewName" class="small text-muted mt-2 {{ $specialOffer->image_path ? '' : 'd-none' }}">
            @if($specialOffer->image_path) {{ basename($specialOffer->image_path) }} @endif
          </div>
        </div>
      </div>

      <!-- Зображення на ногах -->
      <div class="mb-4">
        <label for="preview_path" class="form-label fw-semibold">Зображення на ногах (preview)</label>
        <input type="file" id="preview_path" name="preview_path" accept="image/*"
               class="form-control @error('preview_path') is-invalid @enderror"
               onchange="window.previewImage(this, 'previewPreview')">
        @error('preview_path') <div class="invalid-feedback">{{ $message }}</div> @enderror

        <div class="mt-3">
          <div id="previewPreviewSpinner" class="d-flex align-items-center gap-2 d-none">
            <div class="spinner-border text-purple" role="status" style="width:1.25rem;height:1.25rem;"></div>
            <small class="text-muted">Завантаження попереднього перегляду…</small>
          </div>

          <img id="previewPreview"
               src="{{ $specialOffer->preview_path ? asset('storage/'.$specialOffer->preview_path) : '' }}"
               alt="Прев'ю"
               style="{{ $imgStyle }} @if(!$specialOffer->preview_path) display:none; @endif">

          <div id="previewPreviewName" class="small text-muted mt-2 {{ $specialOffer->preview_path ? '' : 'd-none' }}">
            @if($specialOffer->preview_path) {{ basename($specialOffer->preview_path) }} @endif
          </div>
        </div>
      </div>

      <!-- Ціна -->
      <div class="mb-3">
        <label for="price" class="form-label fw-semibold">Ціна ($) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" id="price" name="price"
               class="form-control @error('price') is-invalid @enderror"
               value="{{ old('price', $specialOffer->price) }}" required>
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Стара ціна -->
      <div class="mb-3">
        <label for="old_price" class="form-label">Стара ціна ($)</label>
        <input type="number" step="0.01" min="0" id="old_price" name="old_price"
               class="form-control @error('old_price') is-invalid @enderror"
               value="{{ old('old_price', $specialOffer->old_price) }}">
        @error('old_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Знижка (%) -->
      <div class="mb-3">
        <label for="discount" class="form-label">Знижка (%)</label>
        <input type="number" min="0" max="99" id="discount" name="discount"
               class="form-control @error('discount') is-invalid @enderror"
               value="{{ old('discount', $specialOffer->discount) }}">
        @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Текст кнопки -->
      <div class="mb-3">
        <label for="button_text" class="form-label">Текст кнопки</label>
        <input type="text" id="button_text" name="button_text"
               class="form-control @error('button_text') is-invalid @enderror"
               value="{{ old('button_text', $specialOffer->button_text) }}">
        @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Лінк кнопки -->
      <div class="mb-3">
        <label for="button_link" class="form-label">Посилання кнопки</label>
        <input type="url" id="button_link" name="button_link"
               class="form-control @error('button_link') is-invalid @enderror"
               value="{{ old('button_link', $specialOffer->button_link) }}">
        @error('button_link') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Дата завершення -->
      <div class="mb-3">
        <label for="expires_at" class="form-label">Дата завершення</label>
        <input type="datetime-local" id="expires_at" name="expires_at"
               class="form-control @error('expires_at') is-invalid @enderror"
               value="{{ old('expires_at', $specialOffer->expires_at ? $specialOffer->expires_at->format('Y-m-d\TH:i') : '') }}">
        @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <!-- Активність -->
      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
               {{ old('is_active', $specialOffer->is_active) ? 'checked' : '' }}>
        <label class="form-check-label fw-semibold" for="is_active">Активна</label>
      </div>

      <!-- Кнопка збереження зі спінером -->
      <button type="submit" id="saveBtn" class="btn btn-success px-5 py-2 fw-semibold">
        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
        <i class="bi bi-check-circle me-2"></i>
        <span class="btn-text">Зберегти</span>
      </button>
    </form>
  </div>
</div>

<!-- Fullscreen overlay loader -->
<div id="screenLoader" class="screen-loader d-none" aria-hidden="true">
  <div class="text-center">
    <div class="spinner-border text-purple" role="status" style="width:4rem;height:4rem;"></div>
    <div class="mt-3 fw-semibold" style="color: #7367f0;">Збереження…</div>
  </div>
</div>
@endsection

@push('page-styles')
<style>
  .screen-loader{
    position: fixed;
    inset: 0;
    background: rgba(33, 37, 41, .45);
    backdrop-filter: blur(1px);
    -webkit-backdrop-filter: blur(1px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2050;
    pointer-events: all;
  }
  .spinner-border.text-purple { color: #7367f0 !important; }
</style>
@endpush

@push('page-scripts')
<script>
window.previewImage = function(input, previewId) {
  const img     = document.getElementById(previewId);
  const spinner = document.getElementById(previewId + 'Spinner');
  const nameLbl = document.getElementById(previewId + 'Name');

  if (spinner) spinner.classList.remove('d-none');
  if (img) { img.style.display = 'none'; img.removeAttribute('src'); }
  if (nameLbl) { nameLbl.classList.add('d-none'); nameLbl.textContent = ''; }

  if (!input.files || !input.files[0]) { if (spinner) spinner.classList.add('d-none'); return; }

  const file = input.files[0];
  if (!file.type || !file.type.startsWith('image/')) {
    if (spinner) spinner.classList.add('d-none');
    input.value = '';
    alert('Будь ласка, оберіть файл зображення (jpg, png, webp...)');
    return;
  }

  const objectUrl = URL.createObjectURL(file);

  img.onload = function() {
    if (spinner) spinner.classList.add('d-none');
    img.style.display = 'block';
    URL.revokeObjectURL(objectUrl);
    if (nameLbl) {
      nameLbl.textContent = file.name + (file.size ? ' • ' + Math.round(file.size/1024) + ' KB' : '');
      nameLbl.classList.remove('d-none');
    }
  };

  img.onerror = function() {
    if (spinner) spinner.classList.add('d-none');
    img.style.display = 'none';
    URL.revokeObjectURL(objectUrl);
    alert('Не вдалося відобразити превʼю зображення.');
  };

  img.alt = file.name || 'Превʼю';
  img.src = objectUrl;
};

(function(){
  const form = document.getElementById('special-offer-form');
  const screenLoader = document.getElementById('screenLoader');
  const saveBtn = document.getElementById('saveBtn');
  if (!form) return;

  form.addEventListener('submit', function(){
    if (saveBtn){
      saveBtn.disabled = true;
      const sp  = saveBtn.querySelector('.spinner-border');
      const txt = saveBtn.querySelector('.btn-text');
      if (sp)  sp.classList.remove('d-none');
      if (txt) txt.textContent = 'Збереження…';
    }
    if (screenLoader) screenLoader.classList.remove('d-none');
  }, { passive: true });
})();
</script>
@endpush
