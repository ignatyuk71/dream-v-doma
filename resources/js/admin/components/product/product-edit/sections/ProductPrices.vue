<template>
  <div class="card mb-4 p-4">
    <div class="mb-3">
      <label class="form-label">Ціна</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.price }"
        v-model="form.price"
        placeholder="Ціна"
      />
      <div v-if="errors?.price" class="invalid-feedback">
        {{ errors.price }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Стара ціна</label>
      <input type="text" class="form-control" v-model="form.old_price" />
    </div>

    <div class="mb-3">
      <label class="form-label">Артикул (SKU)</label>
      <input
        type="text"
        class="form-control"
        :class="{ 'is-invalid': errors?.sku }"
        v-model="form.sku"
      />
      <div v-if="errors?.sku" class="invalid-feedback">
        {{ errors.sku }}
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Штрихкод</label>
      <input type="text" class="form-control" v-model="form.barcode" />
    </div>

    <div class="mb-3">
      <label class="form-label">Кількість</label>
      <input
        type="number"
        class="form-control"
        :class="{ 'is-invalid': errors?.quantity_in_stock }"
        v-model="form.quantity_in_stock"
      />
      <div v-if="errors?.quantity_in_stock" class="invalid-feedback">
        {{ errors.quantity_in_stock }}
      </div>
    </div>

    <div class="form-check form-switch mb-2">
      <input
        class="form-check-input"
        type="checkbox"
        id="stockSwitch"
        v-model="form.status"
      />
      <label class="form-check-label" for="stockSwitch">Є в наявності</label>
    </div>

    <div class="form-check form-switch">
      <input
        class="form-check-input"
        type="checkbox"
        id="popularSwitch"
        v-model="form.is_popular"
      />
      <label class="form-check-label" for="popularSwitch">Популярне</label>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['clear-error'])

function clearError(field) {
  emit('clear-error', field)
}
</script>
