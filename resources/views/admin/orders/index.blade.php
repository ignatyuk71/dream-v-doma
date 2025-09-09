@extends('admin.layouts.vuexy')

@section('title','Замовлення — Список')

@section('content')
@php
$placeholder = '/assets/img/placeholder.svg';

// ===== helpers =====
$statusLabels = \App\Enums\OrderStatus::labels();
$pageSum      = $orders->sum('total_price'); // сума на сторінці
$ordersCount  = $orders->count();
$avgOrder     = $ordersCount ? round($pageSum / $ordersCount, 2) : 0;
$currency     = $orders->first()->currency ?? 'UAH';

$img = function($url) {
  if (!$url) return null;
  return (str_starts_with($url,'http://') || str_starts_with($url,'https://') || str_starts_with($url,'//'))
    ? $url
    : '/storage/' . ltrim($url,'/');
};
$money = fn($n, $cur = 'UAH') => number_format((float)$n, 2, '.', ' ') . ' ' . $cur;

// Bootstrap/Vuexy бейджі
$badge = fn($s) => match($s) {
  'pending'    => 'badge px-2 bg-label-warning text-capitalized',
  'confirmed'  => 'badge px-2 bg-label-info text-capitalized',
  'processing' => 'badge px-2 bg-label-success text-capitalized',
  'packed'     => 'badge px-2 bg-label-secondary text-capitalized',
  'shipped'    => 'badge px-2 bg-label-primary text-capitalized',
  'delivered'  => 'badge px-2 bg-label-success text-capitalized',
  'cancelled'  => 'badge px-2 bg-label-dark text-capitalized',
  'returned'   => 'badge px-2 bg-label-danger text-capitalized',
  'refunded'   => 'badge px-2 bg-label-success text-capitalized',
  default      => 'badge px-2 bg-label-secondary text-capitalized',
};

$statusMeta = [];
foreach ($statusLabels as $val => $label) {
  $statusMeta[$val] = [
    'label' => $label,
    'class' => $badge($val),
  ];
}
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
  <div id="tpl-orders">

    <!-- ===== Toolbar / KPIs ===== -->
    <div class="toolbar">
      <div class="t-chip">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="3" stroke="#16a34a"></rect>
          <path d="M7 12l3 3 7-7" stroke="#16a34a"></path>
        </svg>
        Сума на сторінці: <b>{{ number_format($pageSum, 0, '.', ' ') }} {{ $currency }}</b>
      </div>

      <div class="kpi">
        <div class="k"><span class="k-label">Замовлень</span><span class="k-val">{{ $ordersCount }}</span></div>
        <div class="k"><span class="k-label">Середній чек</span><span class="k-val">{{ $money($avgOrder, $currency) }}</span></div>
      </div>

      <div class="spacer"></div>

      <div class="tool" id="bulk-export">Експорт</div>
      <div class="tool muted" id="bulk-print">Друк</div>
    </div>

    {{-- ===== Filters ===== --}}
    <form method="GET" action="{{ url()->current() }}" class="card mb-3">
      <div class="card-body py-3">
        <div class="row g-2 align-items-end">

          {{-- Пошук --}}
          <div class="col-12 col-md-5">
            <label class="form-label mb-1">Пошук</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                     placeholder="Ім’я / телефон / email / № замовлення / артикул">
            </div>
          </div>

          {{-- Статуси (мультивибір) --}}
          @php
            $selectedStatuses = collect((array)request('status'))
              ->map(fn($v)=> (string)$v)->unique()->values()->all();
          @endphp
          <div class="col-12 col-md-4">
            <label class="form-label mb-1 d-block">Статуси</label>

            <div class="status-chip-select" data-name="status[]">
              <button type="button" class="form-control d-flex align-items-center justify-content-between"
                      data-bs-toggle="dropdown" aria-expanded="false">
                <span class="chips d-flex flex-wrap gap-2">
                  @forelse($selectedStatuses as $s)
                    <span class="badge bg-success d-inline-flex align-items-center gap-2" data-chip="{{ $s }}">
                      {{ $statusLabels[$s] ?? $s }}
                      <i class="bi bi-x-lg" data-remove="{{ $s }}" role="button"></i>
                    </span>
                  @empty
                    <span class="text-muted">Усі статуси</span>
                  @endforelse
                </span>
                <i class="bi bi-caret-down-fill ms-auto"></i>
              </button>

              <div class="dropdown-menu p-2 w-100">
                <div class="d-flex justify-content-between align-items-center px-1 pb-2">
                  <strong>Обрати статуси</strong>
                  <button type="button" class="btn btn-sm btn-link text-decoration-none js-clear-status">Очистити</button>
                </div>
                <div class="status-list" style="max-height:220px; overflow:auto;">
                  @foreach($statusLabels as $val => $label)
                    <label class="dropdown-item d-flex align-items-center gap-2">
                      <input type="checkbox" class="form-check-input"
                             value="{{ $val }}" @checked(in_array($val,$selectedStatuses,true))>
                      <span>{{ $label }}</span>
                    </label>
                  @endforeach
                </div>
              </div>

              <div class="hidden-inputs">
                @foreach($selectedStatuses as $s)
                  <input type="hidden" name="status[]" value="{{ $s }}">
                @endforeach
              </div>
            </div>
          </div>

          {{-- Дата від --}}
          <div class="col-6 col-md-1">
            <label class="form-label mb-1">З дати</label>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
          </div>

          {{-- Дата до --}}
          <div class="col-6 col-md-1">
            <label class="form-label mb-1">По дату</label>
            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
          </div>

          <div class="col-12 d-flex gap-2 justify-content-end mt-2">
            <a href="{{ url()->current() }}" class="btn btn-light">
              <i class="bi bi-x-circle"></i> Скинути
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-funnel"></i> Застосувати
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- ===== Table ===== -->
    <div class="card">
      <div class="table-wrap">
        <table class="table" id="ordersAccordion">
          <thead class="thead sticky">
            <tr>
              <th style="width:42px;"></th>
              <th style="width:240px">Номер і дата</th>
              <th style="width:160px">Товари</th>
              <th>Покупець</th>
              <th style="width:150px" class="text-end">Сума</th>
              <th style="width:260px">Оплата / Статус</th>
              <th style="width:160px" class="text-end">Дії</th>
            </tr>
          </thead>
          <tbody>
          @forelse($orders as $o)
            @php
              $firstItem  = $o->items->first();
              $collapseId = 'ord-'.$o->id;
              $statusVal  = $o->status->value ?? (string)$o->status;
              $statusLbl  = $statusLabels[$statusVal] ?? (string)$statusVal; // без strtoupper
              $qtySum     = (int)($o->items->sum('quantity') ?: $o->items->count());
              $paidState  = $o->payment_status ?? ($o->paid_at ? 'paid' : 'unpaid');
              $shipCost   = $o->shipping_price ?? null;
              $discount   = $o->discount_total ?? null;
              $coupon     = $o->coupon_code ?? null;
            @endphp

<!-- summary -->
<tr class="order-row">
  <td class="left-gutter">
    <button class="btn btn-sm btn-link p-0 toggle-row" type="button"
            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
            aria-expanded="false" aria-controls="{{ $collapseId }}">
      <span class="chev">▶</span>
    </button>
  </td>

  <td>
    <div class="order-no">#{{ $o->order_number ?? $o->id }}</div>
    <div class="small muted">{{ $o->created_at?->format('d.m.Y, H:i') }}</div>

    <div class="payline small">
      <svg class="picon" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
      {{ $o->payment_method ?? 'Спосіб оплати не вказано' }}
    </div>

    @if($o->delivery?->np_ref)
      <div class="payline small">
        <svg class="picon" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M15 9l-6 6"></path></svg>
        <b>ТТН:</b>
      </div>
    @endif
  </td>

  <td>
  <div class="d-flex align-items-center gap-2">
    <img src="{{ $img($firstItem?->image_url) }}"
         onerror="this.onerror=null;this.src='{{ $placeholder }}';"
         alt="" width="74" height="74"
         style="object-fit:cover;border-radius:10px;">
    <div class="small">
        <div><b>Товарів:</b> {{ $qtySum }}</div>
    </div>
</div>
</td>


  <td>
    <div class="buyer-name">{{ $o->customer->name ?? '—' }}</div>
    @if($o->customer?->phone)
      <div class="small muted d-flex align-items-center gap-2">
        <a href="tel:{{ preg_replace('/\s+/', '', $o->customer->phone) }}">{{ $o->customer->phone }}</a>
        <button type="button"
                class="btn p-0 border-0 bg-transparent js-copy"
                title="Копіювати"
                data-copy="{{ preg_replace('/\s+/', '', $o->customer->phone) }}">
          <svg width="15" height="15" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
        </button>
      </div>
    @endif
    @if($o->customer?->email)
      <div class="small muted"><a href="mailto:{{ $o->customer->email }}">{{ $o->customer->email }}</a></div>
    @endif
  </td>

  <td class="right nowrap text-end">
    <div class="fw-semibold">{{ $money($o->total_price, $o->currency ?? $currency) }}</div>
    @if($discount || $shipCost)
      <div class="small muted">
        @if($discount)<span>знижка: -{{ $money($discount, $o->currency ?? $currency) }}</span>@endif
        @if($shipCost)<span class="ms-2">доставка: {{ is_numeric($shipCost) ? $money($shipCost, $o->currency ?? $currency) : 'перевізник' }}</span>@endif
      </div>
    @endif
  </td>

  <!-- Статус праворуч, в один ряд -->
  <td class="text-end">
    <div class="d-inline-flex align-items-center gap-2 flex-wrap justify-content-end w-100">
      <span class="{{ $badge($statusVal) }} status-pill"
            data-status-badge data-status="{{ $statusVal }}">{{ $statusLbl }}</span>

      <div class="dropdown dropstart">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
          Змінити
        </button>
        <ul class="dropdown-menu dropdown-menu-end p-2">
          @foreach($statusLabels as $val => $label)
            <li>
              <button type="button"
                      class="dropdown-item d-flex align-items-center gap-2 status-change"
                      data-url="{{ route('admin.orders.status.update', $o) }}"
                      data-id="{{ $o->id }}"
                      data-status="{{ $val }}">
                <span class="{{ $badge($val) }}">{{ $label }}</span>
              </button>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </td>

  <td class="text-end">
    <div class="actions">
        <!-- Відкрити -->
        <a class="a" href="{{ route('admin.orders.show', $o) }}" title="Відкрити">
            <i class="bi bi-box-arrow-up-right text-success"></i>
        </a>

        <!-- Видалити -->
        <form action="{{ route('admin.orders.destroy', $o) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Точно видалити замовлення #{{ $o->id }}?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="a border-0 bg-transparent" title="Видалити">
                <i class="bi bi-trash text-danger"></i>
            </button>
        </form>
    </div>
</td>

</tr>

            <!-- details -->
            <tr class="details-row">
              <td colspan="7" class="p-0 border-0">
                <div id="{{ $collapseId }}" class="collapse" data-bs-parent="#ordersAccordion">
                  <div class="details-panel">

                    <!-- ===== 3 колонки ===== -->
                    <div class="grid-3">

                      <!-- Buyer -->
                      <div class="card shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">Про покупця</h5>
                        </div>
                        <div class="card-body">

                          <!-- Отримувач -->
                          <div class="py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                              <div class="text-muted small d-flex align-items-center gap-2">
                                Отримувач
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="2"></circle><path d="M12 5v3"></path><path d="M12 16v3"></path><path d="M19 12h-3"></path><path d="M8 12H5"></path></svg>
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3Z"></path><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7Z"></path></svg>
                              </div>
                              <div class="d-flex align-items-center gap-2">
                                <a class="text-body text-decoration-none" href="tel:{{ $o->customer->phone ?? '' }}">
                                  {{ $o->customer->phone ?? '—' }}
                                </a>
                                <button type="button" class="btn p-0 border-0 bg-transparent js-copy" title="Копіювати телефон" data-copy="{{ preg_replace('/\s+/', '', $o->customer->phone ?? '') }}">
                                  <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
                                </button>
                              </div>
                            </div>
                            <div class="mt-1 fw-semibold">{{ $o->customer->name ?? '—' }}</div>
                          </div>

                          <!-- Замовник -->
                          <div class="py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                              <div class="text-muted small">Замовник</div>
                              <div class="d-flex align-items-center gap-2">
                                <a class="text-body text-decoration-none" href="tel:{{ $o->customer->phone ?? '' }}">
                                  {{ $o->customer->phone ?? '—' }}
                                </a>
                                <button type="button" class="btn p-0 border-0 bg-transparent js-copy" title="Копіювати телефон" data-copy="{{ preg_replace('/\s+/', '', $o->customer->phone ?? '') }}">
                                  <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
                                </button>
                              </div>
                            </div>
                            <div class="mt-1 fw-semibold">{{ $o->customer->name ?? '—' }}</div>
                          </div>

                          <!-- Кількість покупок -->
                          <div class="py-3">
                            <div class="d-flex align-items-center gap-2">
                              <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"></path><path d="M17 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"></path><path d="M6.2 4l-.3-2H1v2h3l2.4 12.2A2 2 0 0 0 8.4 18h8.9a2 2 0 0 0 2-1.6L21 8H6.6L6.2 4Z"></path><path d="M18.8 10l-1.2 6H8.4L7.3 10h11.5Z"></path></svg>
                              <span>Кількість покупок: {{ $o->customer?->orders_count ?? 1 }}</span>
                            </div>
                          </div>

                        </div>
                      </div>
                      <!-- /Buyer -->

                      <!-- Address -->
                      <div class="card shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">Адреса доставки</h5>
                        </div>

                        <div class="card-body">
                          <!-- Служба -->
                          <div class="py-3 border-bottom">
                            <div class="text-muted small">Служба</div>
                            <div class="mt-1 fw-semibold d-flex align-items-center gap-2">
                              @if(($o->delivery->delivery_type ?? '') === 'courier')
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 8h-3V4H3v10h1a3 3 0 1 0 6 0h4a3 3 0 1 0 6 0h1v-4l-2-2Z"></path><path d="M17 14H10a3 3 0 0 0-5.83 0H5V6h10v3h3l2 2v3h-.17A3 3 0 0 0 17 14Z"></path></svg>
                                <span>курєр</span>
                              @elseif(($o->delivery->delivery_type ?? '') === 'branch')
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3V5Z"></path><path d="M5 7v2l7 4 7-4V7H5Z"></path></svg>
                                <span>на пошту</span>
                              @elseif(($o->delivery->delivery_type ?? '') === 'postomat')
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18v16H3V4Z"></path><path d="M5 6v12h14V6H5Z"></path><path d="M7 8h4v3H7V8Z"></path><path d="M12 8h5v3h-5V8Z"></path><path d="M7 12h4v3H7v-3Z"></path><path d="M12 12h5v3h-5v-3Z"></path></svg>
                                <span>поштомат</span>
                              @else
                                <span>{{ $o->delivery->delivery_type ?? '—' }}</span>
                              @endif
                            </div>
                          </div>

                          @if($o->delivery?->np_description)
                            <div class="py-3 border-bottom">
                              <div class="text-muted small">Відділення</div>
                              <div class="mt-1 fw-medium">{{ $o->delivery->np_description }}</div>
                            </div>
                          @endif

                          <div class="py-3 border-bottom">
                            <div class="text-muted small">Адреса</div>
                            <div class="mt-1 fw-medium">{{ $o->delivery->np_address ?? $o->delivery->courier_address ?? '—' }}</div>
                          </div>

                          @if($o->delivery?->np_ref)
                            <div class="py-3 border-bottom">
                              <div class="text-muted small">ТТН</div>
                              <div class="mt-1 fw-semibold d-flex align-items-center gap-2">
                                <span>{{ $o->delivery->np_ref }}</span>
                                <button type="button" class="btn p-0 border-0 bg-transparent js-copy" title="Копіювати ТТН" data-copy="{{ $o->delivery->np_ref }}">
                                  <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
                                </button>
                              </div>
                            </div>
                          @endif

                          <div class="pt-3">
                            <div class="d-flex align-items-center gap-2">
                              <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 7H3v8a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V7Z"></path><path d="M17 14H7v-2h10v2Z"></path><path d="M17 10H7V8h10v2Z"></path></svg>
                              <span>Зворотна доставка коштів: {{ ($o->delivery->cod ?? false) ? 'увімкнено' : 'вимкнено' }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <!-- /Address -->

                      <!-- Payment -->
                      <div class="card shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                          <h5 class="mb-0">Оплата</h5>
                        </div>

                        <div class="card-body">
                          <!-- Всього до сплати -->
                          <div class="mb-3">
                            <div class="text-muted small">Всього до сплати</div>
                            <div class="fs-4 fw-semibold">{{ $money($o->total_price, $o->currency ?? $currency) }}</div>
                          </div>

                          <!-- Деталізація -->
                          <div class="ps-3 border-start border-2 mb-3" style="--bs-border-opacity:.25;">
                            <div class="py-2">
                              <div class="text-muted small">Вартість товарів в замовленні</div>
                              <div class="fw-medium">
                                {{ $money($o->subtotal_price ?? ($o->total_price - ($shipCost ?? 0) + ($discount ?? 0)), $o->currency ?? $currency) }}
                              </div>
                            </div>

                            <div class="py-2">
                              <div class="text-muted small">Вартість доставки</div>
                              <div class="fw-medium">
                                @if(($o->total_price ?? 0) >= 1000)
                                  Безкоштовно
                                @else
                                  @if(is_numeric($shipCost))
                                    {{ $money($shipCost, $o->currency ?? $currency) }}
                                  @else
                                    За тарифами перевізника
                                  @endif
                                @endif
                              </div>
                            </div>

                            <div class="py-2">
                              <div class="text-muted small d-flex align-items-center gap-1">
                                Вартість доставки для покупця
                                <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
                                  <circle cx="12" cy="12" r="2"></circle><path d="M12 5v3"></path><path d="M12 16v3"></path><path d="M19 12h-3"></path><path d="M8 12H5"></path>
                                </svg>
                              </div>
                              <div class="fw-medium">
                                @if(($o->total_price ?? 0) >= 1000)
                                  Безкоштовно
                                @else
                                  @if(is_numeric($shipCost))
                                    {{ $money($shipCost, $o->currency ?? $currency) }}
                                  @else
                                    @if(($o->delivery->delivery_type ?? '') === 'courier')
                                      Оплата курєру — 135 ₴
                                    @elseif(($o->delivery->delivery_type ?? '') === 'branch')
                                      Оплата при отримані — 90–110 ₴
                                    @elseif(($o->delivery->delivery_type ?? '') === 'postomat')
                                      Оплата на поштоматі — 60 ₴
                                    @else
                                      За тарифами перевізника
                                    @endif
                                  @endif
                                @endif
                              </div>
                            </div>
                          </div>

                          <!-- Спосіб оплати -->
                          <div class="mb-3">
                            <div class="text-muted small">Спосіб оплати</div>
                            <div class="fw-medium">{{ $o->payment_method ?? '—' }}</div>
                          </div>

                          <!-- Статус оплати -->
                          <div class="mb-2">
                            <div class="text-muted small">Статус оплати</div>
                            <div class="fw-medium">
                              {{ $paidState === 'paid' ? 'Сплачено' : ($paidState === 'refunded' ? 'Повернено' : 'Не сплачено') }}
                            </div>
                          </div>

                          <!-- Комісія -->
                          <div class="mb-3">
                            <div class="text-muted small d-flex align-items-center gap-2">
                              Комісія за грошовий переказ
                            </div>
                            <div class="fw-medium">27.00 ₴</div>
                          </div>
                        </div>
                      </div>
                      <!-- /Payment -->

                    </div>
                    <!-- ===== /grid-3 ===== -->

                    <!-- Products (повна ширина під блоками) -->
                    <div class="card shadow-sm mt-3">
                      <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Товари ({{ $qtySum }})</h5>
                      </div>

                      <div class="card-body">
                        @forelse($o->items as $it)
                          <div class="d-flex align-items-center py-2 border-bottom">
                            <!-- thumb -->
                            <div class="flex-shrink-0 me-3">
                            @if($it->image_url)
                              <img src="{{ $img($it->image_url) }}"
                                  onerror="this.onerror=null;this.src='{{ $placeholder }}';"
                                  alt="" class="rounded-2 border" style="width:60px;height:60px;object-fit:cover;">
                            @else
                              <img src="{{ $placeholder }}" alt="" class="rounded-2 border" style="width:60px;height:60px;object-fit:cover;">
                            @endif
                            </div>

                            <!-- center -->
                            <div class="flex-grow-1 min-w-0">
                              <div class="fw-semibold text-truncate" title="{{ $it->product_name }}">
                                {{ $it->product_name }}
                              </div>
                              <div class="d-flex flex-wrap gap-1 mt-1">
                                <span class="badge text-bg-light">Артикул: {{ $it->variant_sku ?: '—' }}</span>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">Розмір: {{ $it->size ?: '—' }}</span>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">Колір: {{ $it->color ?: '—' }}</span>
                              </div>
                            </div>

                            <!-- right -->
                            <div class="text-end ms-3">
                              <div class="small text-muted">
                                {{ $money($it->price ?? 0, $o->currency ?? $currency) }} × {{ $it->quantity ?? 1 }}
                              </div>
                              <div class="fw-semibold">
                                {{ $money($it->total ?? (($it->price ?? 0) * ($it->quantity ?? 1)), $o->currency ?? $currency) }}
                              </div>
                            </div>
                          </div>
                        @empty
                          <div class="text-muted">Без товарів</div>
                        @endforelse
                      </div>
                    </div>
                    <!-- /Products -->

                  </div><!-- /details-panel -->
                </div><!-- /collapse -->
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-4 text-muted">Замовлень не знайдено</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <!-- ===== Pagination ===== -->
    <div class="pt-3">
      {{ $orders->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>
@endsection

@push('page-styles')
<style>

   /* базові змінні (посилив контраст значень через --text) */
   #tpl-orders{ --bg:#f5f6fa; --card:#fff; --muted:#6b7280; --text:#111; --border:#e6e8ef; --hover:#f3f4f6; }
  #tpl-orders *{ box-sizing:border-box }
  #tpl-orders{ color:var(--text) }

  .toolbar{background:#fff;border:1px solid var(--border);border-radius:12px;margin-bottom:12px;padding:10px 14px;display:flex;align-items:center;gap:12px}
  .t-chip{display:inline-flex;align-items:center;gap:8px;border:1px solid #dff4e6;background:#f2fff7;padding:8px 10px;border-radius:8px;color:#166534;font-weight:600}
  .kpi{display:flex;gap:16px;margin-left:12px}
  .k{background:#fafafa;border:1px solid var(--border);border-radius:10px;padding:8px 10px;min-width:140px;display:flex;flex-direction:column}
  .k-label{font-size:12px;color:#6b7280}
  .k-val{font-weight:700}
  .spacer{flex:1}
  .tool{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;border:1px solid var(--border);background:#fff;cursor:pointer;font-weight:600}
  .tool.muted{color:var(--muted);background:#fafafa}
  .icon{width:18px;height:18px}

  .card{background:#fff;border:1px solid var(--border);border-radius:12px}
  .table-wrap{overflow:auto}
  table.table{width:100%;border-collapse:separate;border-spacing:0}
  .thead th{font-size:12px;text-transform:uppercase;letter-spacing:.02em;color:var(--muted);text-align:left;padding:12px 14px;border-bottom:1px solid var(--border);background:#fff}
  .thead.sticky th{position:sticky;top:0;z-index:2}

  tr.order-row > td{padding:14px;border-bottom:1px solid var(--border);background:#fff;vertical-align:top}
  tr.order-row:hover > td{background:var(--hover)}
  .order-no{font-weight:700}
  .left-gutter{width:42px;text-align:center}
  .chev{display:inline-block;color:#20a35a;transition:transform .18s ease;margin-right:6px}
  .toggle-row[aria-expanded="true"] .chev{ transform: rotate(90deg) }

  .payline{display:flex;align-items:center;gap:8px;margin-top:8px;color:#475569}
  .picon{width:16px;height:16px}
  .thumb{width:74px;height:74px;border-radius:10px;background:#eceff5;border:1px solid var(--border)}
  .buyer-name{font-weight:600}
  .right{text-align:right}
  .nowrap{white-space:nowrap}
  .actions{display:inline-flex;gap:10px}
  .actions .a{width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center}

  /* glow/pulse для очікування */
  [data-status-badge][data-status="pending"]{ box-shadow:0 0 0 .25rem rgba(255,193,7,.18) }
  @keyframes softpulse{0%{box-shadow:0 0 0 .25rem rgba(255,193,7,.18)}50%{box-shadow:0 0 0 .45rem rgba(255,193,7,.28)}100%{box-shadow:0 0 0 .25rem rgba(255,193,7,.18)}}
  [data-status-badge][data-status="pending"]{ animation:softpulse 2s ease-in-out infinite }

  /* details */
  .details-row td{padding:0;background:#fafbff}
  .details-panel{padding:14px 16px;border-top:1px dashed var(--border)}
  .grid-3{display:grid;gap:16px;grid-template-columns:1.1fr 1.1fr .9fr}
  .d-card{background:#fff;border:1px solid var(--border);border-radius:14px}
  .d-head{padding:14px 16px;border-bottom:1px solid var(--border);font-weight:700}
  .d-body{padding:14px 16px}
  .kv{display:flex;gap:12px;align-items:center}
  .kv .k{width:160px;color:#6b7280;font-size:13px}
  .kv .v{flex:1}

  .product-item{display:grid;grid-template-columns:64px 1fr auto;gap:12px;align-items:center}
  .thumb2{width:64px;height:64px;border-radius:12px;background:#eceff5;border:1px solid var(--border)}
  .title{font-weight:700}

  @media (max-width: 1060px){
    .grid-3{grid-template-columns:1fr}
    .kv .k{width:120px}
  }

  .btn.btn-xs{--bs-btn-padding-y: .05rem; --bs-btn-padding-x: .25rem; --bs-btn-font-size: .72rem}

  /* ===== контраст значень: робимо текст значень чорним навіть у "приглушених" класах ===== */
  #tpl-orders .text-secondary-emphasis{ color: var(--text) !important; }
  #tpl-orders .fw-medium,
  #tpl-orders .fw-semibold,
  #tpl-orders .buyer-name,
  #tpl-orders .order-no,
  #tpl-orders .k-val,
  #tpl-orders .card-body .fs-4{
    color: var(--text) !important;
  }

  /* Fallback для класу з прикладу */
  #tpl-orders .text-capitalized { text-transform: capitalize; }

  /* ===== turn off any box-shadows & pulse on status badges ===== */
  #tpl-orders .badge{ box-shadow: none !important; }
  #tpl-orders [data-status-badge]{ animation: none !important; box-shadow: none !important; }
  #tpl-orders .status-pill{ box-shadow: none !important; }

  .status-chip-select .form-control { cursor: pointer; }
  .status-chip-select .chips { min-height: 1.6rem; }
  .status-chip-select .badge .bi-x-lg { font-size: .7rem; opacity:.9 }
  .status-chip-select .dropdown-menu { max-width: 100%; }
  /* ——— компактний мультивибір статусів ——— */
.status-chip-select button.form-control{
  padding: .35rem .5rem;         /* менший внутрішній відступ */
  min-height: 40px;               /* нижча "висота" поля у закритому стані */
}

/* чипи трохи менші */
.status-chip-select .chips .badge{
  font-size: .8rem;
  padding: .35rem .45rem;
  border-radius: .6rem;
}

/* саме меню робимо не на всю ширину та з меншим скролом */
/* робимо випадаюче меню статусів меншим */
.status-chip-select .dropdown-menu {
  width: 320px !important;    /* фіксована ширина */
  max-width: 90vw;            /* щоб на малих екранах не вилазило */
  padding: .5rem .75rem;      /* компактні паддінги */
  border-radius: .5rem;       /* плавні краї */
}

/* список опцій нижчий */
.status-chip-select .status-list{
  max-height: 160px;              /* було ~220 — зменшили */
  overflow: auto;
}

/* рядки опцій компактніші */
.status-chip-select .dropdown-item{
  padding: .25rem .5rem;          /* менший вертикальний паддінг */
  font-size: .95rem;
  line-height: 1.2;
}

/* чекбокси менші й вирівняні по центру */
.status-chip-select .dropdown-item .form-check-input{
  width: 1rem;
  height: 1rem;
  margin-top: 0;
}

</style>
@endpush

@push('page-scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('.status-chip-select');
    selects.forEach(function (el) {
      const hiddenInputs = el.querySelector('.hidden-inputs');
      const checkboxes   = el.querySelectorAll('.status-list input[type=checkbox]');
      const chipsWrap    = el.querySelector('.chips');
      const clearBtn     = el.querySelector('.js-clear-status');

      function renderHidden() {
        hiddenInputs.innerHTML = '';
        chipsWrap.innerHTML = '';
        let any = false;
        checkboxes.forEach(cb => {
          if (cb.checked) {
            any = true;
            hiddenInputs.insertAdjacentHTML('beforeend',
              `<input type="hidden" name="status[]" value="${cb.value}">`);
            chipsWrap.insertAdjacentHTML('beforeend',
              `<span class="badge bg-success d-inline-flex align-items-center gap-2" data-chip="${cb.value}">
                ${cb.nextElementSibling.textContent}
                <i class="bi bi-x-lg" data-remove="${cb.value}" role="button"></i>
              </span>`);
          }
        });
        if (!any) {
          chipsWrap.innerHTML = '<span class="text-muted">Усі статуси</span>';
        }
      }

      checkboxes.forEach(cb => cb.addEventListener('change', renderHidden));

      chipsWrap.addEventListener('click', function(e){
        const rm = e.target.closest('[data-remove]');
        if (!rm) return;
        const val = rm.getAttribute('data-remove');
        const cb  = el.querySelector(`.status-list input[value="${val}"]`);
        if (cb) cb.checked = false;
        renderHidden();
      });

      if (clearBtn) clearBtn.addEventListener('click', function(){
        checkboxes.forEach(cb => cb.checked = false);
        renderHidden();
      });
    });
  });
</script>

<script>
  const CSRF = '{{ csrf_token() }}';
  const STATUS_META = @json($statusMeta);

  // Клік по всьому рядку (крім інтерактивних) — відкриває/закриває деталі
  document.addEventListener('click', function (e) {
    const row = e.target.closest('tr.order-row');
    if (!row) return;
    if (e.target.closest('a, button, select, input, label, .dropdown-menu')) return;
    row.querySelector('.toggle-row')?.click();
  });

  // Копіювання (телефон/ТТН)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.js-copy');
    if (!btn) return;
    e.preventDefault();
    const text = btn.dataset.copy || '';
    if (!text) return;
    (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject())
      .then(()=>{
        const prev = btn.innerHTML;
        btn.innerHTML = '✔ скопійовано';
        setTimeout(()=> btn.innerHTML = prev, 1200);
      })
      .catch(()=>{
        const tmp = document.createElement('input');
        tmp.value = text;
        document.body.appendChild(tmp);
        tmp.select();
        try { document.execCommand('copy'); } catch(e) {}
        document.body.removeChild(tmp);
      });
  });

  // Зміна статусу
  document.addEventListener('click', async function(e){
    const item = e.target.closest('.status-change');
    if (!item) return;

    e.preventDefault();

    const status = item.dataset.status;
    const url    = item.dataset.url;

    let pill = item.closest('td')?.querySelector('[data-status-badge]');
    if (!pill) {
      const openedToggle = document.querySelector('button[data-bs-toggle="dropdown"].show');
      pill = openedToggle?.closest('td')?.querySelector('[data-status-badge]');
    }
    if (!pill) return;

    const prev = pill.getAttribute('data-status');
    applyStatus(pill, status); // optimistic UI

    if (!url) return;

    try{
      const res = await fetch(url, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status }),
        credentials: 'same-origin'
      });

      const ctype = res.headers.get('content-type') || '';
      if (res.redirected || ctype.startsWith('text/html')) throw new Error('redirect/html');
      if (!res.ok) throw new Error(await res.text() || ('HTTP ' + res.status));
      await res.json().catch(()=>({}));

    }catch(err){
      applyStatus(pill, prev); // rollback
      alert('Не вдалося оновити статус');
    }
  });

  function applyStatus(pill, status){
    const info = STATUS_META[status] || {label: status, class: 'badge px-2 bg-label-secondary text-capitalized'};
    pill.className = info.class + ' status-pill';
    pill.textContent = info.label;
    pill.setAttribute('data-status', status);
  }
</script>
@endpush
