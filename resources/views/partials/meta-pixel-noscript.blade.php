@php
  $t = \Illuminate\Support\Facades\DB::table('tracking_settings')->first();

  // безпечне зчитування, навіть якщо $t === null
  $pixelId = $t?->pixel_id ?? null;

  $enabled = $t
    && (int)($t->pixel_enabled ?? 0) === 1
    && !empty($pixelId)
    && !((int)($t->exclude_admin ?? 1) === 1 && request()->is('admin*'));
@endphp

@if ($enabled && $pixelId)
  <noscript>
    <img
      src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
      height="1"
      width="1"
      style="display:none"
      alt=""
    />
  </noscript>
@endif
