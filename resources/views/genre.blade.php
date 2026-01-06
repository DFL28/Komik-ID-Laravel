@extends('layouts.app')

@section('content')
<div class="genre-page">
    <div class="genre-header">
        <h1 class="genre-header__title">📚 Browse by Genre</h1>
        <p class="genre-header__subtitle">
            @if($genre)
                Showing {{ $manga->total() }} titles in <strong>{{ $genre }}</strong>
            @else
                Select a genre to filter
            @endif
        </p>
    </div>
    
    <!-- Genre Filter Pills -->
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
    
    <!-- Manga Grid -->
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
                    @if($item->genres)
                        <p class="manga-card__genres">{{ Str::limit($item->genres, 30) }}</p>
                    @endif
                </div>
            </a>
        @empty
            <div class="empty-state">
                <p>Tidak ada manga untuk genre {{ $genre ?? 'ini' }}</p>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    <div class="pagination">
        {{ $manga->appends(['genre' => $genre])->links() }}
    </div>
</div>

<style>
.genre-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-md);
}

.genre-header {
    margin-bottom: var(--spacing-xl);
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
    color: var(--text-secondary);
    margin: 0;
}

/* Genre Filter Pills */
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

.manga-card__genres {
    font-size: 0.8rem;
    color: var(--text-tertiary);
    margin: var(--spacing-xs) 0 0;
}

@media (max-width: 768px) {
    .genre-header__title {
        font-size: 1.75rem;
    }
    
    .genre-filter {
        padding: var(--spacing-md);
    }
}
</style>
@endsection
