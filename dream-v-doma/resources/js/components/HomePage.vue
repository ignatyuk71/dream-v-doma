<template>
      <CartOffcanvas />
      <Topbar />
      <Navbar />

    <main class="content-wrapper">
        
        <HeroBanner />
        <CategorySwiper />
        <FeaturedProducts />
        <SpecialOffers />
    </main>
    <BackToTop />
    <Footer />
   
  </template>
  
  <script setup>
  import SpecialOffers from './SpecialOffers.vue'
  import CategorySwiper from './CategorySwiper.vue'
  import FeaturedProducts from './FeaturedProducts.vue'
  import Topbar from './Topbar.vue'
  import Navbar from './Navbar.vue'
  import HeroBanner from './HeroBanner.vue'
  import CartOffcanvas from './CartOffcanvas.vue'
  import BackToTop from './BackToTop.vue'
  import Footer from './Footer.vue'
  </script>