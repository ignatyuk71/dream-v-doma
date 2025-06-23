<template>
  <div class="sticky-product-banner-inner pt-5">
    <div class="navbar container flex-nowrap align-items-center bg-body pt-4 pt-lg-5 mt-lg-n2">
      <div class="d-flex align-items-center min-w-0 ms-lg-2 me-3">
        <div class="ratio ratio-1x1 flex-shrink-0" style="width: 50px">
          <img :src="product.images?.[0]?.full_url" :alt="product.translations?.[0]?.title ?? 'Product'" />
        </div>
        <h4 class="h6 fw-medium d-none d-lg-block ps-3 mb-0">
          {{ product.translations.find(t => t.locale === $i18n.locale)?.name || '—' }}
        </h4>
        <div class="w-100 min-w-0 d-lg-none ps-2">
          <h4 class="fs-sm fw-medium text-truncate mb-1">
            {{ product.translations?.[0]?.title ?? 'Назва товару' }}
          </h4>
          <div class="h6 mb-0">
            {{ formatPrice(product.price) }}
            <del class="fs-xs fw-normal text-body-tertiary" v-if="product.old_price">
              {{ formatPrice(product.old_price) }}
            </del>
          </div>
        </div>
      </div>

      <div class="h4 d-none d-lg-block mb-0 ms-auto me-4">
        {{ formatPrice(product.price) }}
        <del class="fs-sm fw-normal text-body-tertiary" v-if="product.old_price">
          {{ formatPrice(product.old_price) }}
        </del>
      </div>

      <div class="d-flex gap-2 flex-shrink-0">
        <a :href="`/${$i18n.locale}/checkout`" class="btn btn-dark">
          <i class="ci-check me-2"></i>
          {{ $t('checkout_cart') || 'Оформити замовлення' }}
        </a>
      </div>
    </div>
  </div>
</template>
  
  <script setup>
  import { useI18n } from 'vue-i18n'
  
  const props = defineProps({
    product: {
      type: Object,
      required: true,
    },
  })
  
  const { t } = useI18n()
  
  function formatPrice(value) {
    if (!value) return ''
    return `${parseFloat(value).toFixed(2)} ${t('currency')}`
  }
  </script>
  