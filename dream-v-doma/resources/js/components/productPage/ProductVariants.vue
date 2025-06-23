<template>
    <div>
      <!-- Вибір кольору -->
      <div class="mb-1">
        <label class="form-label fw-semibold pb-1 mb-1">
          {{ $t('productcolor') }}:
          <span class="text-body fw-normal">{{ selectedColorLabel }}</span>
        </label>
        <div class="d-flex flex-wrap gap-2">
          <template v-for="(color, index) in colors" :key="index">
            <input
              type="radio"
              class="btn-check"
              :id="`color-${index}`"
              name="colors"
              :value="color.label"
              v-model="selectedColor"
            />
            <label :for="`color-${index}`" class="btn btn-image p-0" :data-label="color.label">
              <img :src="color.image" width="56" :alt="color.label" />
              <span class="visually-hidden">{{ color.label }}</span>
            </label>
          </template>
        </div>
      </div>
  
      <!-- Вибір розміру -->
      <div class="mb-2">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <label class="form-label fw-semibold mb-0">{{ $t('productsize') }}</label>
          <div class="nav">
            <a class="nav-link animate-underline fw-normal px-0" href="#sizeGuide" data-bs-toggle="modal">
              <i class="ci-ruler fs-lg me-2"></i>
              <span class="animate-target">{{ $t('productsize_guide') }}</span>
            </a>
          </div>
        </div>
        <select
          class="form-select form-select-lg"
          v-model="selectedSize"
          aria-label="Select size"
        >
          <option value="">{{ $t('productchoose_size') }}</option>
          <option v-for="(size, i) in sizes" :key="i" :value="size">
            {{ size }}
          </option>
        </select>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, watch } from 'vue'
  
  const props = defineProps({
    product: Object
  })
  
  const colors = ref([
    { label: 'Темно-синій', image: '/assets/img/shop/fashion/product/colors/color01.png' },
    { label: 'Рожевий', image: '/assets/img/shop/fashion/product/colors/color02.png' },
    { label: 'Блакитний', image: '/assets/img/shop/fashion/product/colors/color03.png' }
  ])
  
  const sizes = ref([
    '6-8 (XS)',
    '8-10 (S)',
    '10-12 (M)',
    '12-14 (L)',
    '14-16 (XL)'
  ])
  
  const selectedColor = ref(colors.value[0].label)
  const selectedSize = ref('')
  
  const selectedColorLabel = computed(() => selectedColor.value || '—')
  
  // watch([selectedColor, selectedSize], () => {
  //   emit('update:variant', { color: selectedColor.value, size: selectedSize.value })
  // })
  </script>
  