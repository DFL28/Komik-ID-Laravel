@extends('layouts.app')

@section('content')
<div class="genre-page">
    <div class="genre-header">
        <h1 class="genre-header__title">Browse by Genre</h1>
        @if($genre)
            <p class="genre-header__subtitle">Showing {{ $manga->total() }} titles in <strong>{{ $genre }}</strong></p>
        @else
            <p class="genre-header__subtitle">{{ $manga->total() }} titles available</p>
        @endif
    </div>

    <form class="genre-advanced-filter" method="get" action="{{ route('genre') }}">
        <div class="genre-advanced-filter__row">
            <div class="filter-group">
                <label for="filterGenre">Genre</label>
                <select id="filterGenre" name="genre">
                    <option value="">All</option>
                    @foreach($genres as $g)
                        <option value="{{ $g }}" {{ $genre === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="filterStatus">Status</label>
                <select id="filterStatus" name="status">
                    <option value="">All</option>
                    <option value="ongoing" {{ $status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterContentType">Content type</label>
                <select id="filterContentType" name="content_type">
                    <option value="">All</option>
                    @foreach($contentTypes ?? [] as $ct)
                        <option value="{{ $ct }}" {{ $contentType === $ct ? 'selected' : '' }}>{{ $ct }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="filterType">Type</label>
                <select id="filterType" name="type">
                    <option value="">All</option>
                    @foreach($typeOptions ?? [] as $to)
                        <option value="{{ $to }}" {{ $type === $to ? 'selected' : '' }}>{{ $to }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="filterOrder">Order</label>
                <select id="filterOrder" name="order">
                    <option value="default" {{ $order === 'default' ? 'selected' : '' }}>Default</option>
                    <option value="latest" {{ $order === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="popular" {{ $order === 'popular' ? 'selected' : '' }}>Popular</option>
                    <option value="rating" {{ $order === 'rating' ? 'selected' : '' }}>Rating</option>
                    <option value="title" {{ $order === 'title' ? 'selected' : '' }}>Title</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filterColor">Color</label>
                <select id="filterColor" name="color">
                    <option value="">All</option>
                    <option value="color" {{ $color === 'color' ? 'selected' : '' }}>Color</option>
                    <option value="bw" {{ $color === 'bw' ? 'selected' : '' }}>B/W</option>
                </select>
            </div>
        </div>
        <div class="genre-advanced-filter__actions">
            <button type="submit" class="btn btn--primary">Apply</button>
            <a href="{{ route('genre') }}" class="btn btn--ghost">Reset</a>
        </div>
    </form>

    @if(!empty($genres))
    <div class="genre-filter">
        <a href="{{ route('genre') }}"
           class="genre-pill {{ !$genre ? 'genre-pill--active' : '' }}">
            All Genres
        </a>
        @foreach($genres as $g)
            <a href="{{ route('genre', ['genre' => $g]) }}"
               class="genre-pill {{ $genre === $g ? 'genre-pill--active' : '' }}">
                {{ $g }}
            </a>
        @endforeach
    </div>
    @endif

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
            <p class="empty-state">Belum ada manga untuk genre ini.</p>
        @endforelse
    </div>

    @if ($manga->hasPages())
    <div class="custom-pagination">
        <div class="pagination-info">
            <p>Menampilkan <strong>{{ $manga->firstItem() }}</strong> - <strong>{{ $manga->lastItem() }}</strong> dari <strong>{{ $manga->total() }}</strong> hasil</p>
        </div>

        <div class="pagination-controls">
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
.genre-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-md);
}

.genre-header {
    margin-bottom: var(--spacing-2xl);
    text-align: center;
}

.genre-header__title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-sm);
}

.genre-header__subtitle {
    font-size: 1.1rem;
    color: var(--text-tertiary);
    margin: 0;
}

.genre-advanced-filter {
    margin-bottom: var(--spacing-xl);
    padding: var(--spacing-lg);
    background: var(--bg-secondary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-lg);
}

.genre-advanced-filter__row {
    display: grid;
    grid-template-columns: repeat(6, minmax(120px, 1fr));
    gap: var(--spacing-md);
}

.filter-group label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-tertiary);
    margin-bottom: var(--spacing-xs);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.filter-group select {
    width: 100%;
    padding: 0.6rem 0.8rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    font-size: 0.9rem;
}

.genre-advanced-filter__actions {
    margin-top: var(--spacing-md);
    display: flex;
    gap: var(--spacing-sm);
}

.genre-filter {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-2xl);
    padding: var(--spacing-lg);
    background: var(--bg-secondary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-lg);
}

.genre-pill {
    padding: var(--spacing-sm) var(--spacing-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-full);
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all var(--transition-fast);
}

.genre-pill:hover {
    background: var(--bg-elevated);
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

.genre-pill--active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-color: var(--primary);
    color: white;
    box-shadow: 0 4px 12px var(--primary-glow);
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
    .genre-header__title {
        font-size: 1.75rem;
    }

    .genre-advanced-filter__row {
        grid-template-columns: repeat(2, minmax(140px, 1fr));
    }

    .genre-advanced-filter__actions {
        flex-direction: column;
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

@media (max-width: 540px) {
    .genre-advanced-filter__row {
        grid-template-columns: 1fr;
    }

    .genre-filter {
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: var(--spacing-sm);
        padding-bottom: var(--spacing-sm);
    }

    .genre-pill {
        white-space: nowrap;
        font-size: 0.8rem;
        padding: var(--spacing-xs) var(--spacing-md);
    }
}
</style>
@endsection
