@extends('admin.layouts.vuexy')

@section('title', 'Налаштування Pixel & CAPI')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

  {{-- Заголовок --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
      <h4 class="mb-1">Налаштування трекінгу</h4>
      <div class="text-muted small">Meta Pixel &amp; Conversions API</div>
    </div>
    <div>
      <a href="{{ route('admin.settings_pixel.tracking') }}" class="btn btn-primary">
        <i class="ti tabler-adjustments me-1"></i> Редагувати
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

  {{-- Cards --}}
  <div class="row g-4">
    {{-- Meta Pixel --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Meta Pixel</h5>
          <span class="badge {{ $trackingSummary['pixel_enabled'] ? 'bg-success' : 'bg-secondary' }}">
            {{ $trackingSummary['pixel_enabled'] ? 'Увімкнено' : 'Вимкнено' }}
          </span>
        </div>
        <div class="card-body">
          <p><strong>Pixel ID:</strong> {{ $trackingSummary['pixel_id'] ?? '—' }}</p>
          <p><strong>Валюта:</strong> {{ $trackingSummary['default_currency'] }}</p>
          <div>
            <strong>Події:</strong>
            <ul class="mb-0">
              @foreach ($trackingSummary['events'] as $event => $enabled)
                <li>
                  {{ ucfirst(str_replace('_', ' ', $event)) }}: 
                  <span class="{{ $enabled ? 'text-success' : 'text-muted' }}">
                    {{ $enabled ? '✓' : '—' }}
                  </span>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>

    {{-- CAPI --}}
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Meta Conversions API</h5>
          <span class="badge {{ $trackingSummary['capi_enabled'] ? 'bg-success' : 'bg-secondary' }}">
            {{ $trackingSummary['capi_enabled'] ? 'Увімкнено' : 'Вимкнено' }}
          </span>
        </div>
        <div class="card-body">
          <p>
            <strong>Статус:</strong>
            {{ $trackingSummary['capi_enabled'] ? 'Активний' : 'Неактивний' }}
          </p>
          <p class="text-muted small mb-0">
            Переглянь деталі у <a href="{{ route('admin.settings_pixel.tracking') }}">редакторі налаштувань</a>.
          </p>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
