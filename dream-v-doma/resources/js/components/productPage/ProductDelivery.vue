<template>
    <!-- Інформація про доставку і наявність -->
    <ul class="list-unstyled gap-3 pb-3 pb-lg-4 mb-3">
      <li class="d-flex flex-wrap fs-sm mb-1">
        <span class="fw-medium text-dark-emphasis me-2">
          <i class="ci-clock fs-base me-2"></i>
          Очікувана доставка:
        </span>
        <span v-if="deliveryFrom && deliveryTo">{{ deliveryFrom }} – {{ deliveryTo }}</span>
        <span v-else>–</span>
      </li>
      <li class="d-flex flex-wrap fs-sm">
        <span class="fw-medium text-dark-emphasis me-2">
          <i class="ci-delivery fs-base me-2"></i>
          Безкоштовна доставка та повернення:
        </span>
        <span>Для всіх замовлень від 1000 грн</span>
      </li>
    </ul>
  
    <!-- Статус залишків -->
    <div class="d-flex flex-wrap justify-content-between fs-sm mb-3">
      <span class="fw-medium text-dark-emphasis me-2">🔥 Поспішайте! Акція закінчується</span>
      <span><span class="fw-medium text-dark-emphasis">6</span> шт. в наявності</span>
    </div>
    <div class="progress" role="progressbar" aria-label="Залишок на складі" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
      <div class="progress-bar rounded-pill bg-danger" style="width: 25%"></div>
    </div>
  
    <!-- 🚚 ОПЛАТА І ДОСТАВКА -->
    <div class="pt-4">
      <!-- Варіант: ФОП / картка -->
      <div class="border-bottom pb-4 mb-4">
        <div class="row gx-3 align-items-start">
          <div class="col-auto fs-lg text-center">📦</div>
          <div class="col">
            <div class="fw-semibold mb-1">Нова Пошта (ФОП / картка)</div>
            <ul class="list-unstyled text-muted small mb-0">
              <li>• Оплата на рахунок ФОП або банківську картку.</li>
              <li>• Вартість доставки: <strong>80 грн</strong> по місту, <strong>105 грн</strong> по смт/селах.</li>
              <li>• Остаточна ціна залежить від адреси доставки.</li>
            </ul>
          </div>
          <div class="col-auto text-end text-muted small text-nowrap">1–2 дні</div>
        </div>
      </div>
  
      <!-- Варіант: Накладений платіж -->
      <div class="row gx-3 align-items-start">
        <div class="col-auto fs-lg text-center">💰</div>
        <div class="col">
          <div class="fw-semibold mb-1">Накладений платіж (оплата при отриманні)</div>
          <ul class="list-unstyled text-muted small mb-0">
            <li>• Доставка: <strong>80 грн</strong> по місту, <strong>105 грн</strong> по смт/селах.</li>
            <li>• Комісія за накладений платіж: приблизно <strong>25 грн</strong>.</li>
            <li>• Загальна сума залежить від тарифів «Нової Пошти».</li>
          </ul>
        </div>
        <div class="col-auto text-end text-muted small text-nowrap">1–2 дні</div>
      </div>
    </div>
  </template>
  
  
  <script setup>
  import { ref, onMounted } from 'vue'
  
  const deliveryFrom = ref(null)
  const deliveryTo = ref(null)
  
  function formatDate(date) {
    return new Intl.DateTimeFormat('uk-UA', {
      day: 'numeric',
      month: 'long'
    }).format(date)
  }
  
  onMounted(() => {
    const today = new Date()
    const from = new Date(today)
    const to = new Date(today)
    to.setDate(to.getDate() + 3)
  
    deliveryFrom.value = formatDate(from)
    deliveryTo.value = formatDate(to)
  })
  </script>
  
  