@php
  $t = \Illuminate\Support\Facades\DB::table('tracking_settings')->first();
  $pixelId  = $t->pixel_id ?? null;
  $enabled  = $t
              && (int)($t->pixel_enabled ?? 0) === 1
              && !empty($pixelId)
              && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));
@endphp

@if ($enabled)
<noscript>
  <img height="1" width="1" style="display:none"
       src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1" />
</noscript>
@endif
