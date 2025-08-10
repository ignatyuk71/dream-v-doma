@php
    $locale = app()->getLocale();
@endphp

<section class="container pt-5 mt-2 mt-sm-3 mt-lg-4 mt-xl-5">

    @foreach ($categories as $category)
        @php
            $catTranslation = $category->translations->firstWhere('locale', $locale) ?? $category->translations->first();
            $categorySlug = $catTranslation->slug ?? $category->id;

            // Обмежуємо вивід до 4 або 8 товарів
            $categoryProducts = $category->products->take(8);
        @endphp

        {{-- Назва категорії і кнопка “Дивитись всі” --}}
        <div class="d-flex justify-content-between align-items-center mb-2 mt-5">
            <h2 class="h4 mb-0">
                {{ $catTranslation->name ?? '' }}
            </h2>
          
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gy-4 gy-md-5">
            @foreach ($categoryProducts as $product)
                @php
                    $translation = $product->translations->firstWhere('locale', $locale) ?? $product->translations->first();
                    $productSlug = $translation->slug ?? $product->id;
                @endphp
                <div class="col">
                    <div class="animate-underline hover-effect-opacity">
                        <div class="position-relative mb-3">
                            {{-- Лейбли, кнопки, зображення --}}
                            <a class="d-block" href="{{ url($locale . '/' . $categorySlug . '/' . $productSlug) }}">
                                <div class="ratio" style="--cz-aspect-ratio: calc(308 / 274 * 100%)">
                                    <img src="{{ $product->images[0]->url ?? asset('assets/img/placeholder.svg') }}"
                                        alt="Product Image" style="object-fit: cover; width: 100%; height: 100%;" />
                                </div>
                            </a>
                        </div>
                        <div class="nav mb-2">
                            <a class="nav-link animate-target min-w-0 text-dark-emphasis p-0" href="#">
                                <span class="text-truncate">{{ $translation->name ?? '' }}</span>
                            </a>
                        </div>
                        <div class="h6 mb-2">
                            {{ $product->price }} {{ __('currency') }}
                            @if ($product->old_price)
                                <del class="fs-sm fw-normal text-body-tertiary">{{ $product->old_price }} {{ __('currency') }}</del>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
            
        </div>
        <a href="{{ url($locale . '/' . $categorySlug) }}" class="btn btn-outline-primary btn-sm">
                  {{ __('View all') }}
            </a>
    @endforeach

</section>
