@extends('layouts.app')

@section('content')
<div class="home-page">
    
    <!-- Latest Updates Section -->
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">📅 Update Terbaru</h2>
            <a href="{{ route('latest') }}" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            @forelse($latestUpdates as $item)
                <a href="{{ route('manga.detail', $item->slug) }}" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="{{ resolveMedia($item->cover_path) }}" alt="{{ $item->title }}" loading="lazy">
                        <span class="manga-card__badge manga-card__badge--update">NEW</span>
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title">{{ $item->title }}</h3>
                        <p class="manga-card__chapter">{{ timeAgo($item->last_chapter_at) }}</p>
                    </div>
                </a>
            @empty
                <p class="empty-state">Belum ada update terbaru.</p>
            @endforelse
        </div>
    </section>

    <!-- Manhwa Section -->
    @if($manhwa->isNotEmpty())
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">🇰🇷 Manhwa</h2>
            <a href="{{ route('type', 'manhwa') }}" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            @foreach($manhwa as $item)
                <a href="{{ route('manga.detail', $item->slug) }}" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="{{ resolveMedia($item->cover_path) }}" alt="{{ $item->title }}" loading="lazy">
                        @if($item->status === 'completed')
                            <span class="manga-card__badge manga-card__badge--complete">Complete</span>
                        @endif
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title">{{ $item->title }}</h3>
                        @if($item->last_chapter_at)
                            <p class="manga-card__chapter">{{ timeAgo($item->last_chapter_at) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Manhua Section -->
    @if($manhua->isNotEmpty())
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">🇨🇳 Manhua</h2>
            <a href="{{ route('type', 'manhua') }}" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            @foreach($manhua as $item)
                <a href="{{ route('manga.detail', $item->slug) }}" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="{{ resolveMedia($item->cover_path) }}" alt="{{ $item->title }}" loading="lazy">
                        @if($item->status === 'completed')
                            <span class="manga-card__badge manga-card__badge--complete">Complete</span>
                        @endif
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title">{{ $item->title }}</h3>
                        @if($item->last_chapter_at)
                            <p class="manga-card__chapter">{{ timeAgo($item->last_chapter_at) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Manga Section -->
    @if($manga->isNotEmpty())
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">🇯🇵 Manga</h2>
            <a href="{{ route('type', 'manga') }}" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            @foreach($manga as $item)
                <a href="{{ route('manga.detail', $item->slug) }}" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="{{ resolveMedia($item->cover_path) }}" alt="{{ $item->title }}" loading="lazy">
                        @if($item->status === 'completed')
                            <span class="manga-card__badge manga-card__badge--complete">Complete</span>
                        @endif
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title">{{ $item->title }}</h3>
                        @if($item->last_chapter_at)
                            <p class="manga-card__chapter">{{ timeAgo($item->last_chapter_at) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

</div>

<style>
.home-page {
    padding: var(--spacing-xl) 0;
}

.home-section {
    max-width: 1400px;
    margin: 0 auto var(--spacing-2xl);
    padding: 0 var(--spacing-md);
}

.home-section__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.home-section__title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
}

.home-section__link {
    color: var(--primary);
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    transition: all var(--transition-fast);
}

.home-section__link:hover {
    color: var(--primary-dark);
    transform: translateX(4px);
}

.manga-card__badge--update {
    background: var(--success);
    color: white;
}

.manga-card__badge--complete {
    background: var(--warning);
    color: white;
}

@media (max-width: 768px) {
    .home-section__title {
        font-size: 1.25rem;
    }
}
</style>
@endsection
