@if ($products->hasPages())
  <nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">

      {{-- Prev --}}
      @if ($products->onFirstPage())
        <li class="page-item disabled">
          <span class="page-link">@lang('pagination.previous')</span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">
            @lang('pagination.previous')
          </a>
        </li>
      @endif

      {{-- Номери сторінок --}}
      @foreach ($products->links()->elements as $element)
        {{-- "Три крапки" --}}
        @if (is_string($element))
          <li class="page-item disabled d-sm-none">
            <span class="page-link px-2 pe-none">{{ $element }}</span>
          </li>
        @endif

        {{-- Масив сторінок --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $products->currentPage())
              <li class="page-item active" aria-current="page">
                <span class="page-link">
                  {{ $page }}
                  <span class="visually-hidden">(current)</span>
                </span>
              </li>
            @else
              <li class="page-item d-none d-sm-block">
                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
              </li>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Next --}}
      @if ($products->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">
            @lang('pagination.next')
          </a>
        </li>
      @else
        <li class="page-item disabled">
          <span class="page-link">@lang('pagination.next')</span>
        </li>
      @endif

    </ul>
  </nav>
@endif
