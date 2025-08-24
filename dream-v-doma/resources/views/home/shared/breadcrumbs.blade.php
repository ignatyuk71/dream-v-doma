<nav class="container pt-2 pt-xxl-3 my-3 my-md-2" aria-label="breadcrumb">
  <ol class="breadcrumb">
    @foreach ($items as $item)
      <li class="breadcrumb-item {{ $item['active'] ? 'active' : '' }}"@if ($item['active']) aria-current="page" @endif>
        @if ($item['active'])
          {{ $item['text'] }}
        @else
          <a href="{{ $item['href'] }}">{{ $item['text'] }}</a>
        @endif
      </li>
    @endforeach
  </ol>
</nav>

