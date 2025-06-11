<template>
    <section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">
      <h2 class="text-center pb-2 pb-sm-3">This week's highlights</h2>
  
      <!-- Nav pills -->
      <div class="row g-0 overflow-x-auto pb-2 pb-sm-3 mb-3">
        <div class="col-auto pb-1 pb-sm-0 mx-auto">
          <ul class="nav nav-pills flex-nowrap text-nowrap">
            <li class="nav-item">
              <a class="nav-link active" href="#">Best sellers</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">New arrivals</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Sale items</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">Top rated</a>
            </li>
          </ul>
        </div>
      </div>
  
      <!-- Products grid -->
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gy-4 gy-md-5">
        <div class="col" v-for="n in 12" :key="n">
          <div class="animate-underline hover-effect-opacity">
            <div class="position-relative mb-3">
              <span class="badge text-bg-danger position-absolute top-0 start-0 z-2 mt-2 mt-sm-3 ms-2 ms-sm-3" v-if="n === 1">Sale</span>
              <button type="button" class="btn btn-icon btn-secondary animate-pulse fs-base bg-transparent border-0 position-absolute top-0 end-0 z-2 mt-1 mt-sm-2 me-1 me-sm-2" aria-label="Add to Wishlist">
                <i class="ci-heart animate-target"></i>
              </button>
              <a class="d-flex bg-body-tertiary rounded p-3" href="/product">
                <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
                  <img :src="`/assets/img/shop/fashion/0${n}.png`" alt="Product Image">
                </div>
              </a>
              <div class="hover-effect-target position-absolute start-0 bottom-0 w-100 z-2 opacity-0 pb-2 pb-sm-3 px-2 px-sm-3">
                <div class="d-flex align-items-center justify-content-center gap-2 gap-xl-3 bg-body rounded-2 p-2">
                  <span class="fs-xs fw-medium text-secondary-emphasis py-1 px-sm-2">S</span>
                  <span class="fs-xs fw-medium text-secondary-emphasis py-1 px-sm-2">M</span>
                  <span class="fs-xs fw-medium text-secondary-emphasis py-1 px-sm-2">L</span>
                </div>
              </div>
            </div>
            <div class="nav mb-2">
              <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="/product">
                <span class="text-truncate">Example product name</span>
              </a>
            </div>
            <div class="h6 mb-2">$99.00 <del class="fs-sm fw-normal text-body-tertiary">$120.00</del></div>
            <div class="position-relative">
              <div class="hover-effect-target fs-xs text-body-secondary opacity-100">+1 color</div>
              <div class="hover-effect-target d-flex gap-2 position-absolute top-0 start-0 opacity-0">
                <input type="radio" class="btn-check" :id="`color-${n}-1`" checked>
                <label :for="`color-${n}-1`" class="btn btn-color fs-base" style="color: #284971">
                  <span class="visually-hidden">Color</span>
                </label>
                <input type="radio" class="btn-check" :id="`color-${n}-2`">
                <label :for="`color-${n}-2`" class="btn btn-color fs-base" style="color: #8b9bc4">
                  <span class="visually-hidden">Color</span>
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script setup>
  // Dummy loop for now. Later you can replace with props/products API.
  </script>
  