@extends('admin.layouts.vuexy')

@section('title','Замовлення — Список')

@section('content')
@php
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

  // Bootstrap/Vuexy бейджі для статусів
  $badge = fn($s) => match($s) {
    'pending'   => 'badge bg-label-warning text-bg-warning',
    'confirmed' => 'badge bg-label-info text-bg-info',
    'packed'    => 'badge bg-label-secondary text-bg-secondary',
    'shipped'   => 'badge bg-label-primary text-bg-primary',
    'delivered' => 'badge bg-label-success text-bg-success',
    'cancelled' => 'badge bg-label-dark text-bg-dark',
    'returned'  => 'badge bg-label-danger text-bg-danger',
    'refunded'  => 'badge bg-label-success text-bg-success',
    default     => 'badge bg-label-secondary text-bg-secondary',
  };

  // JSON для JS (optimistic UI)
  $statusMeta = [];
  foreach ($statusLabels as $val => $label) {
    $statusMeta[$val] = [
      'label' => strtoupper($label),
      'class' => $badge($val) . ' rounded-pill fw-bold',
    ];
  }
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
  <div id="tpl-orders">

    <!-- ===== Toolbar / KPIs ===== -->
    <div class="toolbar">
      <div class="t-chip">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
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

      <div class="tool" id="bulk-export">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Експорт
      </div>
      <div class="tool muted" id="bulk-print">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18v4h12v-4"/><rect x="6" y="12" width="12" height="8"/><path d="M20 12v-1a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v1"/></svg>
        Друк
      </div>
    </div>

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
              $statusLbl  = strtoupper($statusLabels[$statusVal] ?? $statusVal);
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
                  <svg class="picon" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                  {{ $o->payment_method ?? 'Спосіб оплати не вказано' }}
                </div>

                @if($o->delivery?->np_ref)
                  <div class="payline small">
                    <svg class="picon" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/></svg>
                    <b>ТТН:</b>
                    <a target="_blank" rel="noopener" href="https://tracking.novaposhta.ua/#/uk/search?cargo_number={{ $o->delivery->np_ref }}">{{ $o->delivery->np_ref }}</a>
                  </div>
                @endif
              </td>

              <td>
                <div class="d-flex align-items-center gap-2">
                  @if($firstItem?->image_url)
                    <img src="{{ $img($firstItem->image_url) }}" alt="" width="74" height="74" style="object-fit:cover;border-radius:10px;">
                  @else
                    <div class="thumb"></div>
                  @endif
                  <div class="small">
                    <div><b>Товарів:</b> {{ $qtySum }}</div>
                  </div>
                </div>
              </td>

              <td>
                <div class="buyer-name">{{ $o->customer->name ?? '—' }}</div>
                @if($o->customer?->phone)
                  <div class="small muted">
                    <a href="tel:{{ preg_replace('/\s+/', '', $o->customer->phone) }}">{{ $o->customer->phone }}</a>
                    <button class="btn btn-xs btn-link copy" data-copy="{{ $o->customer->phone }}">копіювати</button>
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

              <td>
                <!-- Оплата + зміна статусу -->
                <div class="d-flex flex-column gap-2">
                  <div class="dropdown status-dd">
                    <span class="{{ $badge($statusVal) }} rounded-pill fw-bold status-pill"
                          data-status-badge data-status="{{ $statusVal }}">{{ $statusLbl }}</span>

                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle mt-1 w-auto"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      Змінити статус
                    </button>

                    <ul class="dropdown-menu p-2">
                      @foreach($statusLabels as $val => $label)
                        <li>
                          <button type="button"
                                  class="dropdown-item d-flex align-items-center gap-2 status-change"
                                  data-url=""
                                  data-id="{{ $o->id }}"
                                  data-status="{{ $val }}">
                            <span class="{{ $badge($val) }} rounded-pill">{{ strtoupper($label) }}</span>
                          </button>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </td>

              <td class="text-end">
                <div class="actions">
                  <a class="a" href="{{ route('admin.orders.show',$o) }}" title="Відкрити">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M21 14v7h-7"/></svg>
                  </a>
                  <button class="a js-print" data-id="{{ $o->id }}" title="Друк">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18v4h12v-4"/><rect x="6" y="12" width="12" height="8"/><path d="M20 12v-1a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v1"/></svg>
                  </button>
                  <button class="a js-ttn" data-id="{{ $o->id }}" title="Створити ТТН">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h7"/></svg>
                  </button>
                  <button class="a js-remind" data-id="{{ $o->id }}" title="Нагадування">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M12 22a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2Z"/><path d="M8 8a4 4 0 1 1 8 0v3a7 7 0 0 0 2 5H6a7 7 0 0 0 2-5Z"/></svg>
                  </button>
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
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="2"/><path d="M12 5v3m0 8v3m7-7h-3M8 12H5"/></svg>
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3ZM19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7Z"/></svg>
                              </div>
                              <div class="d-flex align-items-center gap-2">
                                <a class="text-body text-decoration-none" href="tel:{{ $o->customer->phone ?? '' }}">
                                  {{ $o->customer->phone ?? '—' }}
                                </a>
                                <button type="button" class="btn p-0 border-0 bg-transparent" title="Копіювати телефон">
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
                                <button type="button" class="btn p-0 border-0 bg-transparent" title="Копіювати телефон">
                                  <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
                                </button>
                              </div>
                            </div>
                            <div class="mt-1 fw-semibold">{{ $o->customer->name ?? '—' }}</div>
                          </div>

                          <!-- Кількість покупок -->
                          <div class="py-3">
                            <div class="d-flex align-items-center gap-2">
                              <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm10 0a2 2 0 1 0 .001 4.001A2 2 0 0 0 17 18ZM6.2 4l-.3-2H1v2h3l2.4 12.2A2 2 0 0 0 8.4 18h8.9a2 2 0 0 0 2-1.6L21 8H6.6L6.2 4Zm12.6 6-1.2 6H8.4L7.3 10h11.5Z"/></svg>
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
                          <button type="button" class="btn p-0 border-0 bg-transparent" title="Копіювати">
                            <svg width="15" height="15" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
                          </button>
                        </div>

                        <div class="card-body">
                          <!-- Служба -->
                          <div class="py-3 border-bottom">
                            <div class="text-muted small">Служба</div>
                            <div class="mt-1 fw-semibold d-flex align-items-center gap-2">
                              @if(($o->delivery->delivery_type ?? '') === 'courier')
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 8h-3V4H3v10h1a3 3 0 1 0 6 0h4a3 3 0 1 0 6 0h1v-4l-2-2Zm-3 6H10a3 3 0 0 0-5.83 0H5V6h10v3h3l2 2v3h-.17A3 3 0 0 0 17 14Z"/></svg>
                                <span>курєр</span>
                              @elseif(($o->delivery->delivery_type ?? '') === 'branch')
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18v14H3V5Zm2 2в2l7 4 7-4V7H5Z"/></svg>
                                <span>на пошту</span>
                              @elseif(($o->delivery->delivery_type ?? '') === 'postomat')
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18v16H3V4Zm2 2v12h14V6H5Zm2 2h4v3H7V8Zm5 0h5v3h-5V8Zm-5 4h4v3H7v-3Zm5 0h5v3h-5v-3Z"/></svg>
                                <span>поштомат</span>
                              @else
                                <span>{{ $o->delivery->delivery_type ?? '—' }}</span>
                              @endif
                            </div>
                          </div>

                          @if($o->delivery?->np_description)
                            <div class="py-3 border-bottom">
                              <div class="text-muted small">Відділення</div>
                              <div class="mt-1">{{ $o->delivery->np_description }}</div>
                            </div>
                          @endif

                          <div class="py-3 border-bottom">
                            <div class="text-muted small">Адреса</div>
                            <div class="mt-1">{{ $o->delivery->np_address ?? $o->delivery->courier_address ?? '—' }}</div>
                          </div>

                          @if($o->delivery?->np_ref)
                            <div class="py-3 border-bottom">
                              <div class="text-muted small">ТТН</div>
                              <div class="mt-1 fw-semibold d-flex align-items-center gap-2">
                                <span>{{ $o->delivery->np_ref }}</span>
                                <button type="button" class="btn p-0 border-0 bg-transparent" title="Копіювати ТТН">
                                  <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></svg>
                                </button>
                              </div>
                            </div>
                          @endif

                          <div class="pt-3">
                            <div class="d-flex align-items-center gap-2">
                              <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 7H3v8a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V7Zm-4 7H7v-2h10v2Zm0-4H7V8h10v2Z"/></svg>
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
                                  <circle cx="12" cy="12" r="2"/><path d="M12 5v3m0 8v3m7-7h-3M8 12H5"/>
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
                                <img src="{{ $img($it->image_url) }}" alt=""
                                     class="rounded-2 border" style="width:60px;height:60px;object-fit:cover;">
                              @else
                                <div class="rounded-2 border bg-light" style="width:60px;height:60px;"></div>
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
  #tpl-orders{ --bg:#f5f6fa; --card:#fff; --muted:#6b7280; --text:#2f3342; --border:#e6e8ef; --hover:#f3f4f6; }
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
</style>
@endpush

@push('scripts')
<script>
  const CSRF = '{{ csrf_token() }}';
  const STATUS_META = @json($statusMeta);

  // Клік по всьому рядку (крім інтерактивних елементів) — відкриває/закриває деталі
  document.addEventListener('click', function (e) {
    const row = e.target.closest('tr.order-row');
    if (!row) return;
    if (e.target.closest('a, button, select, input, label, .dropdown-menu')) return;
    row.querySelector('.toggle-row')?.click();
  });

  // Копіювання телефона
  document.querySelectorAll('#tpl-orders .copy').forEach(btn=>{
    btn.addEventListener('click', (e)=>{
      e.preventDefault();
      const text = btn.dataset.copy || '';
      if (!text) return;
      navigator.clipboard?.writeText(text).then(()=>{
        btn.textContent = 'скопійовано';
        setTimeout(()=>btn.textContent='копіювати', 1200);
      });
    });
  });

  // Зміна статусу з dropdown (optimistic UI + PATCH)
  document.querySelectorAll('#tpl-orders .status-change').forEach(item=>{
    item.addEventListener('click', async ()=>{
      const status = item.dataset.status;
      const url    = item.dataset.url;
      const cell   = item.closest('td');
      const pill   = cell.querySelector('[data-status-badge]');

      // optimistic UI
      applyStatus(pill, status);

      try{
        const form = new FormData();
        form.append('_token', CSRF);
        form.append('_method','PATCH');
        form.append('status', status);
        const res = await fetch(url, { method:'POST', body: form });
        if(!res.ok) throw new Error(await res.text());
      }catch(err){
        // відкат якщо помилка
        const prev = pill.getAttribute('data-status');
        applyStatus(pill, prev);
        alert('Не вдалося оновити статус. Перевір, чи є маршрут admin.orders.updateStatus');
      }
    });
  });

  function applyStatus(pill, status){
    const meta = STATUS_META[status] || {label: status.toUpperCase(), class: 'badge bg-secondary rounded-pill fw-bold'};
    pill.className = meta.class + ' status-pill';
    pill.textContent = meta.label;
    pill.setAttribute('data-status', status);
  }

  // плейсхолдери для дій
  document.querySelectorAll('#tpl-orders .js-print').forEach(b=>b.addEventListener('click',()=>alert('Друк накладної — підключи маршрут')));
  document.querySelectorAll('#tpl-orders .js-ttn').forEach(b=>b.addEventListener('click',()=>alert('Створення ТТН — підключи інтеграцію')));
  document.querySelectorAll('#tpl-orders .js-remind').forEach(b=>b.addEventListener('click',()=>alert('Надіслати нагадування — підключи логіку')));
</script>
@endpush
