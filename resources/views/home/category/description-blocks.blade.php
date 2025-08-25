@php
  // Приймаємо $blocks (масив). Якщо не передали — пробуємо зчитати з $translation->description (JSON).
  if (!isset($blocks)) {
      $locale = app()->getLocale();
      $raw = $translation->description ?? null;

      $blocks = [];
      if (is_array($raw)) {
          $value = array_is_list($raw) ? $raw : ($raw[$locale] ?? reset($raw) ?? null);
          if (is_array($value)) {
              $blocks = $value;
          } elseif (is_string($value) && trim($value) !== '') {
              $blocks = [['type' => 'text', 'title' => null, 'text' => $value]];
          }
      } elseif (is_string($raw) && trim($raw) !== '') {
          $blocks = [['type' => 'text', 'title' => null, 'text' => $raw]];
      }
  }
@endphp

@if(!empty($blocks))
  @foreach($blocks as $block)
    @php $type = $block['type'] ?? 'text'; @endphp

    <section class="py-3 border-bottom">
      @switch($type)

        {{-- TEXT BLOCK --}}
        @case('text')
          <div class="container-fluid px-4 px-lg-5">
            <div class="mx-auto">
              @if(!empty($block['title']))
                <h2 class="fw-bold mb-3 text-center" style="font-size:1.75rem;">
                  {{ $block['title'] }}
                </h2>
              @endif
              @if(!empty($block['text']))
                <p class="text-muted" style="font-size:1.1rem; line-height:1.7; white-space:pre-line;">
                  {!! nl2br(e($block['text'])) !!}
                </p>
              @endif
            </div>
          </div>
        @break

        {{-- IMAGE RIGHT --}}
        @case('image_right')
          <div class="container px-4 px-lg-5">
            <div class="row align-items-center gx-5 gy-4">
              <div class="col-12 col-lg-6">
                @if(!empty($block['title']))
                  <h2 class="fw-bold mb-3" style="font-size:1.75rem;">
                    {{ $block['title'] }}
                  </h2>
                @endif
                @if(!empty($block['text']))
                  <p class="text-muted" style="font-size:1.1rem; line-height:1.7; white-space:pre-line;">
                    {!! nl2br(e($block['text'])) !!}
                  </p>
                @endif
              </div>
              <div class="col-12 col-lg-6 text-center">
                @if(!empty($block['imageUrl']))
                  <img src="{{ asset($block['imageUrl']) }}"
                       alt="{{ $block['title'] ?? 'Category image' }}"
                       class="img-fluid rounded"
                       loading="lazy">
                @endif
              </div>
            </div>
          </div>
        @break

        {{-- IMAGE LEFT --}}
        @case('image_left')
          <div class="container px-4 px-lg-5">
            <div class="row align-items-center gx-5 gy-4 flex-lg-row-reverse">
              <div class="col-12 col-lg-6">
                @if(!empty($block['title']))
                  <h2 class="fw-bold mb-3" style="font-size:1.75rem;">
                    {{ $block['title'] }}
                  </h2>
                @endif
                @if(!empty($block['text']))
                  <p class="text-muted" style="font-size:1.1rem; line-height:1.7; white-space:pre-line;">
                    {!! nl2br(e($block['text'])) !!}
                  </p>
                @endif
              </div>
              <div class="col-12 col-lg-6 text-center">
                @if(!empty($block['imageUrl']))
                  <img src="{{ asset($block['imageUrl']) }}"
                       alt="{{ $block['title'] ?? 'Category image' }}"
                       class="img-fluid rounded"
                       loading="lazy">
                @endif
              </div>
            </div>
          </div>
        @break

        {{-- TWO IMAGES --}}
        @case('two_images')
          <div class="container px-4 px-lg-5">
            <div class="row gx-4 gy-4">
              @if(!empty($block['imageUrl1']))
                <div class="col-12 col-md-6 text-center">
                  <img src="{{ asset($block['imageUrl1']) }}"
                       alt="Gallery image 1"
                       class="img-fluid rounded shadow"
                       loading="lazy">
                </div>
              @endif
              @if(!empty($block['imageUrl2']))
                <div class="col-12 col-md-6 text-center">
                  <img src="{{ asset($block['imageUrl2']) }}"
                       alt="Gallery image 2"
                       class="img-fluid rounded shadow"
                       loading="lazy">
                </div>
              @endif
            </div>
          </div>
        @break

        @default
          {{-- unknown type - skip --}}
      @endswitch
    </section>
  @endforeach
@endif
