<template>
    <main class="content-wrapper">
      <div class="modal fade" id="sizeGuide" tabindex="-1" aria-labelledby="sizeGuideLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0">
                <div class="modal-header">
                    <h5 class="modal-title" id="sizeGuideLabel">{{ $t('productsize_guide') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                    <table class="table table-bordered text-center mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>EU</th>
                            <th>US</th>
                            <th>UK</th>
                            <th>(см)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td>36</td><td>5.5</td><td>3.5</td><td>23.0</td></tr>
                        <tr><td>37</td><td>6.5</td><td>4.5</td><td>23.7</td></tr>
                        <tr><td>38</td><td>7.5</td><td>5.5</td><td>24.4</td></tr>
                        <tr><td>39</td><td>8.5</td><td>6.5</td><td>25.0</td></tr>
                        <tr><td>40</td><td>9</td><td>7</td><td>25.7</td></tr>
                        <tr><td>41</td><td>10</td><td>8</td><td>26.4</td></tr>
                        </tbody>
                    </table>
                    </div>
                </div>
                </div>
            </div>
      </div>
      <!-- Breadcrumb -->
      <nav class="container pt-2 pt-xxl-3 my-3 my-md-4" aria-label="breadcrumb">
        <ProductBreadcrumbs :product="product" :current-locale="locale" />
      </nav>
  
      <!-- Галерея + Інфо -->
      <section class="container">
        <div class="row">
          <div class="col-md-6">
            <ProductGallery :product="product" />
          </div>
          <div class="col-md-6">
            <ProductInfo :product="product" @added="onAdded" />
          </div>
        </div>
      </section>
  
      <!-- Sticky Bar -->
      <section class="sticky-product-banner sticky-top" data-sticky-element>
        <StickyProductBar :product="product" />
      </section>
  
      <!-- Tabs -->
      <ProductDescription :product="product" />
      <!-- Інші товари -->
      <ProductCarousel
        :products="relatedProducts"
        title="Інші тапки"
        uid="related-products"
      />
  
      <!-- Instagram feed -->
      <section class="container pt-2 mt-2 mt-sm-3 mt-lg-1 mt-xl-1">
        <InstagramFeed />
      </section>
    
      <!-- ТОСТ повідомлення -->
      <FrontendToast ref="toastRef" />
    </main>
  </template>
  
  <script>
  import { ref } from 'vue'
  import ProductDescription from './ProductDescription.vue'
  import InstagramFeed from '@/components/shared/InstagramFeed.vue'
  import ProductGallery from './ProductGallery.vue'
  import ProductInfo from './ProductInfo.vue'
  import ProductBreadcrumbs from './ProductBreadcrumbs.vue'
  import StickyProductBar from './StickyProductBar.vue'
  import ProductCarousel from '../shared/ProductCarousel.vue'
  import FrontendToast from '@/components/ui/FrontendToast.vue'
  
  export default {
    components: {
      ProductGallery,
      ProductInfo,
      ProductBreadcrumbs,
      StickyProductBar,
      ProductCarousel,
      InstagramFeed,
      ProductDescription,
      FrontendToast
    },
    setup() {
        const el = document.getElementById('product-page')
        const product = ref(JSON.parse(el.dataset.product))
        const locale = ref(el.dataset.locale)
        const toastRef = ref(null)

        const relatedProducts = ref([
            {
            name: 'Тапки з хутром',
            slug: 'tapky-z-khutrom',
            price: '450 грн',
            old_price: '520 грн',
            image: '/assets/img/shop/fashion/11.png'
            },
            {
            name: 'Домашні тапки чорні',
            slug: 'home-black',
            price: '399 грн',
            image: '/assets/img/shop/fashion/10.png'
            }
        ])

        function onAdded(name) {
            toastRef.value?.show(`«${name}» додано в кошик 🛒`)
        }

        return {
            product,
            locale,
            relatedProducts,
            toastRef,
            onAdded // ← ❗️ОЦЕ ДОДАЙ
        }
        }

  }
  </script>
  