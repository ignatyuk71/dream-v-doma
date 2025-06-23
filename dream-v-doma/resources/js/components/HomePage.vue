<template>
    <Topbar />
    <div class="container-lg">
        <Navbar />
    </div>
    <main class="content-wrapper">
       <HeroBanner />
    <div class="container-lg">
        <CategorySwiper />
        <FeaturedProducts />
        <SpecialOffers />
    
    <ProductCarousel
            :products="relatedProducts"
            title="Інші тапки"
            uid="related-products"
        />
     <!-- Instagram feed -->
     <section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
        <div class="text-center pt-1 pb-2 pb-md-3">
          <h2 class="pb-2 mb-1">
            <span class="animate-underline">
              <a class="animate-target text-dark-emphasis text-decoration-none" href="#!">#cartzilla</a>
            </span>
          </h2>
          <p>Find more inspiration on our Instagram</p>
        </div>
        <InstagramFeed />
      </section>
    </div>
    </main>
  
    <BackToTop />
    <Footer />
  </template>
  
  <script setup>
import { ref } from 'vue'

import InstagramFeed from '@/components/shared/InstagramFeed.vue'
import ProductCarousel from './shared/ProductCarousel.vue'
import SpecialOffers from './SpecialOffers.vue'
import CategorySwiper from './CategorySwiper.vue'
import FeaturedProducts from './FeaturedProducts.vue'
import Topbar from './Topbar.vue'
import Navbar from './Navbar.vue'
import HeroBanner from './HeroBanner.vue'
import CartOffcanvas from './cart/CartOffcanvas.vue'
import BackToTop from './BackToTop.vue'
import Footer from './Footer.vue'

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
  },
])
</script>

  