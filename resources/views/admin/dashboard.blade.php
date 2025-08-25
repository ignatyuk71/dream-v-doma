@extends('admin.layouts.vuexy')
@section('title', 'Дашборд')

@section('content')
<div class="container-xxl py-4">

  {{-- KPI --}}
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Замовлення</div>
            <div class="h4 mb-0">{{ $kpi['orders'] ?? '1 245' }}</div>
          </div>
          <span class="badge bg-label-primary p-3 rounded-circle">
            <i class="bi bi-bag fs-5"></i>
          </span>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Виручка</div>
            <div class="h4 mb-0">{{ $kpi['revenue'] ?? '$48 900' }}</div>
          </div>
          <span class="badge bg-label-success p-3 rounded-circle">
            <i class="bi bi-currency-dollar fs-5"></i>
          </span>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Клієнти</div>
            <div class="h4 mb-0">{{ $kpi['customers'] ?? '8 549' }}</div>
          </div>
          <span class="badge bg-label-info p-3 rounded-circle">
            <i class="bi bi-people fs-5"></i>
          </span>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card shadow-sm h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small">Середній чек</div>
            <div class="h4 mb-0">{{ $kpi['aov'] ?? '$39.2' }}</div>
          </div>
          <span class="badge bg-label-warning p-3 rounded-circle">
            <i class="bi bi-graph-up fs-5"></i>
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- Графіки --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-8">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Виручка по місяцях</h5>
          <span class="text-body-secondary small">Оновлено {{ $updatedAt ?? 'сьогодні' }}</span>
        </div>
        <div class="card-body">
          <div id="revenueArea" style="min-height: 320px;"></div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header">
          <h5 class="mb-0">Канали продажів</h5>
        </div>
        <div class="card-body">
          <div id="channelsDonut" style="min-height: 320px;"></div>
          <div class="d-flex justify-content-between mt-3 small text-muted">
            <span>Маркетплейси</span><span id="mpPct">—</span>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>Сайт</span><span id="sitePct">—</span>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>Instagram</span><span id="igPct">—</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Популярні товари + Останні замовлення --}}
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Популярні товари</h5>
          <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-seam me-1"></i> Всі товари
          </a>
        </div>
        <div class="card-body">
          <ul class="list-unstyled m-0">
            @forelse(($topProducts ?? []) as $p)
              <li class="d-flex align-items-center mb-3">
                <img src="{{ asset($p['thumb']) }}" class="rounded me-3" width="46" height="46" style="object-fit:cover" alt="">
                <div class="flex-grow-1">
                  <div class="fw-semibold text-truncate">{{ $p['name'] }}</div>
                  <div class="small text-muted">SKU: {{ $p['sku'] }}</div>
                </div>
                <div class="fw-semibold">{{ $p['price'] }}</div>
              </li>
            @empty
              <li class="text-muted text-center py-4">Даних поки немає.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Останні замовлення</h5>
          <a href="" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-receipt me-1"></i> До списку
          </a>
        </div>
        <div class="card-body table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Клієнт</th>
                <th>Сума</th>
                <th>Статус</th>
                <th>Дата</th>
                <th class="text-end">Дії</th>
              </tr>
            </thead>
            <tbody>
              @forelse(($orders ?? []) as $o)
                <tr>
                  <td>{{ $o['id'] }}</td>
                  <td class="text-truncate" style="max-width:220px">{{ $o['customer'] }}</td>
                  <td class="fw-semibold">{{ $o['total'] }}</td>
                  <td>
                    @php $status = $o['status'] ?? 'new'; @endphp
                    <span class="badge {{ [
                      'new'=>'bg-label-primary',
                      'paid'=>'bg-label-success',
                      'shipped'=>'bg-label-info',
                      'canceled'=>'bg-label-danger'
                    ][$status] ?? 'bg-secondary' }}">
                      {{ ucfirst($status) }}
                    </span>
                  </td>
                  <td class="text-muted small">{{ $o['date'] }}</td>
                  <td class="text-end">
                    <div class="dropdown">
                      <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('admin.orders.show', $o['id']) }}" class="dropdown-item">
                          <i class="bi bi-eye me-2"></i> Переглянути
                        </a>
                        <a href="{{ route('admin.orders.edit', $o['id']) }}" class="dropdown-item">
                          <i class="bi bi-pencil-square me-2"></i> Редагувати
                        </a>
                        <a href="#" class="dropdown-item text-danger">
                          <i class="bi bi-x-circle me-2"></i> Скасувати
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-muted py-4">Замовлень поки немає.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('vendor-scripts')
  {{-- ApexCharts з Vuexy --}}
  <script src="{{ asset('vendor/vuexy/assets') }}/vendor/libs/apex-charts/apexcharts.js"></script>
@endpush

@push('page-scripts')
@php
  // Безпечні фолбеки, щоб не ламалось, якщо контролер не дав дані
  $revenueSeries = $charts['revenue'] ?? [31,40,28,51,42,109,100,120,98,115,90,140];
  $channelSeries = $charts['channels'] ?? [44,36,20]; // mp, site, ig
@endphp
<script>
  // Revenue area
  (function () {
    const el = document.querySelector('#revenueArea');
    if (!el || typeof ApexCharts === 'undefined') return;

    const dataSeries = @json($revenueSeries);

    const options = {
      chart: { type: 'area', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
      series: [{ name: 'Виручка', data: dataSeries }],
      xaxis: { categories: ['Січ','Лют','Бер','Кві','Тра','Чер','Лип','Сер','Вер','Жов','Лис','Гру'] },
      stroke: { curve: 'smooth', width: 3 },
      dataLabels: { enabled: false },
      colors: undefined,
      fill: { type: 'gradient', gradient: { shadeIntensity: .2, opacityFrom: .4, opacityTo: .1, stops: [0,90,100]} },
      grid: { borderColor: 'rgba(0,0,0,.05)' },
      yaxis: { labels: { formatter: v => '$' + v } },
      tooltip: { y: { formatter: v => '$' + v } }
    };
    new ApexCharts(el, options).render();
  })();

  // Channels donut
  (function () {
    const el = document.querySelector('#channelsDonut');
    if (!el || typeof ApexCharts === 'undefined') return;

    const series = @json($channelSeries);
    const options = {
      chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
      series,
      labels: ['Маркетплейси','Сайт','Instagram'],
      legend: { position: 'bottom' },
      dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%' }
    };
    const chart = new ApexCharts(el, options);
    chart.render();

    chart.updateSeries(series).then(() => {
      const sum = series.reduce((a,b)=>a+b,0) || 1;
      document.getElementById('mpPct').textContent   = Math.round(series[0]/sum*100)+'%';
      document.getElementById('sitePct').textContent = Math.round(series[1]/sum*100)+'%';
      document.getElementById('igPct').textContent   = Math.round(series[2]/sum*100)+'%';
    });
  })();
</script>
@endpush
