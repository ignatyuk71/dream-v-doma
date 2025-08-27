@if (config('services.meta_pixel.enabled') && config('services.meta_pixel.id'))
<!-- Meta Pixel NoScript -->
<noscript>
  <img height="1" width="1" style="display:none"
       src="https://www.facebook.com/tr?id={{ config('services.meta_pixel.id') }}&ev=PageView&noscript=1" />
</noscript>
@endif
