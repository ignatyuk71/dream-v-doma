<template>
    <main class="content-wrapper">
      <div class="container-lg">
       <!-- Хлібні крихти -->
       <Breadcrumbs :items="breadcrumbs" />
  
      <!-- Page title -->
      <h1 class="h3 container mb-4">Shop catalog</h1>
  
      <!-- Desktop banners (≥768px) -->
      <section class="container pb-4 pb-md-5 mb-xl-3 d-none d-md-block">
        <div class="row g-3 g-lg-4">
          <!-- iPhone banner -->
          <div class="col-md-7">
            <div
              class="position-relative d-flex flex-column flex-sm-row align-items-center h-100 rounded-5 overflow-hidden px-5 px-sm-0 pe-sm-4"
            >
              <span
                class="position-absolute top-0 start-0 w-100 h-100 d-none-dark rtl-flip"
                style="background: linear-gradient(90deg, #accbee 0%, #e7f0fd 100%)"
              ></span>
              <div
                class="position-relative z-1 text-center text-sm-start pt-4 pt-sm-0 ps-xl-4 mt-2 mt-sm-0 order-sm-2"
              >
                <h2 class="h3 mb-2">iPhone 14</h2>
                <p class="fs-sm text-light-emphasis mb-sm-4">
                  Apple iPhone 14 128GB Blue
                </p>
                <a class="btn btn-primary" href="shop-product-general-electronics.html">
                  From $899
                  <i class="ci-arrow-up-right fs-base ms-1 me-n1"></i>
                </a>
              </div>
              <div
                class="position-relative z-1 w-100 align-self-end order-sm-1"
                style="max-width: 416px"
              >
                <div class="ratio rtl-flip" style="--cz-aspect-ratio: calc(320 / 416 * 100%)">
                  <img src="/public/assets/img/shop/electronics/banners/iphone-1.png" alt="iPhone 14" />
                </div>
              </div>
            </div>
          </div>
          <!-- iPad banner -->
          <div class="col-md-5">
            <div
              class="position-relative d-flex flex-column align-items-center justify-content-between h-100 rounded-5 overflow-hidden pt-3"
            >
              <span
                class="position-absolute top-0 start-0 w-100 h-100 d-none-dark rtl-flip"
                style="background: linear-gradient(90deg, #fdcbf1 0%, #fdcbf1 1%, #ffecfa 100%)"
              ></span>
              <div class="position-relative z-1 text-center pt-3 mx-4">
                <i class="ci-apple text-body-emphasis fs-3 mb-3"></i>
                <p class="fs-sm text-light-emphasis mb-1">
                  Deal of the week
                </p>
                <h2 class="h3 mb-4">iPad Pro M1</h2>
              </div>
              <a class="position-relative z-1 d-block w-100" href="shop-product-general-electronics.html">
                <div class="ratio" style="--cz-aspect-ratio: calc(159 / 525 * 100%)">
                  <img src="/public/assets/img/shop/electronics/banners/ipad.png" width="525" alt="iPad" />
                </div>
              </a>
            </div>
          </div>
        </div>
      </section>
  
      <!-- Mobile banner -->
      <section class="container pb-4 d-block d-md-none">
        <div class="position-relative d-flex flex-column align-items-center h-100 rounded-5 overflow-hidden px-4">
          <span
            class="position-absolute top-0 start-0 w-100 h-100"
            style="background: linear-gradient(90deg, #accbee 0%, #e7f0fd 100%)"
          ></span>
          <div class="position-relative z-1 text-center pt-4">
            <h2 class="h4 mb-2">iPhone 14</h2>
          </div>
          <div class="position-relative z-1 w-100" style="max-width: 280px">
            <div class="ratio" style="--cz-aspect-ratio: calc(320 / 416 * 100%)">
              <img src="/public/assets/img/shop/electronics/banners/iphone-1.png" alt="iPhone 14" />
            </div>
          </div>
        </div>
      </section>
  
      <!-- Selected filters -->
      <section class="container mb-4">
        <div class="row">
          <div class="col-12">
            <div class="d-md-flex align-items-start">
              <div class="h6 fs-sm fw-normal text-nowrap translate-middle-y mt-3 mb-0 me-4">
                Знайдено <span class="fw-semibold">732</span> товарів
              </div>
              <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-secondary">
                  <i class="ci-close fs-sm ms-n1 me-1"></i>Категорія: Тапки
                </button>
                <button type="button" class="btn btn-sm btn-secondary">
                  <i class="ci-close fs-sm ms-n1 me-1"></i>Розмір: 39
                </button>
                <button type="button" class="btn btn-sm btn-secondary">
                  <i class="ci-close fs-sm ms-n1 me-1"></i>Колір: Червоний
                </button>
                <button type="button" class="btn btn-sm btn-secondary">
                  <i class="ci-close fs-sm ms-n1 me-1"></i>Ціна: 300–500 грн
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-secondary bg-transparent border-0 text-danger text-decoration-underline px-0 ms-2"
                >
                  Очистити всі
                </button>
              </div>
            </div>
          </div>
        </div>
        <hr class="d-lg-none my-3" />
      </section>
  
      <!-- Main grid + sidebar -->
      <section class="container pb-5 mb-sm-2 mb-md-3 mb-lg-4 mb-xl-5">
        <div class="row">
          <!-- Sidebar -->
          <aside class="col-lg-2 col-md-3">
            <div class="offcanvas-lg offcanvas-start" id="filterSidebar">
              <div class="offcanvas-header py-3">
                <h5 class="offcanvas-title">Filter and sort</h5>
                <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="offcanvas"
                  data-bs-target="#filterSidebar"
                  aria-label="Close"
                ></button>
              </div>
              <div class="offcanvas-body flex-column pt-2 py-lg-0">
                <!-- Price range -->
                <div class="w-100 border rounded p-3 p-xl-4 mb-3 mb-xl-4">
                <h4 class="h6 mb-3">Ціна</h4>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <input
                    type="number"
                    class="form-control"
                    placeholder="від"
                    min="0"
                    v-model="priceMin"
                    >
                    <span>–</span>
                    <input
                    type="number"
                    class="form-control"
                    placeholder="до"
                    min="0"
                    v-model="priceMax"
                    >
                </div>

                <input
                    type="range"
                    class="form-range"
                    :min="priceRangeMin"
                    :max="priceRangeMax"
                    :step="1"
                    v-model="priceSlider"
                    @input="syncPriceInputs"
                />
                <div class="d-flex justify-content-between fs-sm text-body-secondary">
                    <span>{{ priceRangeMin }} грн</span>
                    <span>{{ priceRangeMax }} грн</span>
                </div>
                </div>


  
                <!-- Categories -->
                <div class="w-100 border rounded p-3 p-xl-4 mb-3 mb-xl-4">
                  <h4 class="h6">Категорії</h4>
                  <div class="d-flex flex-column gap-1">
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="home" checked />
                        <label for="home" class="form-check-label text-body-emphasis">
                          Домашні тапки
                        </label>
                      </div>
                    </div>
                    <!-- Repeat for other categories -->
                  </div>
                </div>
  
                <!-- Sizes -->
                <div class="w-100 border rounded p-3 p-xl-4 mb-3 mb-xl-4">
                  <h4 class="h6">Розмір</h4>
                  <div class="d-flex flex-column gap-1">
                    <!-- Size items -->
                    <div class="d-flex align-items-center justify-content-between">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="size-36" />
                        <label for="size-36" class="form-check-label text-body-emphasis">36</label>
                      </div>
                      <span class="text-body-secondary fs-xs">13</span>
                    </div>
                    <!-- Repeat for sizes 37–41 -->
                  </div>
                </div>
  
                <!-- Colors -->
                <div class="w-100 border rounded p-3 p-xl-4">
                  <h4 class="h6">Колір</h4>
                  <div class="nav d-block mt-n2">
                    <!-- Color buttons -->
                    <button
                      type="button"
                      class="nav-link w-auto animate-underline fw-normal pt-2 pb-0 px-0"
                    >
                      <span
                        class="rounded-circle me-2"
                        style="width:.875rem;height:.875rem;margin-top:.125rem;background-color:#8bc4ab"
                      ></span>
                      <span class="animate-target">Зелений</span>
                    </button>
                    <!-- Repeat for other colors -->
                  </div>
                </div>
              </div>
            </div>
          </aside>
  
          <!-- Product grid -->
          <div class="col-lg-10">
        <!-- Product grid -->

  <div v-if="category && category.products" class="row row-cols-2 row-cols-md-3 g-4 pb-3 mb-3">
    <div class="col" v-for="(product, index) in category.products" :key="product.id">
      <div class="product-card animate-underline hover-effect-opacity bg-body rounded">
        <div class="position-relative">
          <div class="position-absolute top-0 end-0 z-2 hover-effect-target opacity-0 mt-3 me-3">
            <div class="d-flex flex-column gap-2">
              <button type="button" class="btn btn-icon btn-secondary animate-pulse d-none d-lg-inline-flex" aria-label="Add to Wishlist">
                <i class="ci-heart fs-base animate-target"></i>
              </button>
              <button type="button" class="btn btn-icon btn-secondary animate-rotate d-none d-lg-inline-flex" aria-label="Compare">
                <i class="ci-refresh-cw fs-base animate-target"></i>
              </button>
            </div>
          </div>
          <div class="dropdown d-lg-none position-absolute top-0 end-0 z-2 mt-2 me-2">
            <button type="button" class="btn btn-icon btn-sm btn-secondary bg-body" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
              <i class="ci-more-vertical fs-lg"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end fs-xs p-2" style="min-width:auto">
              <li><a class="dropdown-item" href="#!"><i class="ci-heart fs-sm ms-n1 me-2"></i>Add to Wishlist</a></li>
              <li><a class="dropdown-item" href="#!"><i class="ci-refresh-cw fs-sm ms-n1 me-2"></i>Compare</a></li>
            </ul>
          </div>
          <a class="d-block rounded-top overflow-hidden position-relative" :href="`/${$i18n.locale}/product/${product.translations.find(t => t.locale === $i18n.locale)?.slug}`">
            <span class="badge bg-danger position-absolute top-0 start-0 z-2 mt-2 ms-2 mt-lg-3 ms-lg-3">-21%</span>
            <div class="ratio ratio-1x1 overflow-hidden">
              <img :src="product.images[0]?.full_url" class="object-fit-cover w-100 h-100" :alt="product.translations.find(t => t.locale === $i18n.locale)?.name" />
            </div>
          </a>
        </div>
        <div class="w-100 min-w-0 px-1 pb-2 px-sm-3 pb-sm-3">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="d-flex gap-1 fs-xs">
              <i class="ci-star-filled text-warning"></i>
              <i class="ci-star-filled text-warning"></i>
              <i class="ci-star-filled text-warning"></i>
              <i class="ci-star-filled text-warning"></i>
              <i class="ci-star text-body-tertiary opacity-75"></i>
            </div>
            <span class="text-body-tertiary fs-xs">(123)</span>
          </div>
          <h3 class="pb-1 mb-2">
            <a class="d-block fs-sm fw-medium text-truncate" :href="`/${$i18n.locale}/product/${product.translations.find(t => t.locale === $i18n.locale)?.slug}`">
              <span class="animate-target">{{ product.translations.find(t => t.locale === $i18n.locale)?.name }}</span>
            </a>
          </h3>
          <div class="d-flex align-items-center justify-content-between">
            <div class="h5 lh-1 mb-0">{{ Number(product.price).toFixed(2) }} грн</div>
            <button type="button" class="product-card-button btn btn-icon btn-secondary animate-slide-end ms-2" aria-label="Add to Cart">
              <i class="ci-shopping-cart fs-base animate-target"></i>
            </button>
          </div>
        </div>
        <div class="product-card-details position-absolute top-100 start-0 w-100 bg-body rounded-bottom shadow mt-n2 p-3 pt-1">
          <span class="position-absolute top-0 start-0 w-100 bg-body mt-n2 py-2"></span>
          <ul class="list-unstyled d-flex flex-column gap-2 m-0">
            <li class="d-flex align-items-center">
              <span class="fs-xs">Display:</span>
              <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
              <span class="text-dark-emphasis fs-xs fw-medium text-end">OLED 1440x1600</span>
            </li>
            <li class="d-flex align-items-center">
              <span class="fs-xs">Graphics:</span>
              <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
              <span class="text-dark-emphasis fs-xs fw-medium text-end">Adreno 540</span>
            </li>
            <li class="d-flex align-items-center">
              <span class="fs-xs">Sound:</span>
              <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
              <span class="text-dark-emphasis fs-xs fw-medium text-end">2x3.5mm jack</span>
            </li>
            <li class="d-flex align-items-center">
              <span class="fs-xs">Input:</span>
              <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
              <span class="text-dark-emphasis fs-xs fw-medium text-end">4 built-in cameras</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
      <div class="position-relative rounded-5 overflow-hidden mb-4">
        <span class="position-absolute top-0 start-0 w-100 h-100 d-none-dark rtl-flip" style="background: linear-gradient(-90deg, #accbee 0%, #e7f0fd 100%)"></span>
        <span class="position-absolute top-0 start-0 w-100 h-100 d-none d-block-dark rtl-flip" style="background: linear-gradient(-90deg, #1b273a 0%, #1f2632 100%)"></span>
        <div class="row align-items-center position-relative z-1">
          <div class="col-md-6 pt-5 pt-md-0 mb-2 mb-md-0">
            <div class="text-center text-md-start py-md-5 px-4 ps-md-5 pe-md-0 me-md-n5">
              <h3 class="text-uppercase fw-bold ps-xxl-3 pb-2 mb-1">Seasonal weekly sale 2024</h3>
              <p class="text-body-emphasis ps-xxl-3 mb-0">Use code <span class="d-inline-block fw-semibold text-dark bg-white rounded-pill py-1 px-2">Sale 2024</span> to get best offer</p>
            </div>
          </div>
          <div class="col-md-6 d-flex justify-content-center justify-content-md-end">
            <div class="me-3 me-lg-4 me-xxl-5">
              <img src="/public/assets/img/shop/electronics/banners/iphone-2.png" class="d-block rtl-flip" width="335" alt="Banner" />
            </div>
          </div>
        </div>
      </div>
    </div>

  
            <!-- Pagination -->
            <nav class="border-top mt-4 pt-3" aria-label="Catalog pagination">
              <ul class="pagination pagination-lg pt-2 pt-md-3">
                <li class="page-item disabled me-auto">
                  <a class="page-link d-flex align-items-center h-100 fs-lg px-2" href="#!" aria-label="Previous page">
                    <i class="ci-chevron-left mx-1"></i>
                  </a>
                </li>
                <li class="page-item active" aria-current="page">
                  <span class="page-link">1<span class="visually-hidden">(current)</span></span>
                </li>
                <li class="page-item"><a class="page-link" href="#!">2</a></li>
                <li class="page-item"><a class="page-link" href="#!">3</a></li>
                <li class="page-item"><span class="page-link pe-none">...</span></li>
                <li class="page-item"><a class="page-link" href="#!">16</a></li>
                <li class="page-item ms-auto">
                  <a class="page-link d-flex align-items-center h-100 fs-lg px-2" href="#!" aria-label="Next page">
                    <i class="ci-chevron-right mx-1"></i>
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </section>
    </div>
      
  
      <section class="bg-body-tertiary py-5">
        <div class="container pt-sm-2 pt-md-3 pt-lg-4 pt-xl-5">
          <div class="row">
            <div class="col-md-6 col-lg-5 mb-5 mb-md-0">
              <h2 class="h4 mb-2">Sign up to our newsletter</h2>
              <p class="text-body pb-2 pb-ms-3">Receive our latest updates about our products &amp; promotions</p>
              <form class="d-flex needs-validation pb-1 pb-sm-2 pb-md-3 pb-lg-0 mb-4 mb-lg-5" novalidate>
                <div class="position-relative w-100 me-2">
                  <input type="email" class="form-control form-control-lg" placeholder="Your email" required>
                </div>
                <button type="submit" class="btn btn-lg btn-primary">Subscribe</button>
              </form>
              <div class="d-flex gap-3">
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Instagram">
                  <i class="ci-instagram fs-base"></i>
                </a>
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Facebook">
                  <i class="ci-facebook fs-base"></i>
                </a>
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="YouTube">
                  <i class="ci-youtube fs-base"></i>
                </a>
                <a class="btn btn-icon btn-secondary rounded-circle" href="#!" aria-label="Telegram">
                  <i class="ci-telegram fs-base"></i>
                </a>
              </div>
            </div>
            <div class="col-md-6 col-lg-5 col-xl-4 offset-lg-1 offset-xl-2">
              <ul class="list-unstyled d-flex flex-column gap-4 ps-md-4 ps-lg-0 mb-3">
                <li class="nav flex-nowrap align-items-center position-relative">
                  <img src="/public/assets/img/home/electronics/vlog/01.jpg" class="rounded" width="140" alt="Video cover">
                  <div class="ps-3">
                    <div class="fs-xs text-body-secondary lh-sm mb-2">6:16</div>
                    <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">5 New Cool Gadgets You Must See on Cartzilla - Cheap Budget</a>
                  </div>
                </li>
                <li class="nav flex-nowrap align-items-center position-relative">
                  <img src="/public/assets/img/home/electronics/vlog/02.jpg" class="rounded" width="140" alt="Video cover">
                  <div class="ps-3">
                    <div class="fs-xs text-body-secondary lh-sm mb-2">10:20</div>
                    <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">5 Super Useful Gadgets on Cartzilla You Must Have in 2023</a>
                  </div>
                </li>
                <li class="nav flex-nowrap align-items-center position-relative">
                  <img src="/public/assets/img/home/electronics/vlog/03.jpg" class="rounded" width="140" alt="Video cover">
                  <div class="ps-3">
                    <div class="fs-xs text-body-secondary lh-sm mb-2">8:40</div>
                    <a class="nav-link fs-sm hover-effect-underline stretched-link p-0" href="#!">Top 5 New Amazing Gadgets on Cartzilla You Must See</a>
                  </div>
                </li>
              </ul>
              <div class="nav ps-md-4 ps-lg-0">
                <a class="btn nav-link animate-underline text-decoration-none px-0" href="#!">
                  <span class="animate-target">View all</span>
                  <i class="ci-chevron-right fs-base ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>


    <button
        type="button"
        class="fixed-bottom z-sticky w-100 btn btn-lg btn-dark border-0 border-top border-light border-opacity-10 rounded-0 pb-4 d-lg-none"
        data-bs-toggle="offcanvas"
        data-bs-target="#filterSidebar"
        aria-controls="filterSidebar"
        data-bs-theme="light"
        >
        <i class="ci-filter fs-base me-2"></i>
        Фільтри
    </button>



    </main>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue'

const { t, locale } = useI18n() // ← Ось тут ти отримуєш t

const category = ref(null)
const breadcrumbs = ref([])

onMounted(() => {
  const el = document.getElementById('category-page')
  if (el?.dataset.category) {
    try {
      category.value = JSON.parse(el.dataset.category)

      breadcrumbs.value = [
        { text: t('home'), href: `/${locale.value}` },
        {
          text: category.value.translations.find(t => t.locale === locale.value)?.name,
          active: true
        }
      ]
    } catch (e) {
      console.error('❌ Помилка розпарсування категорії', e)
    }
  }
})
  
  function addToCart(product) {
    console.log('➕ Додано в корзину:', product.id);
  }
  </script>






  <style scoped>
  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  
  input[type="number"] {
    -moz-appearance: textfield;
  }
  </style>