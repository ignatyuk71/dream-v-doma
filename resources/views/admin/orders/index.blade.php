@extends('admin.layouts.vuexy')

@section('title','Замовлення')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">

    @php
      $statusLabels = \App\Enums\OrderStatus::labels();
    @endphp

    {{-- Фільтри --}}
    <div class="card-header pb-0">
      <form method="get" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label mb-1">Пошук</label>
          <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                 placeholder="№ замовлення, ім’я, телефон, email">
        </div>
        <div class="col-md-2">
          <label class="form-label mb-1">Статус</label>
          <select name="status" class="form-select">
            <option value="">Будь-який</option>
            @foreach($statusLabels as $val => $label)
              <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label mb-1">Від дати</label>
          <input type="date" name="from" value="{{ request('from') }}" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label mb-1">До дати</label>
          <input type="date" name="to" value="{{ request('to') }}" class="form-control">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary flex-grow-1">Застосувати</button>
          <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Скинути</a>
        </div>
      </form>
      <hr class="mt-3 mb-0">
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover align-middle mb-0" id="ordersAccordion">
        <thead class="border-top">
          <tr>
            <th style="width:44px;"></th>   {{-- стрілка --}}
            <th>Номер і дата</th>
            <th>Товари</th>
            <th>Покупець</th>
            <th class="text-end">Сума</th>
            <th>Статус</th>
            <th style="width:200px;">Змінити статус</th> {{-- ⬅ listbox одразу після статусу --}}
            <th class="text-end">Дії</th>
          </tr>
        </thead>
        <tbody>

        @php
          $badge = fn($s) => match($s) {
            'pending'   => 'badge bg-label-warning',
            'confirmed' => 'badge bg-label-info',
            'packed'    => 'badge bg-label-secondary',
            'shipped'   => 'badge bg-label-primary',
            'delivered' => 'badge bg-label-success',
            'cancelled' => 'badge bg-label-dark',
            'returned'  => 'badge bg-label-danger',
            'refunded'  => 'badge bg-label-success',
            default     => 'badge bg-label-secondary',
          };
          $img = function($url) {
            if (!$url) return null;
            return (str_starts_with($url,'http://') || str_starts_with($url,'https://') || str_starts_with($url,'//'))
              ? $url
              : '/storage/' . ltrim($url,'/');
          };
        @endphp

        @foreach($orders as $o)
          @php
            $firstItem  = $o->items->first();
            $collapseId = 'ord-'.$o->id;
            $statusVal  = $o->status->value ?? (string)$o->status;
          @endphp

          {{-- Рядок-резюме --}}
          <tr class="order-summary">
            <td class="text-center">
              <button class="btn btn-sm btn-link p-0 toggle-row"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#{{ $collapseId }}"
                      aria-expanded="false"
                      aria-controls="{{ $collapseId }}">
                <i class="ti tabler-chevron-down rotate"></i>
              </button>
            </td>

            <td>
              <div class="fw-semibold">#{{ $o->order_number ?? $o->id }}</div>
              <div class="text-muted small">{{ $o->created_at?->format('d.m.Y, H:i') }}</div>
            </td>

            <td>
              <div class="d-flex align-items-center gap-2">
                @if($firstItem && $firstItem->image_url)
                  <img src="{{ $img($firstItem->image_url) }}" alt=""
                       width="54" height="54" style="object-fit:cover;border-radius:8px;">
                @endif
                <h6 class="mb-3">Товари: {{ $o->items->count() }}</h6>
              </div>
            </td>

            <td>
              @if($o->customer)
                <div>{{ $o->customer->name ?? '—' }}</div>
                @if(!empty($o->customer->phone))
                  <div class="text-muted small">{{ $o->customer->phone }}</div>
                @endif
              @else
                —
              @endif
            </td>

            <td class="text-end">
              {{ number_format((float)$o->total_price, 2, '.', ' ') }} {{ $o->currency ?? 'UAH' }}
            </td>

            <td>
              <span class="{{ $badge($statusVal) }}">{{ strtoupper(\App\Enums\OrderStatus::labels()[$statusVal] ?? $statusVal) }}</span>
            </td>

            {{-- ⬇️ список для швидкої зміни статусу цього замовлення --}}
            <td>
              <form method="POST" action="{{ route('admin.orders.update', $o) }}">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                  @foreach($statusLabels as $val => $label)
                    <option value="{{ $val }}" @selected($statusVal === $val)>{{ $label }}</option>
                  @endforeach
                </select>
                {{-- notes не обов'язкові, не відправляємо --}}
              </form>
            </td>

            <td class="text-end">
              <a href="{{ route('admin.orders.show',$o) }}" class="btn btn-sm btn-outline-primary">Відкрити</a>
            </td>
          </tr>

          {{-- Рядок-деталі — без “смужки” --}}
          <tr class="details-row">
            <td class="p-0 border-0"></td>
            <td colspan="7" class="p-0 border-0">
              <div id="{{ $collapseId }}" class="collapse fade-slide" data-bs-parent="#ordersAccordion">
                <div class="p-3 bg-light-subtle">
                  <div class="row g-3">

                    {{-- Про покупця --}}
                    <div class="col-lg-4">
                      <div class="card h-100">
                        <div class="card-body">
                          <h6 class="mb-3">Про покупця</h6>
                          <div class="mb-2">
                            <div class="text-muted small">Отримувач</div>
                            <div class="fw-semibold">{{ $o->customer->name ?? '—' }}</div>
                            @if(!empty($o->customer?->phone))
                              <div class="text-muted small">{{ $o->customer->phone }}</div>
                            @endif
                          </div>
                          <div class="mb-2">
                            <div class="text-muted small">Замовник</div>
                            <div class="fw-semibold">{{ $o->customer->name ?? '—' }}</div>
                            @if(!empty($o->customer?->phone))
                              <div class="text-muted small">{{ $o->customer->phone }}</div>
                            @endif
                          </div>
                          <div class="d-flex gap-2 mt-3">
                            <a class="btn btn-sm btn-outline-secondary" href="tel:{{ $o->customer->phone ?? '' }}">Подзвонити</a>
                            <a class="btn btn-sm btn-outline-secondary" href="mailto:{{ $o->customer->email ?? '' }}">Написати</a>
                          </div>
                        </div>
                      </div>
                    </div>

                    {{-- Адреса доставки --}}
                    @php $d = $o->delivery; @endphp
                    <div class="col-lg-4">
                      <div class="card h-100">
                        <div class="card-body">
                          <h6 class="mb-3">Адреса доставки</h6>
                          <div class="mb-2">
                            <div class="text-muted small">Служба</div>
                            <div class="fw-semibold">{{ $d->delivery_type ?? '—' }}</div>
                          </div>
                          @if($d?->np_description)
                            <div class="mb-2"><div class="text-muted small">Відділення</div><div>{{ $d->np_description }}</div></div>
                          @endif
                          @if($d?->np_address)
                            <div class="mb-2"><div class="text-muted small">Адреса НП</div><div>{{ $d->np_address }}</div></div>
                          @endif
                          @if($d?->courier_address)
                            <div class="mb-2"><div class="text-muted small">Адреса курʼєра</div><div>{{ $d->courier_address }}</div></div>
                          @endif

                          <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-sm btn-success" type="button">Створити ТТН</button>
                          </div>
                        </div>
                      </div>
                    </div>

                    {{-- Оплата / суми --}}
                    <div class="col-lg-4">
                      <div class="card h-100">
                        <div class="card-body">
                          <h6 class="mb-3">Оплата</h6>
                          <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Вартість товарів</span>
                            <span class="fw-semibold">{{ number_format((float)$o->total_price, 2, '.', ' ') }} {{ $o->currency ?? 'UAH' }}</span>
                          </div>
                          <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Вартість доставки</span>
                            <span class="fw-semibold">За тарифами перевізника</span>
                          </div>
                          <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Спосіб оплати</span>
                            <span class="fw-semibold">@if($statusVal==='pending') Післяплата @else Оплата/підтверджено @endif</span>
                          </div>
                          <button class="btn btn-sm btn-outline-primary" type="button">Надіслати реквізити</button>
                        </div>
                      </div>
                    </div>

                    {{-- Товари списком --}}
                    <div class="col-12">
                      <div class="card">
                        <div class="card-body">
                          <h6 class="mb-3">Товари: {{ $o->items->count() }}</h6>
                          @foreach($o->items as $it)
                            <div class="d-flex align-items-center justify-content-between py-2 border-top">
                              <div class="d-flex align-items-center gap-2">
                                @if($it->image_url)
                                  <img src="{{ $img($it->image_url) }}" alt=""
                                       width="44" height="44" style="object-fit:cover;border-radius:8px;">
                                @endif
                                <div>
                                  <div class="fw-semibold">{{ $it->product_name }}</div>
                                  <div class="text-muted small">{{ implode(' · ', array_filter([$it->variant_sku, $it->size, $it->color])) }}</div>
                                </div>
                              </div>
                              <div class="text-end" style="min-width:220px">
                                <div>{{ number_format((float)$it->price, 2, '.', ' ') }} {{ $o->currency ?? 'UAH' }} × {{ $it->quantity }}</div>
                                <div class="fw-semibold">{{ number_format((float)$it->total, 2, '.', ' ') }} {{ $o->currency ?? 'UAH' }}</div>
                              </div>
                            </div>
                          @endforeach
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </td>
          </tr>
        @endforeach

        </tbody>
      </table>
    </div>
  </div>

  <div class="pt-3">
    {{ $orders->onEachSide(1)->links('pagination::bootstrap-5') }}
  </div>
</div>
@endsection

@push('styles')
<style>
  /* Плавне розгортання */
  .collapse.fade-slide { transition: height .25s ease, opacity .25s ease; }
  .collapse.fade-slide:not(.show) { opacity: 0; }
  .collapse.fade-slide.show { opacity: 1; }

  /* Поворот стрілочки */
  .toggle-row .rotate { transition: transform .2s ease; }
  .toggle-row[aria-expanded="true"] .rotate { transform: rotate(180deg); }

  /* Hover на весь рядок */
  #ordersAccordion.table-hover > tbody > tr.order-summary > * {
    transition: background-color .15s ease, color .15s ease;
  }
  #ordersAccordion.table-hover > tbody > tr.order-summary:hover > * {
    background-color: #f2f2f5 !important;
    color: #1f2937 !important;
  }

  /* Прибрати смужку між рядками */
  #ordersAccordion tr.details-row > td { padding: 0 !important; border: 0 !important; }
</style>
@endpush

@push('scripts')
<script>
// Не розгортати при кліку по інтерактивних елементах
document.addEventListener('click', function (e) {
  const row = e.target.closest('tr.order-summary');
  if (!row) return;
  if (e.target.closest('a, button, select, input, label, textarea')) return;
  row.querySelector('.toggle-row')?.click();
});
</script>
@endpush

