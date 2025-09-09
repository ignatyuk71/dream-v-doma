<section class="container pt-1 mt-2 mt-sm-3 mt-lg-4 mt-xl-1">
  <div class="tabs-scroll-wrapper">
    <ul class="nav nav-underline product-tabs-scroll" role="tablist">
      <li class="nav-item" role="presentation">
        <button type="button" class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description-tab-pane" role="tab" aria-selected="true">
          {{ __('product.description') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button type="button" class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery-tab-pane" role="tab" aria-selected="false">
          {{ __('product.delivery_and_returns') }}
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button type="button" class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" role="tab" aria-selected="false">
          {{ __('product.reviews') }} <span class="d-none d-md-inline">({{ $product->reviews->count() }})</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button type="button" class="nav-link" onclick="scrollToSpecs()">
          {{ __('product.characteristics') }}
        </button>
      </li>
    </ul>
  </div>

  <div class="tab-content pt-3">
    <div class="tab-pane fade show active fs-sm" id="description-tab-pane" role="tabpanel" aria-labelledby="description-tab">
      @php
        use Illuminate\Support\Str;

        // Хелпер для нормалізації URL зображень
        $toPublicUrl = function ($path) {
            if (empty($path)) {
                return asset('assets/img/placeholder.svg');
            }
            if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path; // вже абсолютний URL
            }
            $p = ltrim($path, '/');

            // якщо вже веб-шлях /storage/...
            if (Str::startsWith($p, 'storage/')) {
                return asset($p);
            }

            // зняти префікси "public/" або "app/public/"
            $p = preg_replace('#^(?:app/)?public/#', '', $p);

            // решту віддаємо як /storage/{p}
            return asset('storage/'.$p);
        };

        $locale = app()->getLocale();
        $desc   = $product->translations->firstWhere('locale', $locale)?->description ?? '';
        $blocks = $desc ? json_decode($desc, true) : [];
      @endphp

      @if(!empty($blocks))
        @foreach($blocks as $block)
          @php $type = $block['type'] ?? 'text'; @endphp



<section class="py-3 border-bottom">
  @switch($type)

    {{-- TEXT BLOCK --}}
    @case('text')
      <div class="container-fluid px-2 px-lg-5">
        <div class="mx-auto">
          @if(!empty($block['title']))
            <h2 class="fw-bold mb-3 text-center cms-block-title">
              {{ $block['title'] }}
            </h2>
          @endif
          <p class="text-muted cms-block-text">
            {!! nl2br(e($block['text'] ?? '')) !!}
          </p>
        </div>
      </div>
    @break

    {{-- IMAGE RIGHT --}}
    @case('image_right')
      <div class="container px-2 px-lg-5">
        <div class="row align-items-center gx-5 gy-4">
          <div class="col-12 col-lg-6">
            @if(!empty($block['title']))
              <h2 class="fw-bold mb-3 cms-block-title">
                {{ $block['title'] }}
              </h2>
            @endif
            <p class="text-muted cms-block-text">
              {!! nl2br(e($block['text'] ?? '')) !!}
            </p>
          </div>
          <div class="col-12 col-lg-6 text-center">
            @if(!empty($block['imageUrl']))
              <img src="{{ $toPublicUrl($block['imageUrl']) }}"
                   alt="{{ $block['title'] ?? 'Product image' }}"
                   class="img-fluid rounded"
                   loading="lazy">
            @endif
          </div>
        </div>
      </div>
    @break

    {{-- IMAGE LEFT --}}
    @case('image_left')
      <div class="container px-2 px-lg-5">
        <div class="row align-items-center gx-5 gy-4 flex-lg-row-reverse">
          <div class="col-12 col-lg-6">
            @if(!empty($block['title']))
              <h2 class="fw-bold mb-3 cms-block-title">
                {{ $block['title'] }}
              </h2>
            @endif
            <p class="text-muted cms-block-text">
              {!! nl2br(e($block['text'] ?? '')) !!}
            </p>
          </div>
          <div class="col-12 col-lg-6 text-center">
            @if(!empty($block['imageUrl']))
              <img src="{{ $toPublicUrl($block['imageUrl']) }}"
                   alt="{{ $block['title'] ?? 'Product image' }}"
                   class="img-fluid rounded"
                   loading="lazy">
            @endif
          </div>
        </div>
      </div>
    @break

    {{-- TWO IMAGES --}}
    @case('two_images')
      <div class="container px-2 px-lg-5">
        <div class="row gx-4 gy-4">
          @if(!empty($block['imageUrl1']))
            <div class="col-12 col-md-6 text-center">
              <img src="{{ $toPublicUrl($block['imageUrl1']) }}"
                   alt="Gallery image 1"
                   class="img-fluid rounded shadow"
                   loading="lazy">
            </div>
          @endif
          @if(!empty($block['imageUrl2']))
            <div class="col-12 col-md-6 text-center">
              <img src="{{ $toPublicUrl($block['imageUrl2']) }}"
                   alt="Gallery image 2"
                   class="img-fluid rounded shadow"
                   loading="lazy">
            </div>
          @endif
        </div>
      </div>
    @break

    @default
      {{-- fallback --}}
  @endswitch
</section>

        @endforeach
      @endif

      {{-- Характеристики --}}
      <div class="col-md-7 order-md-1 pt-4" id="specs-anchor">
        <h2 class="h3 pb-2 pb-md-3">{{ __('specs.title') }}</h2>
        <ul class="list-unstyled d-flex flex-column gap-3 fs-sm pb-3 m-0 mb-2 mb-sm-3">
          @foreach ($product->attributeValues as $attrValue)
            @php
              $attrTrans  = $attrValue->attribute->translations->firstWhere('locale', app()->getLocale());
              $valueTrans = $attrValue->translations->firstWhere('locale', app()->getLocale());
            @endphp
            @if ($attrTrans && $valueTrans)
              <li class="d-flex align-items-center position-relative pe-4">
                <span>{{ $attrTrans->name }}:</span>
                <span class="d-block flex-grow-1 border-bottom border-dashed px-1 mt-2 mx-2"></span>
                <span class="text-dark-emphasis fw-medium text-end">{{ $valueTrans->value }}</span>
              </li>
            @endif
          @endforeach
        </ul>
      </div>
    </div>

    <div class="tab-pane fade fs-sm" id="delivery-tab-pane" role="tabpanel" aria-labelledby="delivery-tab">
      <p>{{ __('product.delivery_and_returns') }}...</p>
    </div>

    <div class="tab-pane fade fs-sm" id="reviews-tab-pane" role="tabpanel" aria-labelledby="reviews-tab">
      <div class="col-md-7 order-md-1">
        <div class="d-flex align-items-center mb-4">
          <h2 class="h3 mb-0">{{ __('product.reviews') }}</h2>
          <button type="button" class="btn btn-secondary ms-auto" data-bs-toggle="modal" data-bs-target="#reviewForm">
            <i class="ci-edit-3 fs-base ms-n1 me-2"></i>
            {{ __('review.leave_a_review') }}
          </button>
        </div>

        <div class="row g-4 pb-3">
          <div class="col-sm-4">
            <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-body-tertiary rounded p-4">
              <div class="h1 pb-2 mb-1">{{ number_format($product->approvedReviews->avg('rating'), 1) }}</div>
              <div class="hstack justify-content-center gap-1 fs-sm mb-2">
                @for ($i = 1; $i <= 5; $i++)
                  <i class="ci-star{{ $i <= round($product->approvedReviews->avg('rating')) ? '-filled text-warning' : ' text-body-tertiary opacity-60' }}"></i>
                @endfor
              </div>
              <div class="fs-sm">{{ $product->approvedReviews->count() }} {{ __('product.review_many') }}</div>
            </div>
          </div>

          <div class="col-sm-8">
            <div class="vstack gap-3">
              @for ($i = 5; $i >= 1; $i--)
                @php
                  $count = $product->approvedReviews->where('rating', $i)->count();
                  $total = $product->approvedReviews->count();
                  $percent = $total ? round(($count / $total) * 100, 1) : 0;
                @endphp
                <div class="hstack gap-2">
                  <div class="hstack fs-sm gap-1">
                    {{ $i }}<i class="ci-star-filled text-warning"></i>
                  </div>
                  <div class="progress w-100" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" style="height: 4px">
                    <div class="progress-bar bg-warning rounded-pill" style="width: {{ $percent }}%"></div>
                  </div>
                  <div class="fs-sm text-nowrap text-end" style="width: 40px;">{{ $count }}</div>
                </div>
              @endfor
            </div>
          </div>
        </div>

        @foreach ($product->reviews as $review)
          <div class="border-bottom py-3 mb-3">
            <div class="d-flex align-items-center mb-3 justify-content-between">
              <div class="text-nowrap me-3">
                <span class="h6 mb-0">{{ $review->author_name }}</span>
              </div>
              <span class="text-body-secondary fs-sm">{{ $review->created_at->format('d.m.Y') }}</span>
            </div>

            <div class="d-flex gap-1 fs-sm pb-2 mb-1">
              @for ($i = 1; $i <= 5; $i++)
                <i class="ci-star{{ $i <= $review->rating ? '-filled text-warning' : ' text-body-tertiary opacity-60' }}"></i>
              @endfor
            </div>

            <p class="fs-sm mb-2">{{ $review->content }}</p>

            @if ($review->photo_path)
              {{-- якщо ти зберігаєш фото в storage/app/public/review/... — краще теж через нормалізацію --}}
              @php $reviewImg = $toPublicUrl($review->photo_path); @endphp
              <div>
                <img src="{{ $reviewImg }}"
                     width="50" height="50" class="rounded border cursor-pointer"
                     data-bs-toggle="modal" data-bs-target="#photoModal-{{ $review->id }}"
                     alt="{{ __('review.photo') }}">
              </div>
            @endif
          </div>

          @if ($review->photo_path)
            @php $reviewImg = $toPublicUrl($review->photo_path); @endphp
            <div class="modal fade" id="photoModal-{{ $review->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-transparent border-0">
                  <div class="modal-body p-0 position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="{{ __('review.modal_close') }}"></button>
                    <img src="{{ $reviewImg }}" class="img-fluid rounded" alt="{{ __('review.photo') }}">
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  </div>
</section>

<div class="modal fade" id="reviewForm" data-bs-backdrop="static" tabindex="-1" aria-labelledby="reviewFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <form method="POST"
          action="{{ route('product-reviews.store', ['locale' => app()->getLocale()]) }}"
          enctype="multipart/form-data"
          class="modal-content needs-validation" novalidate>
      @csrf
      <input type="hidden" name="product_id" value="{{ $product->id }}">

      <div class="modal-header border-0">
        <h5 class="modal-title" id="reviewFormLabel">{{ __('review.modal_title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('review.modal_close') }}"></button>
      </div>

      <div class="modal-body pb-3 pt-0">
        <div class="mb-3">
          <label for="review-name" class="form-label">{{ __('review.your_name') }} <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="review-name" name="author_name" required>
          <div class="invalid-feedback">{{ __('review.your_name_required') }}</div>
        </div>

        <div class="mb-3">
          <label class="form-label">{{ __('review.rating') }} <span class="text-danger">*</span></label>
          <select name="rating" class="form-select" required>
            <option value="">{{ __('review.select_rating') }}</option>
            @for ($i = 5; $i >= 1; $i--)
              <option value="{{ $i }}">{{ $i }} зірк{{ $i == 1 ? 'а' : ($i <= 4 ? 'и' : 'ок') }}</option>
            @endfor
          </select>
          <div class="invalid-feedback">{{ __('review.rating_required') }}</div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="review-text">{{ __('review.review_text') }} <span class="text-danger">*</span></label>
          <textarea class="form-control" rows="4" id="review-text" name="content" required minlength="10"></textarea>
          <div class="invalid-feedback">{{ __('review.review_text_required') }}</div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="review-photo">{{ __('review.photo') }}</label>
          <input class="form-control" type="file" name="photo" id="review-photo" accept="image/*">
          <div class="form-text">{{ __('review.photo_note') }}</div>
          <div id="photo-preview" class="mt-2"></div>
        </div>
      </div>

      <div class="modal-footer flex-nowrap gap-3 border-0 px-4">
        <button type="reset" class="btn btn-secondary w-100 m-0" data-bs-dismiss="modal">{{ __('review.cancel') }}</button>
        <button type="submit" class="btn btn-primary w-100 m-0">{{ __('review.submit') }}</button>
      </div>
    </form>
  </div>
</div>
<style>
  /* Десктоп / за замовчуванням */
  .cms-block-title { font-size: 1.75rem; }
  .cms-block-text  { font-size: 1.2rem; line-height: 1.8; }

  /* Мобільні (Bootstrap sm і нижче) */
  @media (max-width: 575.98px) {
    .cms-block-title { font-size: 1rem; }        /* заголовок трохи менший на мобайлі */
    .cms-block-text  { font-size: 0.8rem; line-height: 1.4; }
  }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.getElementById('review-photo');
  const previewContainer = document.getElementById('photo-preview');

  fileInput.addEventListener('change', function () {
    const file = this.files[0];
    previewContainer.innerHTML = '';
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      previewContainer.innerHTML = '<div class="text-danger">Файл не є зображенням.</div>';
      this.value = '';
      return;
    }

    const now = new Date();
    const pad = num => String(num).padStart(2, '0');
    const fileExt = file.name.split('.').pop().toLowerCase();
    const filename = `domashni-tapochkidream-v-doma_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}.${fileExt}`;

    const reader = new FileReader();
    reader.onload = function (e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.alt = 'Попередній перегляд';
      img.className = 'rounded border';
      img.style.maxWidth = '120px';
      img.style.maxHeight = '120px';

      const caption = document.createElement('div');
      caption.className = 'small mt-2 text-muted';
      caption.textContent = 'Назва файлу: ' + filename;

      previewContainer.appendChild(img);
      previewContainer.appendChild(caption);
    };
    reader.readAsDataURL(file);
  });
});
</script>

@if (session('success'))
<script>
  document.addEventListener('DOMContentLoaded', () => {
    showGlobalToast(@json(session('success')), 'success');
  });
</script>
@endif
<script>
  function scrollToSpecs() {
    const descTab = document.querySelector('#description-tab');
    const descPane = document.querySelector('#description-tab-pane');

    if (descTab && descPane) {
      document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('show', 'active'));
      document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));

      descPane.classList.add('show', 'active');
      descTab.classList.add('active');
    }

    setTimeout(() => {
      const el = document.getElementById('specs-anchor');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    }, 200);
  }
</script>
@endpush
