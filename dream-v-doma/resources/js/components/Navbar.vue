<template>
  <header class="navbar navbar-expand-lg navbar-sticky bg-body d-block z-fixed p-0" data-sticky-navbar='{"offset": 500}'>
    <div class="container py-1 py-lg-3">
      <div class="d-flex align-items-center gap-3">
        <button type="button" class="navbar-toggler me-4 me-md-2" data-bs-toggle="offcanvas" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="Перемкнути навігацію">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>

      <a class="navbar-brand py-1 py-md-2 py-xl-1" href="index.html">
        <span class="d-none d-sm-flex flex-shrink-0 text-primary me-2">
          <!-- logo svg -->
        </span>
        DREAM V DOMA
      </a>

      <div class="d-flex align-items-center">
        <button type="button" class="navbar-toggler d-none navbar-stuck-show me-3" data-bs-toggle="collapse" data-bs-target="#stuckNav" aria-controls="stuckNav" aria-expanded="false" aria-label="Перемкнути навігацію">
          <span class="navbar-toggler-icon"></span>
        </button>

        <a class="btn btn-icon btn-lg fs-lg btn-outline-secondary border-0 rounded-circle animate-shake d-none d-md-inline-flex" href="account-signin.html">
          <i class="ci-user animate-target"></i>
          <span class="visually-hidden">Акаунт</span>
        </a>

        <a class="btn btn-icon btn-lg fs-lg btn-outline-secondary border-0 rounded-circle animate-pulse d-none d-md-inline-flex" href="#!">
          <i class="ci-heart animate-target"></i>
          <span class="visually-hidden">Улюблене</span>
        </a>

        <button type="button" class="btn btn-icon btn-lg fs-xl btn-outline-secondary position-relative border-0 rounded-circle animate-scale" data-bs-toggle="offcanvas" data-bs-target="#shoppingCart" aria-controls="shoppingCart" aria-label="Кошик">
          <span v-if="cart.itemCount > 0" class="position-absolute top-0 start-100 badge fs-xs text-bg-primary rounded-pill mt-1 ms-n4 z-2" style="--cz-badge-padding-y: .25em; --cz-badge-padding-x: .42em">
            {{ cart.itemCount }}
          </span>
          <i class="ci-shopping-bag animate-target me-1"></i>
        </button>
      </div>
    </div>

    <div class="collapse navbar-stuck-hide" id="stuckNav">
      <nav class="offcanvas offcanvas-start" id="navbarNav" tabindex="-1" aria-labelledby="navbarNavLabel">
        <div class="offcanvas-header py-3">
          <h5 class="offcanvas-title" id="navbarNavLabel">Навігація</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрити"></button>
        </div>

        <div class="offcanvas-header gap-3 d-md-none pt-0 pb-3">
          <div class="dropdown nav">
            <language-switcher />
          </div>
        </div>

        <div class="offcanvas-body pt-1 pb-3 py-lg-0">
          <div class="container pb-lg-2 px-0 px-lg-3">
            <div class="position-relative d-lg-flex align-items-center justify-content-between">
              <div class="dropdown d-none d-md-block nav">
                <language-switcher />
              </div>

              <ul class="navbar-nav position-relative me-xl-n5">
                <li v-for="category in categories" :key="category.id" class="nav-item dropdown position-relative pb-lg-2 me-lg-n1 me-xl-0">
                  <a class="nav-link dropdown-toggle"
                     v-if="category.children.length"
                     :href="`/${locale}/category/${category.slug}`"
                     role="button"
                     data-bs-toggle="dropdown"
                     data-bs-trigger="hover"
                     aria-expanded="false">
                    {{ category.name }}
                  </a>
                  <a v-else class="nav-link" :href="`/${locale}/category/${category.slug}`">
                    {{ category.name }}
                  </a>

                  <div class="dropdown-menu start-0 p-4" style="--cz-dropdown-spacer: .75rem">
                    <div class="d-flex flex-column flex-lg-row gap-4">
                      <div style="min-width: 190px">
                        <div class="h6 mb-2">{{ category.name }}</div>
                        <ul class="nav flex-column gap-2 mt-0">
                          <li v-for="child in category.children" :key="child.id" class="d-flex w-100 pt-1">
                            <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0"
                               :href="`/${locale}/category/${child.slug}`">
                              {{ child.name }}
                            </a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>

              <button type="button" class="btn btn-outline-secondary justify-content-start w-100 px-3 mb-lg-2 ms-3 d-none d-lg-inline-flex" style="max-width: 240px" data-bs-toggle="offcanvas" data-bs-target="#searchBox" aria-controls="searchBox">
                <i class="ci-search fs-base ms-n1 me-2"></i>
                <span class="text-body-tertiary fw-normal">Шукати</span>
              </button>
            </div>
          </div>
        </div>

        <div class="offcanvas-header border-top px-0 py-3 mt-3 d-md-none">
          <div class="nav nav-justified w-100">
            <a class="nav-link border-end" href="account-signin.html">
              <i class="ci-user fs-lg opacity-60 me-2"></i>Акаунт
            </a>
            <a class="nav-link" href="#!">
              <i class="ci-heart fs-lg opacity-60 me-2"></i>Улюблене
            </a>
          </div>
        </div>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.nav-link {
  font-family: 'Inter', sans-serif;
  font-weight: 500;
}
@media (max-width: 576px) {
  .navbar-brand {
    font-size: 1.1rem !important;
  }
  .navbar .container,
  .container.py-1.py-lg-3 {
    padding-left: 8px !important;
    padding-right: 8px !important;
  }
}
</style>

<script setup>
import { useCartStore } from '@/stores/cart'
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const cart = useCartStore()
const { locale } = useI18n()
const categories = ref([])

onMounted(async () => {
  try {
    const response = await fetch(`/${locale.value}/api/categories`)
    if (!response.ok) throw new Error('Failed to fetch categories')
    categories.value = await response.json()
  } catch (error) {
    console.error('Помилка при завантаженні категорій:', error)
  }
})
</script>
