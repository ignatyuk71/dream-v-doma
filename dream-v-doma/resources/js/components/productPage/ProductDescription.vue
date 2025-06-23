<template>
    <section class="container pt-1 mt-2 mt-sm-3 mt-lg-4 mt-xl-1">
      <ul class="nav nav-underline flex-nowrap border-bottom" role="tablist">
        <li class="nav-item me-md-1" role="presentation">
          <button type="button" class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description-tab-pane" role="tab" aria-controls="description-tab-pane" aria-selected="true">
            {{ $t('productdescription') }}
          </button>
        </li>
        <li class="nav-item me-md-1" role="presentation">
          <button type="button" class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery-tab-pane" role="tab" aria-controls="delivery-tab-pane" aria-selected="false">
            {{ $t('productdelivery_and_returns') }}<span class="d-none d-md-inline"></span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button type="button" class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" role="tab" aria-controls="reviews-tab-pane" aria-selected="false">
            {{ $t('productreviews') }}<span class="d-none d-md-inline">&nbsp;(23)</span>
          </button>
        </li>
      </ul>
  
      <div class="tab-content pt-4 mt-sm-1 mt-md-3">
  
        <!-- 🟡 Description tab -->
        <div class="tab-pane fade show active fs-sm" id="description-tab-pane" role="tabpanel" aria-labelledby="description-tab">
          <div v-html="description"></div>
        </div>
  
        <!-- 🟢 Delivery tab (залишаємо як є) -->
        <div class="tab-pane fade fs-sm" id="delivery-tab-pane" role="tabpanel" aria-labelledby="delivery-tab">
          <div class="row row-cols-1 row-cols-md-2">
            <div class="col mb-3 mb-md-0">
              <div class="pe-lg-2 pe-xl-3">
                <h6>Доставка</h6>
                <p>Ми прагнемо доставити ваше замовлення якомога швидше. Орієнтовні терміни доставки:</p>
                <ul class="list-unstyled">
                  <li>Стандартна доставка: <span class="text-dark-emphasis fw-semibold">3–7 робочих днів</span></li>
                  <li>Експрес-доставка: <span class="text-dark-emphasis fw-semibold">1–3 робочі дні</span></li>
                </ul>
                <p>Зверніть увагу: час доставки може змінюватись залежно від регіону, свят або акцій. Після відправлення ви отримаєте номер для відстеження посилки.</p>
              </div>
            </div>
            <div class="col">
              <div class="ps-lg-2 ps-xl-3">
                <h6>Повернення</h6>
                <p>Ми хочемо, щоб ви були повністю задоволені покупкою. Якщо з будь-якої причини ви не задоволені замовленням, ви можете повернути його протягом 14 днів з моменту отримання.</p>
                <p>Товар має бути новим, не ношеним, у повній комплектації, з бірками та в оригінальній упаковці.</p>
                <p class="mb-0">Щоб оформити повернення, зв’яжіться з нашою підтримкою, вказавши номер замовлення та причину повернення. Ми надамо вам етикетку для відправлення та інструкції. <strong>Вартість доставки не повертається.</strong></p>
              </div>
            </div>
          </div>
        </div>
  
        <!-- 🔵 Reviews tab -->
        <div class="tab-pane fade" id="reviews-tab-pane" role="tabpanel" aria-labelledby="reviews-tab">
          <!-- блок відгуків -->
        </div>
      </div>
    </section>
  </template>
  
  <script setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'
  
  const props = defineProps({
    product: {
      type: Object,
      required: true
    }
  })
  
  const { locale } = useI18n()
  
  const description = computed(() => {
    const found = props.product?.translations?.find(t => t.locale === locale.value)
    return found?.description || '<p class="text-muted">Опис недоступний</p>'
  })
  
  // Debug
  console.log('🧾 PRODUCT:', props.product)
  console.log('🌐 LOCALE:', locale.value)
  console.log('📚 TRANSLATIONS:', props.product.translations)
  </script>
  