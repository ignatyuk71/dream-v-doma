@extends('admin.layouts.vuexy')

@section('title', 'Meta Pixel & CAPI')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Заголовок + хлібні крихти --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-1">Трекінг: Meta Pixel &amp; CAPI</h4>
      <div class="text-muted small">Керування скриптами Meta Pixel і Conversions API</div>
    </div>
    <div>
      <a href="{{ route('admin.settings_pixel.index') }}" class="btn btn-outline-secondary">
        <i class="ti tabler-arrow-left me-1"></i> До налаштувань
      </a>
    </div>
  </div>

  {{-- Alerts --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      <i class="ti tabler-check me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger" role="alert">
      <div class="d-flex align-items-start">
        <i class="ti tabler-alert-triangle me-2 mt-1"></i>
        <div>
          <div class="fw-semibold mb-1">Перевірте форму:</div>
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.settings_pixel.tracking.update') }}" novalidate>
    @csrf
    @method('PUT')

    <div class="row g-4">
      {{-- META PIXEL --}}
      <div class="col-12 col-xl-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Meta Pixel</h5>
          </div>
          <div class="card-body">
            {{-- Увімкнено Pixel --}}
            <div class="mb-3 d-flex align-items-center justify-content-between">
              <label class="form-label mb-0" for="pixel_enabled">Увімкнути Meta Pixel</label>
              <div class="form-check form-switch m-0">
                <input type="checkbox" class="form-check-input" id="pixel_enabled" name="pixel_enabled"
                  @checked(old('pixel_enabled', $settings->pixel_enabled ?? false))>
              </div>
            </div>

            {{-- Pixel ID --}}
            <div class="mb-3">
              <label for="pixel_id" class="form-label">Pixel ID</label>
              <input type="text" class="form-control @error('pixel_id') is-invalid @enderror"
                     id="pixel_id" name="pixel_id"
                     value="{{ old('pixel_id', $settings->pixel_id ?? '') }}"
                     placeholder="Наприклад: 123456789012345">
              @error('pixel_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @else
                <div class="form-text">Зазвичай складається з цифр (ID пікселя у Meta Events Manager).</div>
              @enderror
            </div>

            {{-- Валюта за замовчуванням --}}
            <div class="mb-3">
              <label for="default_currency" class="form-label">Валюта за замовчуванням</label>
              <input type="text" class="form-control @error('default_currency') is-invalid @enderror"
                     id="default_currency" name="default_currency"
                     value="{{ old('default_currency', $settings->default_currency ?? 'UAH') }}"
                     maxlength="3" style="text-transform:uppercase"
                     placeholder="UAH / PLN / USD / EUR">
              @error('default_currency')
                <div class="invalid-feedback">{{ $message }}</div>
              @else
                <div class="form-text">Код ISO 4217 з 3 літер (UAH, PLN, USD, EUR...).</div>
              @enderror
            </div>

            {{-- Виключити адмін-розділ --}}
            <div class="mb-3 d-flex align-items-center justify-content-between">
              <label class="form-label mb-0" for="exclude_admin">Не відправляти з admin*</label>
              <div class="form-check form-switch m-0">
                <input type="checkbox" class="form-check-input" id="exclude_admin" name="exclude_admin"
                  @checked(old('exclude_admin', $settings->exclude_admin ?? true))>
              </div>
            </div>

            {{-- Consent --}}
            <div class="mb-1 d-flex align-items-center justify-content-between">
              <label class="form-label mb-0" for="require_consent">Відправляти лише з consent</label>
              <div class="form-check form-switch m-0">
                <input type="checkbox" class="form-check-input" id="require_consent" name="require_consent"
                  @checked(old('require_consent', $settings->require_consent ?? false))>
              </div>
            </div>
            <div class="form-text">Якщо увімкнено — події тригеряться лише після отримання згоди (cookie/banner).</div>

            <hr class="my-4">

            <div class="row g-3">
              <div class="col-12">
                <div class="fw-semibold mb-2">Події Meta Pixel</div>
              </div>

              @php
                $events = [
                  'send_page_view'         => 'PageView',
                  'send_view_content'      => 'ViewContent',
                  'send_add_to_cart'       => 'AddToCart',
                  'send_initiate_checkout' => 'InitiateCheckout',
                  'send_purchase'          => 'Purchase',
                  'send_lead'              => 'Lead',
                ];
              @endphp

              @foreach ($events as $name => $label)
                <div class="col-12 d-flex align-items-center justify-content-between">
                  <label class="form-label mb-0" for="{{ $name }}">{{ $label }}</label>
                  <div class="form-check form-switch m-0">
                    <input type="checkbox" class="form-check-input" id="{{ $name }}" name="{{ $name }}"
                      @checked(old($name, $settings->{$name} ?? false))>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      {{-- CAPI --}}
      <div class="col-12 col-xl-6">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="card-title mb-0">Meta Conversions API</h5>
          </div>
          <div class="card-body">
            {{-- Увімкнено CAPI --}}
            <div class="mb-3 d-flex align-items-center justify-content-between">
              <label class="form-label mb-0" for="capi_enabled">Увімкнути CAPI</label>
              <div class="form-check form-switch m-0">
                <input type="checkbox" class="form-check-input" id="capi_enabled" name="capi_enabled"
                  @checked(old('capi_enabled', $settings->capi_enabled ?? false))>
              </div>
            </div>

            {{-- CAPI Token --}}
            <div class="mb-3">
            <label for="capi_token" class="form-label">Access Token</label>
            <textarea class="form-control form-control-lg @error('capi_token') is-invalid @enderror"
                        id="capi_token" name="capi_token"
                        rows="4"
                        placeholder="EAAB... (Meta Access Token)">{{ old('capi_token', $settings->capi_token ?? '') }}</textarea>
            @error('capi_token')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="form-text">Токен з Events Manager → Data Sources → Settings → Conversions API.</div>
            @enderror
            </div>
            {{-- Test code --}}
            <div class="mb-3">
              <label for="capi_test_code" class="form-label">Test Event Code</label>
              <input type="text" class="form-control @error('capi_test_code') is-invalid @enderror"
                     id="capi_test_code" name="capi_test_code"
                     value="{{ old('capi_test_code', $settings->capi_test_code ?? '') }}"
                     placeholder="TEST123ABC">
              @error('capi_test_code')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">Опційно: дозволяє бачити події у вкладці Test Events.</div>
            </div>

            {{-- API version --}}
            <div class="mb-3">
              <label for="capi_api_version" class="form-label">API Version</label>
              <select class="form-select @error('capi_api_version') is-invalid @enderror"
                      id="capi_api_version" name="capi_api_version">
                @php
                  $ver = old('capi_api_version', $settings->capi_api_version ?? 'v20.0');
                  $options = ['v20.0','v21.0']; // Можеш розширити список
                @endphp
                @foreach ($options as $opt)
                  <option value="{{ strtolower($opt) }}" @selected(strtolower($ver) === strtolower($opt))>{{ $opt }}</option>
                @endforeach
              </select>
              @error('capi_api_version')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="alert alert-info mb-0">
              <div class="d-flex">
                <i class="ti tabler-info-circle me-2 mt-1"></i>
                <div class="small">
                  Для коректної роботи CAPI потрібні валідні <code>fbp</code> і <code>fbc</code> (де доречно),
                  а також відповідність параметрів подій політикам Meta.
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- Submit --}}
      <div class="col-12">
        <div class="card">
          <div class="card-body d-flex flex-wrap gap-2 justify-content-end">
            <a href="{{ route('admin.settings_pixel.index') }}" class="btn btn-outline-secondary">Скасувати</a>
            <button type="submit" class="btn btn-primary">
              <i class="ti tabler-device-floppy me-1"></i> Зберегти
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const $pixelEnabled = document.getElementById('pixel_enabled');
    const $capiEnabled  = document.getElementById('capi_enabled');

    function togglePixelFields() {
      const on = $pixelEnabled.checked;
      document.getElementById('pixel_id').disabled = !on;
      document.getElementById('default_currency').disabled = !on;
      document.getElementById('exclude_admin').disabled = !on;
      document.getElementById('require_consent').disabled = !on;

      ['send_page_view','send_view_content','send_add_to_cart','send_initiate_checkout','send_purchase','send_lead']
        .forEach(id => {
          const el = document.getElementById(id);
          if (el) el.disabled = !on;
        });
    }

    function toggleCapiFields() {
      const on = $capiEnabled.checked;
      ['capi_token','capi_test_code','capi_api_version'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = !on;
      });
    }

    if ($pixelEnabled) {
      $pixelEnabled.addEventListener('change', togglePixelFields);
      togglePixelFields();
    }
    if ($capiEnabled) {
      $capiEnabled.addEventListener('change', toggleCapiFields);
      toggleCapiFields();
    }
  })();
</script>
@endpush
