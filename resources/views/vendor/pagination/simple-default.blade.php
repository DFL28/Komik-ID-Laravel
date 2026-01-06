@if ($paginator->hasPages())
    <nav class="pagination-wrapper" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-info">
            <p class="pagination-info__text">
                Menampilkan 
                <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                -
                <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-semibold">{{ $paginator->total() }}</span>
                hasil
            </p>
        </div>

        <div class="pagination-controls">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn--disabled" aria-disabled="true">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="pagination-btn__text">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn pagination-btn--prev" rel="prev">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="pagination-btn__text">Sebelumnya</span>
                </a>
            @endif

            {{-- Pagination Numbers --}}
            <div class="pagination-numbers">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-dots" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-number pagination-number--active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-number">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn pagination-btn--next" rel="next">
                    <span class="pagination-btn__text">Selanjutnya</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @else
                <span class="pagination-btn pagination-btn--disabled" aria-disabled="true">
                    <span class="pagination-btn__text">Selanjutnya</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif

<style>
.pagination-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin: 3rem 0;
}

.pagination-info {
    text-align: center;
}

.pagination-info__text {
    font-size: 0.95rem;
    color: var(--text-secondary, #6b7280);
    margin: 0;
}

.pagination-info__text .font-semibold {
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    background: var(--bg-secondary, #f9fafb);
    border: 2px solid var(--border-primary, #e5e7eb);
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.pagination-btn:hover:not(.pagination-btn--disabled) {
    background: var(--primary, #3b82f6);
    color: white;
    border-color: var(--primary, #3b82f6);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.pagination-btn--disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: var(--bg-tertiary, #f3f4f6);
}

.pagination-btn__text {
    display: none;
}

@media (min-width: 640px) {
    .pagination-btn__text {
        display: inline;
    }
}

.pagination-numbers {
    display: flex;
    gap: 0.375rem;
}

.pagination-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    background: var(--bg-secondary, #f9fafb);
    border: 2px solid var(--border-primary, #e5e7eb);
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.pagination-number:hover {
    background: var(--primary, #3b82f6);
    color: white;
    border-color: var(--primary, #3b82f6);
    transform: translateY(-2px);
}

.pagination-number--active {
    background: var(--primary, #3b82f6);
    color: white;
    border-color: var(--primary, #3b82f6);
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

.pagination-dots {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    color: var(--text-tertiary, #9ca3af);
    font-weight: 700;
}

@media (max-width: 639px) {
    .pagination-wrapper {
        gap: 1rem;
        margin: 2rem 0;
    }

    .pagination-controls {
        gap: 0.25rem;
    }

    .pagination-btn {
        padding: 0.5rem;
        min-width: 2.5rem;
    }

    .pagination-number {
        min-width: 2rem;
        height: 2rem;
        font-size: 0.85rem;
    }

    .pagination-numbers {
        gap: 0.25rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .pagination-info__text {
        color: #9ca3af;
    }

    .pagination-info__text .font-semibold {
        color: #f9fafb;
    }

    .pagination-btn {
        color: #f9fafb;
        background: #1f2937;
        border-color: #374151;
    }

    .pagination-btn--disabled {
        background: #111827;
    }

    .pagination-number {
        color: #f9fafb;
        background: #1f2937;
        border-color: #374151;
    }

    .pagination-dots {
        color: #6b7280;
    }
}
</style>
