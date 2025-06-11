<template>
    <a class="nav-link dropdown-toggle py-1 px-0" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Country select">
      <div class="ratio ratio-1x1" style="width: 20px">
        <img :src="currentFlag" :alt="currentLang.toUpperCase()" />
      </div>
    </a>
    <ul class="dropdown-menu fs-sm" style="--cz-dropdown-spacer: .5rem">
      <li>
        <a class="dropdown-item" href="#" @click.prevent="changeLang('ua')">
          <img src="/public//assets/img/flags/en-uk.png" class="flex-shrink-0 me-2" width="20" alt="Українська">
          Українська
        </a>
      </li>
      <li>
        <a class="dropdown-item" href="#" @click.prevent="changeLang('ru')">
          <img src="/public//assets/img/flags/fr.png" class="flex-shrink-0 me-2" width="20" alt="Русский">
          Русский
        </a>
      </li>
    </ul>
  </template>
  
  <script setup>
  import { useI18n } from 'vue-i18n'
  import { computed } from 'vue'
  
  const { locale } = useI18n()
  
  const currentLang = computed(() => locale.value)
  
  const currentFlag = computed(() => {
    return currentLang.value === 'ru'
      ? '/assets/img/flags/fr.png'
      : '/assets/img/flags/en-uk.png'
  })
  
  function changeLang(lang) {
    locale.value = lang
    localStorage.setItem('lang', lang)
    location.reload() // перезавантажити для коректної зміни в усіх компонентах
  }
  </script>
  