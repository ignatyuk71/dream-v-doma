<template>
    <teleport to="body">
      <transition name="glo-fade">
        <div v-if="active" class="glo-mask" role="dialog" aria-modal="true" :aria-label="label">
          <div class="glo-box" role="status" aria-live="polite">
            <div class="spinner-border text-light" aria-hidden="true"></div>
            <div class="mt-3 fw-semibold">{{ label }}</div>
            <div v-if="hint" class="text-white-50 small mt-1">{{ hint }}</div>
          </div>
        </div>
      </transition>
    </teleport>
  </template>
  
  <script setup>
  defineProps({
    active: Boolean,
    label: { type: String, default: 'Завантаження…' },
    hint:  { type: String, default: '' }
  })
  </script>
  
  <style scoped>
  .glo-mask { position: fixed; inset: 0; background: rgba(9,11,22,.55);
    backdrop-filter: saturate(180%) blur(2px); display: flex; align-items: center; justify-content: center;
    z-index: 2147483647; }
  .glo-box { min-width: 220px; padding: 20px 24px; background: rgba(28,32,44,.88); color:#fff;
    border-radius: 14px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
  .spinner-border { width: 2.5rem; height: 2.5rem; }
  .glo-fade-enter-active, .glo-fade-leave-active { transition: opacity .15s ease; }
  .glo-fade-enter-from, .glo-fade-leave-to { opacity: 0; }
  </style>
  