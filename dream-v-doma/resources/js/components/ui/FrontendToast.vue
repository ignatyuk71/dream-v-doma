<template>
    <transition name="fade">
        <div
  v-if="visible"
  class="toast show shadow border"
  role="alert"
  aria-live="assertive"
  aria-atomic="true"
  style="position: fixed; top: 1rem; right: 1rem; z-index: 1060; min-width: 420px; border-radius: 0.75rem; overflow: hidden; background-color: #fff;"
>  
        <div class="alert alert-success shadow rounded-pill d-flex align-items-center px-4 py-2 gap-2">
          <i class="ci-check fs-lg"></i>
          <span>{{ message }}</span>
        </div>
      </div>
    </transition>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  const visible = ref(false)
  const message = ref('')
  let timeout = null
  
  function show(msg) {
    message.value = msg
    visible.value = true
    clearTimeout(timeout)
    timeout = setTimeout(() => visible.value = false, 2000)
  }
  
  defineExpose({ show })
  </script>
  
  <style scoped>
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
  }
  .fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
  }
  @media (max-width: 768px) {
  .toast {
    left: 0 !important;
    right: 0 !important;
    top: 50px !important;
    border-radius: 0 !important;
    margin: 0 auto;
    width: 100% !important;
    max-width: 100% !important;
  }
}
  </style>
  