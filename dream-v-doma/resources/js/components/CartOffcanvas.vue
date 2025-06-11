<template>
   
<!-- Shopping cart offcanvas -->
<div class="offcanvas offcanvas-end pb-sm-2 px-sm-2" id="shoppingCart" tabindex="-1" aria-labelledby="shoppingCartLabel" style="width: 500px">

<!-- Header -->
<div class="offcanvas-header flex-column align-items-start py-3 pt-lg-4">
  <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-lg-4">
    <h4 class="offcanvas-title" id="shoppingCartLabel">Shopping cart</h4>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <p class="fs-sm">Buy <span class="text-dark-emphasis fw-semibold">$53</span> more to get <span class="text-dark-emphasis fw-semibold">Free Shipping</span></p>
  <div class="progress w-100" role="progressbar" aria-label="Free shipping progress" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
    <div class="progress-bar bg-dark rounded-pill d-none-dark" style="width: 78%"></div>
    <div class="progress-bar bg-light rounded-pill d-none d-block-dark" style="width: 78%"></div>
  </div>
</div>

<!-- Items -->
<div class="offcanvas-body d-flex flex-column gap-4 pt-2">

  <!-- Item -->
  <div class="d-flex align-items-center">
    <a class="flex-shrink-0" href="shop-product-fashion.html">
      <img src="/public/assets/img/shop/fashion/thumbs/07.png" class="bg-body-tertiary rounded" width="110" alt="Thumbnail">
    </a>
    <div class="w-100 min-w-0 ps-3">
      <h5 class="d-flex animate-underline mb-2">
        <a class="d-block fs-sm fw-medium text-truncate animate-target" href="shop-product-fashion.html">Leather sneakers with golden laces</a>
      </h5>
      <div class="h6 pb-1 mb-2">$74.00</div>
      <div class="d-flex align-items-center justify-content-between">
        <div class="count-input rounded-2">
          <button type="button" class="btn btn-icon btn-sm" data-decrement aria-label="Decrement quantity">
            <i class="ci-minus"></i>
          </button>
          <input type="number" class="form-control form-control-sm" value="1" readonly>
          <button type="button" class="btn btn-icon btn-sm" data-increment aria-label="Increment quantity">
            <i class="ci-plus"></i>
          </button>
        </div>
        <button type="button" class="btn-close fs-sm" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" data-bs-title="Remove" aria-label="Remove from cart"></button>
      </div>
    </div>
  </div>

  <!-- Item -->
  <div class="d-flex align-items-center">
    <a class="flex-shrink-0" href="shop-product-fashion.html">
      <img src="/public/assets/img/shop/fashion/thumbs/08.png" class="bg-body-tertiary rounded" width="110" alt="Thumbnail">
    </a>
    <div class="w-100 min-w-0 ps-3">
      <h5 class="d-flex animate-underline mb-2">
        <a class="d-block fs-sm fw-medium text-truncate animate-target" href="shop-product-fashion.html">Classic cotton men's shirt</a>
      </h5>
      <div class="h6 pb-1 mb-2">$27.00</div>
      <div class="d-flex align-items-center justify-content-between">
        <div class="count-input rounded-2">
          <button type="button" class="btn btn-icon btn-sm" data-decrement aria-label="Decrement quantity">
            <i class="ci-minus"></i>
          </button>
          <input type="number" class="form-control form-control-sm" value="1" readonly>
          <button type="button" class="btn btn-icon btn-sm" data-increment aria-label="Increment quantity">
            <i class="ci-plus"></i>
          </button>
        </div>
        <button type="button" class="btn-close fs-sm" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" data-bs-title="Remove" aria-label="Remove from cart"></button>
      </div>
    </div>
  </div>

  <!-- Item -->
  <div class="d-flex align-items-center">
    <a class="flex-shrink-0" href="shop-product-fashion.html">
      <img src="/public/assets/img/shop/fashion/thumbs/09.png" class="bg-body-tertiary rounded" width="110" alt="Thumbnail">
    </a>
    <div class="w-100 min-w-0 ps-3">
      <h5 class="d-flex animate-underline mb-2">
        <a class="d-block fs-sm fw-medium text-truncate animate-target" href="shop-product-fashion.html">Polarized sunglasses for men</a>
      </h5>
      <div class="h6 pb-1 mb-2">$96.00 <del class="text-body-tertiary fs-xs fw-normal">112.00</del></div>
      <div class="d-flex align-items-center justify-content-between">
        <div class="count-input rounded-2">
          <button type="button" class="btn btn-icon btn-sm" data-decrement aria-label="Decrement quantity">
            <i class="ci-minus"></i>
          </button>
          <input type="number" class="form-control form-control-sm" value="1" readonly>
          <button type="button" class="btn btn-icon btn-sm" data-increment aria-label="Increment quantity">
            <i class="ci-plus"></i>
          </button>
        </div>
        <button type="button" class="btn-close fs-sm" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-sm" data-bs-title="Remove" aria-label="Remove from cart"></button>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<div class="offcanvas-header flex-column align-items-start">
  <div class="d-flex align-items-center justify-content-between w-100 mb-3 mb-md-4">
    <span class="text-light-emphasis">Subtotal:</span>
    <span class="h6 mb-0">$197.00</span>
  </div>
  <div class="d-flex w-100 gap-3">
    <a class="btn btn-lg btn-secondary w-100" href="#!">View cart</a>
    <a class="btn btn-lg btn-dark w-100" href="#!">Checkout</a>
  </div>
</div>
</div>


<!-- Search offcanvas -->
<div class="offcanvas offcanvas-top" id="searchBox" data-bs-backdrop="static" tabindex="-1">
<div class="offcanvas-header border-bottom p-0 py-lg-1">
  <form class="container d-flex align-items-center">
    <input type="search" class="form-control form-control-lg fs-lg border-0 rounded-0 py-3 ps-0" placeholder="Search the products" data-autofocus="offcanvas">
    <button type="reset" class="btn-close fs-lg" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </form>
</div>
<div class="offcanvas-body px-0">
  <div class="container text-center">
    <svg class="text-body-tertiary opacity-60 mb-4" xmlns="http://www.w3.org/2000/svg" width="60" viewBox="0 0 512 512" fill="currentColor"><path d="M340.115,361.412l-16.98-16.98c-34.237,29.36-78.733,47.098-127.371,47.098C87.647,391.529,0,303.883,0,195.765S87.647,0,195.765,0s195.765,87.647,195.765,195.765c0,48.638-17.738,93.134-47.097,127.371l16.98,16.98l11.94-11.94c5.881-5.881,15.415-5.881,21.296,0l112.941,112.941c5.881,5.881,5.881,15.416,0,21.296l-45.176,45.176c-5.881,5.881-15.415,5.881-21.296,0L328.176,394.648c-5.881-5.881-5.881-15.416,0-21.296L340.115,361.412z M195.765,361.412c91.484,0,165.647-74.163,165.647-165.647S287.249,30.118,195.765,30.118S30.118,104.28,30.118,195.765S104.28,361.412,195.765,361.412z M360.12,384l91.645,91.645l23.88-23.88L384,360.12L360.12,384z M233.034,233.033c5.881-5.881,15.415-5.881,21.296,0c5.881,5.881,5.881,15.416,0,21.296c-32.345,32.345-84.786,32.345-117.131,0c-5.881-5.881-5.881-15.415,0-21.296c5.881-5.881,15.416-5.881,21.296,0C179.079,253.616,212.45,253.616,233.034,233.033zM135.529,180.706c-12.475,0-22.588-10.113-22.588-22.588c0-12.475,10.113-22.588,22.588-22.588c12.475,0,22.588,10.113,22.588,22.588C158.118,170.593,148.005,180.706,135.529,180.706z M256,180.706c-12.475,0-22.588-10.113-22.588-22.588c0-12.475,10.113-22.588,22.588-22.588s22.588,10.113,22.588,22.588C278.588,170.593,268.475,180.706,256,180.706z"/></svg>
    <h6 class="mb-2">Your search results will appear here</h6>
    <p class="fs-sm mb-0">Start typing in the search field above to see instant search results.</p>
  </div>
</div>
</div>



  </template>
  
  <script setup>
  
  </script>
  