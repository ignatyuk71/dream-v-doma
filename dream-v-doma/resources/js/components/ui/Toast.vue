<template>
  <transition name="toast-fade">
    <div
      v-if="visible"
      class="toast show shadow border"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
      style="position: fixed; top: 1rem; right: 1rem; z-index: 1060; min-width: 420px; border-radius: 0.75rem; overflow: hidden; background-color: #fff;"
    >
      <!-- Заголовок -->
      <div :class="headerClass" class="d-flex justify-content-between align-items-center px-3 py-2">
        <strong class="me-auto">{{ title }}</strong>
        <button type="button" class="btn-close" @click="hideToast" aria-label="Закрити"></button>
      </div>

      <!-- Тіло -->
      <div class="toast-body px-3 py-2 text-dark">
        {{ message }}
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'
import { message, type, visible, hideToast } from '../../helpers/toast.js'

const bsType = computed(() => {
  const map = {
    success: 'success',
    error: 'danger',
    danger: 'danger',
    warning: 'warning',
    info: 'info',
  }
  return map[type.value] || 'info'
})

const title = computed(() => {
  const map = {
    success: '✅ Успішно',
    danger: '❌ Помилка',
    warning: '⚠️ Увага',
    info: 'ℹ️ Інформація',
  }
  return map[bsType.value] || '🔔'
})

const headerClass = computed(() => `toast-${bsType.value}`)
</script>

<style scoped>
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.toast-success {
  background-color: #d1e7dd;
  color: #0f5132;
  font-weight: bold;
}

.toast-danger {
  background-color: #f8d7da;
  color: #842029;
  font-weight: bold;
}

.toast-warning {
  background-color: #fff3cd;
  color: #664d03;
  font-weight: bold;
}

.toast-info {
  background-color: #cff4fc;
  color: #055160;
  font-weight: bold;
}
</style>
