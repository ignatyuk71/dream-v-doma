@php
    use App\Models\InstagramPost;

    $posts = InstagramPost::where('active', true)
        ->orderBy('position') // тобто від меншого до більшого
        ->take(5)             // беремо перші 4
        ->get();
@endphp

@if ($posts->count())
<section class="container mt-2 mt-sm-3 mt-lg-4 mt-xl-5 mb-5">
    <div class="text-center pt-1 pb-2 pb-md-3">
        <h2 class="pb-2 mb-1">
            <span class="animate-underline">
                <a class="animate-target text-dark-emphasis text-decoration-none" href="#!">
                    @lang('instagram.title')
                </a>
            </span>
        </h2>
        <p>@lang('instagram.subtitle')</p>
    </div>

    <div class="overflow-x-auto pb-3 mb-n3" data-simplebar>
        <div class="d-flex gap-2 gap-md-3 gap-lg-4" style="min-width: 700px">
            @foreach($posts as $post)
                <a
                    class="hover-effect-scale hover-effect-opacity position-relative w-100 overflow-hidden"
                    href="{{ $post->link ?? '#' }}"
                    target="_blank"
                >
                    <span class="hover-effect-target position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-25 opacity-0 z-1"></span>
                    <i class="ci-instagram hover-effect-target fs-4 text-white position-absolute top-50 start-50 translate-middle opacity-0 z-2"></i>
                    <div class="hover-effect-target ratio ratio-1x1">
                        <img src="/{{ $post->image }}" alt="{{ $post->alt ?? 'Instagram image' }}" />
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
