<template>
 <!-- Page footer -->
 <footer class="footer pt-5 pb-4">
      <div class="container pt-sm-2 pt-md-3 pt-lg-4">
        <div class="row pb-5 mb-lg-3">

          <!-- Columns with links that are turned into accordion on screens < 500px wide (sm breakpoint) -->
          <div class="col-md-8 col-xl-7 pb-2 pb-md-0 mb-4 mb-md-0 mt-n3 mt-sm-0">
            <div class="accordion" id="footerLinks">
              <div class="row row-cols-1 row-cols-sm-3">
                
                <div class="accordion-item col border-0">
                  <h6 class="accordion-header" id="categoriesHeading">
                    <span class="text-dark-emphasis d-none d-sm-block">Categories</span>
                    <button type="button" class="accord1ion-button  py-3 d-sm-none">Categories</button>
                  </h6>
                  <div class="accordion-collapse d-sm-block" id="categoriesLinks" aria-labelledby="categoriesHeading" data-bs-parent="#footerLinks">
                    <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">For women</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">For men</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Home clothes</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Accessories</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Sale</a>
                      </li>
                    </ul>
                  </div>
                  <hr class="d-sm-none my-0">
                </div>



                <div class="accordion-item col border-0">
                  <h6 class="accordion-header" id="accountHeading">
                    <span class="text-dark-emphasis d-none d-sm-block">Account</span>
                    <button type="button" class=" py-3 d-sm-none" >Account</button>
                  </h6>
                  <div class="accordion-collapse d-sm-block" >
                    <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Your account</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Shipping &amp; policies</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Refunds &amp; replacements</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Order tracking</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Delivery info</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Taxes &amp; fees</a>
                      </li>
                    </ul>
                  </div>
                  <hr class="d-sm-none my-0">
                </div>
                <div class="accordion-item col border-0">
                  <h6 class="accordion-header" id="customerHeading">
                    <span class="text-dark-emphasis d-none d-sm-block">Customer service</span>
                    <button type="button" class="py-3 d-sm-none">Customer service</button>
                  </h6>
                  <div class="accordion-collapse d-sm-block">
                    <ul class="nav flex-column gap-2 pt-sm-3 pb-3 pb-sm-0 mt-n1 mb-1 mb-sm-0">
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Payment methods</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Money back guarantee</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Refunds &amp; replacements</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Product returns</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Support center</a>
                      </li>
                      <li class="d-flex w-100 pt-1">
                        <a class="nav-link animate-underline animate-target d-inline fw-normal text-truncate p-0" href="#!">Shipping</a>
                      </li>
                    </ul>
                  </div>
                  <hr class="d-sm-none my-0">
                </div>
              </div>
            </div>
          </div>

          <!-- Subscription -->
          <div class="col-md-4 offset-xl-1">
            <h6 class="mb-4">Join us and stay up to date</h6>
            <form class="needs-validation" novalidate>
              <div class="form-check form-check-inline">
                <input type="checkbox" class="form-check-input" id="check-woman" checked>
                <label for="check-woman" class="form-check-label">Woman</label>
              </div>
              <div class="form-check form-check-inline">
                <input type="checkbox" class="form-check-input" id="check-man">
                <label for="check-man" class="form-check-label">Man</label>
              </div>
              <div class="form-check form-check-inline">
                <input type="checkbox" class="form-check-input" id="check-sale">
                <label for="check-sale" class="form-check-label">Sale</label>
              </div>
              <div class="position-relative mt-3">
                <input type="email" class="form-control form-control-lg bg-image-none text-start" placeholder="Enter email" aria-label="Your email address" required>
                <div class="invalid-tooltip bg-transparent p-0">Please enter you email address!</div>
                <button type="submit" class="btn btn-icon btn-ghost fs-xl btn-secondary border-0 position-absolute top-0 end-0 mt-1 me-1" aria-label="Submit your email address">
                  <i class="ci-arrow-up-right"></i>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Social account links -->
        <div class="d-flex justify-content-center justify-content-lg-start gap-2 mt-n2 mt-md-0">
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="YouTube" aria-label="Follow us on YouTube">
            <i class="ci-youtube"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Facebook" aria-label="Follow us on Facebook">
            <i class="ci-facebook"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Instagram" aria-label="Follow us on Instagram">
            <i class="ci-instagram"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Telegram" aria-label="Follow us on Telegram">
            <i class="ci-telegram"></i>
          </a>
          <a class="btn btn-icon fs-base btn-outline-secondary border-0" href="#!" data-bs-toggle="tooltip" data-bs-template='<div class="tooltip fs-xs mb-n2" role="tooltip"><div class="tooltip-inner bg-transparent text-body p-0"></div></div>' title="Pinterest" aria-label="Follow us on Pinterest">
            <i class="ci-pinterest"></i>
          </a>
        </div>

        <!-- Copyright + Payment methods -->
        <div class="d-lg-flex align-items-center border-top pt-4 mt-3">
          <div class="d-flex gap-2 gap-sm-3 justify-content-center ms-lg-auto mb-3 mb-md-4 mb-lg-0 order-lg-2">
            <div>
              <img src="/public/assets/img/payment-methods/visa-light-mode.svg" class="d-none-dark" alt="Visa">
              <img src="/public/assets/img/payment-methods/visa-dark-mode.svg" class="d-none d-block-dark" alt="Visa">
            </div>
            <div>
              <img src="/public/assets/img/payment-methods/paypal-light-mode.svg" class="d-none-dark" alt="PayPal">
              <img src="/public/assets/img/payment-methods/paypal-dark-mode.svg" class="d-none d-block-dark" alt="PayPal">
            </div>
            <div>
              <img src="/public/assets/img/payment-methods/mastercard.svg" alt="Mastercard">
            </div>
            <div>
              <img src="/public/assets/img/payment-methods/google-pay-light-mode.svg" class="d-none-dark" alt="Google Pay">
              <img src="/public/assets/img/payment-methods/google-pay-dark-mode.svg" class="d-none d-block-dark" alt="Google Pay">
            </div>
            <div>
              <img src="/public/assets/img/payment-methods/apple-pay-light-mode.svg" class="d-none-dark" alt="Apple Pay">
              <img src="/public/assets/img/payment-methods/apple-pay-dark-mode.svg" class="d-none d-block-dark" alt="Apple Pay">
            </div>
          </div>
          <div class="d-md-flex justify-content-center order-lg-1">
            <ul class="nav justify-content-center gap-4 order-md-3 mb-4 mb-md-0">
              <li class="animate-underline">
                <a class="nav-link fs-xs fw-normal p-0 animate-target" href="#!">Privacy</a>
              </li>
              <li class="animate-underline">
                <a class="nav-link fs-xs fw-normal p-0 animate-target" href="#!">Affiliates</a>
              </li>
              <li class="animate-underline">
                <a class="nav-link fs-xs fw-normal p-0 animate-target" href="#!">Terms of use</a>
              </li>
            </ul>
            <div class="vr text-body-secondary opacity-25 mx-4 d-none d-md-inline-block order-md-2"></div>
            <p class="fs-xs text-center text-lg-start mb-0 order-md-1">
              &copy; All rights reserved. Made by <span class="animate-underline"><a class="animate-target text-dark-emphasis text-decoration-none" href="https://createx.studio/" target="_blank" rel="noreferrer">Createx Studio</a></span>
            </p>
          </div>
        </div>
      </div>
    </footer>



</template>
<style>
@media (min-width: 576px) {
    #categoriesLinks {
      display: block !important;
      height: auto !important;
      visibility: visible !important;
    }
  }
</style>
<script setup>
// нічого не потрібно
</script>
