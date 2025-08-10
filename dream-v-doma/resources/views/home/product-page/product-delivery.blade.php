@php
    // Очікувана доставка
    $deliveryFrom = now()->addDays(1)->translatedFormat('d M');
    $deliveryTo = now()->addDays(2)->translatedFormat('d M');

    // Залишок
    $stock = $product->quantity_in_stock;
    $stockProgress = min(100, round(($stock / 20) * 100)); // умовна шкала: 20 шт = 100%
@endphp

<!-- Інформація про доставку і наявність -->
<ul class="list-unstyled gap-3 pb-3 pb-lg-1 mb-0">
  <li class="d-flex flex-wrap fs-sm mb-1">
    <span class="fw-medium text-dark-emphasis me-2">
      <i class="ci-clock fs-base me-2"></i>
      {{ __('product.expect_order_between') }}
    </span>
    <span> {{ $deliveryFrom }} - {{ $deliveryTo }}</span>
  </li>
  <li class="d-flex flex-wrap fs-sm">
    <span class="fw-medium text-dark-emphasis me-2">
      <i class="ci-delivery fs-base me-2"></i>
      {{ __('product.free_shipping_note') }}
    </span>
    <span>{{ __('product.free_shipping_limit') }}</span>
  </li>
</ul>


<div id="stock-progress" data-product='@json($product)'></div>

  <div class="accordion" id="infoAccordion">
    <!-- 🚚 Доставка -->
    <div class="accordion-item border-top">
  <h3 class="accordion-header" id="headingShipping">
    <button type="button" class="accordion-button collapsed animate-underline" data-bs-toggle="collapse" data-bs-target="#shipping" aria-expanded="true" aria-controls="shipping">
    <span class="animate-target me-2" style="display: flex; align-items: center;">
        Доставка
      </span>
    </button>
  </h3>
  <div class="accordion-collapse collapse show" id="shipping" aria-labelledby="headingShipping" data-bs-parent="#infoAccordion">
    <div class="accordion-body">

      <table class="table table-sm text-muted small mb-2" style="max-width: 540px;">
        <thead>
          <tr>
            <th>Спосіб доставки</th>
            <th>Термін</th>
            <th>Вартість</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <strong>Нова Пошта (відділення/поштомат)</strong>
              <br>
              Отримуйте у зручному відділенні по всій Україні
            </td>
            <td>1–2 робочі дні</td>
            <td>від 95&nbsp;грн</td>
          </tr>
          <tr>
            <td>
              <strong>Нова Пошта (кур'єр)</strong>
              <br>
              Доставка прямо до ваших дверей
            </td>
            <td>1–2 робочі дні</td>
            <td>від 120&nbsp;грн</td>
          </tr>
        </tbody>
      </table>

      <ul class="small text-muted mb-2 ps-3">
        <li>Вартість доставки розраховується за тарифами перевізника і залежить від ваги та габаритів посилки.</li>
        <li>Після оформлення замовлення ви отримаєте SMS або Viber з номером накладної для відстеження.</li>
      </ul>
    </div>
  </div>
</div>



    <!-- 💳 Оплата -->
    <div class="accordion-item">
      <h3 class="accordion-header" id="headingPayment">
        <button type="button" class="accordion-button collapsed animate-underline" data-bs-toggle="collapse" data-bs-target="#payment" aria-expanded="false" aria-controls="payment">
          <span class="animate-target me-2">Оплата</span>
        </button>
      </h3>
      <div class="accordion-collapse collapse" id="payment" aria-labelledby="headingPayment" data-bs-parent="#infoAccordion">
        <div class="accordion-body">
          <ul class="list-unstyled text-muted small mb-0">
            <li>• <strong>Банківська картка</strong> — оплата онлайн через сервіс LiqPay, Monobank або Приват24.</li>
            <li>• <strong>Готівка</strong> — при отриманні на відділенні Нової Пошти (накладений платіж, + комісія пошти).</li>
            <li>• <strong>Безготівковий розрахунок</strong> — для юр. осіб та ФОП, видаємо рахунок-фактуру.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- 🔄 Обмін і повернення -->
  <div class="accordion-item">
    <h3 class="accordion-header" id="headingReturn">
      <button type="button" class="accordion-button collapsed animate-underline" data-bs-toggle="collapse" data-bs-target="#return" aria-expanded="false" aria-controls="return">
        <span class="animate-target me-2"> Обмін та повернення</span>
      </button>
    </h3>
    <div class="accordion-collapse collapse" id="return" aria-labelledby="headingReturn" data-bs-parent="#infoAccordion">
      <div class="accordion-body">
        <div class="alert d-flex align-items-center alert-info mb-3" role="alert" style="background: #f3f7fd; border-radius: 16px;">
          <i class="bi bi-arrow-left-right fs-3 text-primary me-3"></i>
          <div>
            <strong>Обмін/повернення:</strong> протягом 14 днів згідно з законодавством України.<br>
            Кошти повертаємо на картку протягом 1–3 робочих днів.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
