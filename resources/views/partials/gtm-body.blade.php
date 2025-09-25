@if(app()->environment('production') && config('services.gtm.enabled') && config('services.gtm.container_id'))
  <!-- Google Tag Manager (noscript) -->
  <noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.gtm.container_id') }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
  </noscript>
  <!-- End Google Tag Manager (noscript) -->
@endif