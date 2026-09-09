@if ($paginator->hasPages())
    @php
        $anchorId = isset($anchor) ? ltrim((string) $anchor, '#') : null;
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $startPage = max(1, $currentPage - 2);
        $endPage = min($lastPage, $currentPage + 2);
    @endphp

    <div class="pagination-wrap">
        <span class="pagination-summary">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </span>
        <nav class="pagination-nav" aria-label="Pagination data">
            @if ($paginator->onFirstPage())
                <span class="pagination-disabled" aria-disabled="true"><i class="fa-solid fa-chevron-left"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}{{ $anchorId ? '#' . $anchorId : '' }}" aria-label="Halaman sebelumnya"><i class="fa-solid fa-chevron-left"></i></a>
            @endif

            @if ($startPage > 1)
                <a href="{{ $paginator->url(1) }}{{ $anchorId ? '#' . $anchorId : '' }}">1</a>
                @if ($startPage > 2)
                    <span class="pagination-ellipsis">…</span>
                @endif
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page === $currentPage)
                    <span class="pagination-current" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}{{ $anchorId ? '#' . $anchorId : '' }}">{{ $page }}</a>
                @endif
            @endfor

            @if ($endPage < $lastPage)
                @if ($endPage < $lastPage - 1)
                    <span class="pagination-ellipsis">…</span>
                @endif
                <a href="{{ $paginator->url($lastPage) }}{{ $anchorId ? '#' . $anchorId : '' }}">{{ $lastPage }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}{{ $anchorId ? '#' . $anchorId : '' }}" aria-label="Halaman berikutnya"><i class="fa-solid fa-chevron-right"></i></a>
            @else
                <span class="pagination-disabled" aria-disabled="true"><i class="fa-solid fa-chevron-right"></i></span>
            @endif
        </nav>
    </div>
@endif
