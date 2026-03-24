@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        <div class="flex gap-2 items-center justify-between sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-200 cursor-not-allowed leading-5 rounded-md">
{!! __('pagination.previous') !!}
</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 leading-5 rounded-md hover:bg-orange-50 hover:text-orange-600 focus:outline-none focus:ring focus:ring-orange-300 focus:border-orange-500 transition">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 leading-5 rounded-md hover:bg-orange-50 hover:text-orange-600 focus:outline-none focus:ring focus:ring-orange-300 focus:border-orange-500 transition">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-200 cursor-not-allowed leading-5 rounded-md">
{!! __('pagination.next') !!}
</span>
            @endif

        </div>


        <div class="hidden sm:flex-1 sm:flex sm:gap-2 sm:items-center sm:justify-between">

            <div>
                <p class="text-sm text-gray-600">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-semibold">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>


            <div>
<span class="inline-flex shadow-sm rounded-lg overflow-hidden">


{{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="inline-flex items-center px-3 py-2 text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
</svg>
</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
           class="inline-flex items-center px-3 py-2 text-gray-600 bg-white border border-gray-200 hover:bg-orange-50 hover:text-orange-600 transition">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
</svg>
</a>
    @endif



    {{-- Pages --}}
    @foreach ($elements as $element)

        @if (is_string($element))
            <span class="inline-flex items-center px-4 py-2 border border-gray-200 text-gray-400 bg-white">
{{ $element }}
</span>
        @endif


        @if (is_array($element))
            @foreach ($element as $page => $url)

                @if ($page == $paginator->currentPage())
                    <span class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-orange-600 border border-orange-600">
{{ $page }}
</span>
                @else
                    <a href="{{ $url }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-orange-50 hover:text-orange-600 transition">
{{ $page }}
</a>
                @endif

            @endforeach
        @endif

    @endforeach



    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
           class="inline-flex items-center px-3 py-2 text-gray-600 bg-white border border-gray-200 hover:bg-orange-50 hover:text-orange-600 transition">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
</svg>
</a>
    @else
        <span class="inline-flex items-center px-3 py-2 text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed">
<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
</svg>
</span>
    @endif


</span>
            </div>

        </div>
    </nav>
@endif
