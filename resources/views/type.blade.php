@extends('layouts.app')

@section('content')
<div class="type-page">
    <div class="type-header">
        <h1 class="type-header__title">
            @if($type === 'manhwa')
                🇰🇷 Manhwa
            @elseif($type === 'manhua')
                🇨🇳 Manhua
            @else
                🇯🇵 Manga
            @endif
        </h1>
        <p class="type-header__subtitle">{{ $manga->total() }} titles available</p>
    </div>
    
    <div class="manga-grid">
        @forelse($manga as $item)
            <a href="{{ route('manga.detail', $item->slug) }}" class="manga-card">
                <div class="manga-card__cover">
                    <img src="{{ resolveMedia($item->cover_path) }}" alt="{{ $item->title }}" loading="lazy">
                    @if($item->status === 'completed')
                        <span class="manga-card__badge">Complete</span>
                    @endif
                </div>
                <div class="manga-card__info">
                    <h3 class="manga-card__title">{{ $item->title }}</h3>
                    @if($item->last_chapter_at)
                        <p class="manga-card__chapter">{{ timeAgo($item->last_chapter_at) }}</p>
                    @endif
                </div>
            </a>
        @empty
            <p class="empty-state">Belum ada {{ $type }} tersedia.</p>
        @endforelse
    </div>
    
    {{-- Custom Inline Pagination --}}
    @if ($manga->hasPages())
    <div class="custom-pagination">
        <div class="pagination-info">
            <p>Menampilkan <strong>{{ $manga->firstItem() }}</strong> - <strong>{{ $manga->lastItem() }}</strong> dari <strong>{{ $manga->total() }}</strong> hasil</p>
        </div>
        
        <div class="pagination-controls">
            {{-- Previous --}}
            @if ($manga->onFirstPage())
                <span class="page-btn disabled">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="btn-text">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $manga->previousPageUrl() }}" class="page-btn">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <span class="btn-text">Sebelumnya</span>
                </a>
            @endif

            {{-- Page Numbers --}}
            <div class="page-numbers">
                @foreach(range(1, $manga->lastPage()) as $page)
                    @if($page == 1 || $page == $manga->lastPage() || abs($page - $manga->currentPage()) < 2)
                        @if($page == $manga->currentPage())
                            <span class="page-num active">{{ $page }}</span>
                        @else
                            <a href="{{ $manga->url($page) }}" class="page-num">{{ $page }}</a>
                        @endif
                    @elseif(abs($page - $manga->currentPage()) == 2)
                        <span class="page-dots">...</span>
                    @endif
                @endforeach
            </div>

            {{-- Next --}}
            @if ($manga->hasMorePages())
                <a href="{{ $manga->nextPageUrl() }}" class="page-btn">
                    <span class="btn-text">Selanjutnya</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </a>
            @else
                <span class="page-btn disabled">
                    <span class="btn-text">Selanjutnya</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
.type-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-md);
}

.type-header {
    margin-bottom: var(--spacing-2xl);
    text-align: center;
}

.type-header__title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-sm);
}

.type-header__subtitle {
    font-size: 1.1rem;
    color: var(--text-tertiary);
    margin: 0;
}

/* Custom Pagination Styles */
.custom-pagination {
    margin-top: 3rem;
    padding: 2rem 0;
}

.pagination-info {
    text-align: center;
    margin-bottom: 1.5rem;
}

.pagination-info p {
    margin: 0;
    font-size: 0.95rem;
    color: #6b7280;
}

.pagination-info strong {
    font-weight: 700;
    color: #1f2937;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1f2937;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.page-btn:hover:not(.disabled) {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.page-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f3f4f6;
}

.page-btn svg {
    flex-shrink: 0;
}

.btn-text {
    display: none;
}

.page-numbers {
    display: flex;
    gap: 0.375rem;
    align-items: center;
}

.page-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #1f2937;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.page-num:hover:not(.active) {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
    transform: translateY(-2px);
}

.page-num.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
}

.page-dots {
    color: #9ca3af;
    font-weight: 700;
    padding: 0 0.25rem;
}

@media (min-width: 640px) {
    .btn-text {
        display: inline;
    }
}

@media (max-width: 768px) {
    .type-header__title {
        font-size: 1.75rem;
    }
    
    .custom-pagination {
        margin-top: 2rem;
        padding: 1rem 0;
    }
    
    .pagination-controls {
        gap: 0.25rem;
    }
    
    .page-btn {
        padding: 0.5rem;
        min-width: 2.5rem;
    }
    
    .page-num {
        min-width: 2rem;
        height: 2rem;
        font-size: 0.85rem;
    }
}
</style>
@endsection
