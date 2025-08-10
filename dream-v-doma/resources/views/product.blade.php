@extends('layouts.app')

@section('content')
    {{-- Topbar --}}
    @include('home.topbar')

    <div class="container-lg">
        {{-- Navbar --}}
        @include('components.navbar')

        <main class="content-wrapper">

            <!-- Size Guide Modal -->
            <div class="modal fade" id="sizeGuide" tabindex="-1" aria-labelledby="sizeGuideLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0">
                        <div class="modal-header">
                            <h5 class="modal-title" id="sizeGuideLabel">{{ __('product.size_guide') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрити"></button>
                        </div>
                        <div class="modal-body">
                            @if ($product->sizeGuide && $product->sizeGuide->data)
                                @php
                                    $locale = app()->getLocale();
                                    $rows = $product->sizeGuide->data[$locale] ?? [];
                                @endphp

                                @if (!empty($rows))
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    @if (is_array(reset($rows)))
                                                        @foreach (array_keys(reset($rows)) as $header)
                                                            <th>{{ $header }}</th>
                                                        @endforeach
                                                    @else
                                                        <th>{{ __('product.size') }}</th>
                                                        <th>{{ __('product.length_cm') }}</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($rows as $key => $row)
                                                    <tr>
                                                        @if (is_array($row))
                                                            @foreach ($row as $value)
                                                                <td>{{ $value }}</td>
                                                            @endforeach
                                                        @else
                                                            <td>{{ $key }}</td>
                                                            <td>{{ $row }}</td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-center text-muted mb-0">
                                        {{ __('Немає даних для цієї мови.') }}
                                    </p>
                                @endif
                            @else
                                <p class="text-center text-muted mb-0">
                                    {{ __('product.no_size_guide') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Size Guide Modal -->

            <!-- Breadcrumb -->
            @include('home.shared.breadcrumbs', ['items' => $items])

            <!-- Галерея + Інфо -->
            <section class="container">
                <div class="row">
                    <div class="col-md-6">
                        @include('home.product-page.product-gallery', ['product' => $product])
                    </div>
                    <div class="col-md-6">
                        @include('home.product-page.product-info', ['product' => $product])
                    </div>
                </div>
            </section>

          

            <!-- Tabs -->
            @include('home.product-page.product-description', ['product' => $product])
        </main>

        {{-- Product Carousel --}}
        @include('home.shared.product-carousel')

        {{-- Instagram Feed --}}
        @include('home.shared.instagram-feed')

        <!-- Toast -->
        <frontend-toast ref="toastRef"></frontend-toast>
    </div>
  <!-- Sticky Bottom Bar (Buy) -->
  @include('home.product-page.sticky-product-bottom-bar', ['product' => $product])

  {{-- Footer --}}
  @include('home.footer')
@endsection
