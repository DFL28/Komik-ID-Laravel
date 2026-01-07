@extends('layouts.app')

@section('content')
<div class="manga-detail-page">
    <div class="manga-detail-container">
        
        <!-- Hero Section: Cover + Info -->
        <div class="manga-hero">
            <div class="manga-hero__cover">
                <img src="{{ resolveMedia($manga->cover_path) }}" 
                     alt="{{ $manga->title }}" 
                     class="manga-hero__img">
                <button class="manga-hero__bookmark {{ $isBookmarked ? 'active' : '' }}" 
                        onclick="toggleBookmark('{{ $manga->slug }}')">
                    <span>🔖</span> {{ $isBookmarked ? 'Bookmarked' : 'Bookmark' }}
                </button>
            </div>
            
            <div class="manga-hero__content">
                <div class="manga-hero__header">
                    <h1 class="manga-hero__title">{{ $manga->title }}</h1>
                    @if($manga->alternative_title)
                        <p class="manga-hero__alt">{{ $manga->alternative_title }}</p>
                    @endif
                </div>
                
                <!-- Genres -->
                @if($manga->genres)
                <div class="manga-genres">
                    @foreach(explode(',', $manga->genres) as $genre)
                        <span class="manga-genre-tag">{{ trim($genre) }}</span>
                    @endforeach
                </div>
                @endif
                
                <!-- Synopsis -->
                <div class="manga-synopsis">
                    <h3 class="manga-synopsis__title">Synopsis {{ $manga->title }}</h3>
                    <p class="manga-synopsis__text">
                        {{ $manga->description ?? 'Belum ada sinopsis tersedia untuk manga ini.' }}
                    </p>
                </div>
                
                <!-- Chapter Action Buttons -->
                <div class="manga-chapter-actions">
                    @if($chapters->isNotEmpty())
                        <a href="{{ route('chapter.read', [$manga->slug, $chapters->first()->chapter_number]) }}" 
                           class="btn-chapter btn-chapter--first">
                            <span class="btn-chapter__label">First Chapter</span>
                            <span class="btn-chapter__number">Chapter {{ $chapters->first()->chapter_number }}</span>
                        </a>
                        <a href="{{ route('chapter.read', [$manga->slug, $chapters->last()->chapter_number]) }}" 
                           class="btn-chapter btn-chapter--new">
                            <span class="btn-chapter__label">New Chapter</span>
                            <span class="btn-chapter__number">Chapter {{ $chapters->last()->chapter_number }}</span>
                        </a>
                    @else
                        <button class="btn-chapter btn-chapter--disabled" disabled>
                            No Chapters Available
                        </button>
                    @endif
                </div>
                
                <!-- Info Sidebar (Desktop) / List (Mobile) -->
                <div class="manga-info">
                    <div class="manga-info__rating">
                        <div class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= round($manga->rating) ? 'star--filled' : '' }}">★</span>
                            @endfor
                        </div>
                        <span class="rating-value">{{ number_format($manga->rating, 1) }}</span>
                    </div>
                    
                    <dl class="manga-info__list">
                        <div class="manga-info__item">
                            <dt>Status</dt>
                            <dd>{{ ucfirst($manga->status) }}</dd>
                        </div>
                        <div class="manga-info__item">
                            <dt>Type</dt>
                            <dd>{{ $manga->type ?? 'Manga' }}</dd>
                        </div>
                        <div class="manga-info__item">
                            <dt>Released</dt>
                            <dd>{{ $manga->created_at->format('Y') }}</dd>
                        </div>
                        <div class="manga-info__item">
                            <dt>Author</dt>
                            <dd>{{ $manga->author ?? 'Unknown' }}</dd>
                        </div>
                        @if($manga->artist)
                        <div class="manga-info__item">
                            <dt>Artist</dt>
                            <dd>{{ $manga->artist }}</dd>
                        </div>
                        @endif
                        <div class="manga-info__item">
                            <dt>Serialization</dt>
                            <dd>{{ $manga->serialization ?? '-' }}</dd>
                        </div>
                        <div class="manga-info__item">
                            <dt>Posted On</dt>
                            <dd>{{ $manga->created_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="manga-info__item">
                            <dt>Updated On</dt>
                            <dd>{{ $manga->updated_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="manga-info__item">
                            <dt>Views</dt>
                            <dd>{{ number_format($manga->views ?? 0) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Chapter List Section -->
        <div class="manga-chapters-section">
            <div class="manga-chapters-section__header">
                <h2>Chapter {{ $manga->title }}</h2>
            </div>
            
            @if($chapters->isEmpty())
                <div class="empty-chapters">
                    <p>Belum ada chapter tersedia untuk manga ini.</p>
                </div>
            @else
                <!-- Chapter Grid (4 columns desktop, 2 mobile) -->
                <div class="chapter-list">
                    @foreach($chapters->reverse() as $chapter)
                        <a href="{{ route('chapter.read', [$manga->slug, $chapter->chapter_number]) }}" 
                           class="chapter-item {{ in_array($chapter->chapter_number, $readChapters) ? 'chapter-item--read' : '' }}">
                            <span class="chapter-item__number">Chapter {{ $chapter->chapter_number }}</span>
                            <span class="chapter-item__date">{{ $chapter->created_at->format('d/m/Y') }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Comments Section -->
        <div class="manga-comments-section" id="komentar">
            <div class="manga-comments-section__header">
                <h2>Komentar <span class="comment-count" id="commentTotal">{{ $commentsCount }}</span></h2>
                <p class="manga-comments-section__subtitle">Tulis komentar kamu tentang komik ini.</p>
            </div>

            <div class="comment-composer">
                @auth
                    <div class="comment-composer__avatar">
                        <img src="{{ resolveMedia(auth()->user()->avatar_path, '/images/avatar-placeholder.png') }}" alt="Avatar">
                    </div>
                    <div class="comment-composer__body">
                        <textarea id="commentInput" class="comment-composer__input" rows="4" maxlength="1000" placeholder="Tulis komentar di sini..."></textarea>
                        <div class="comment-composer__footer">
                            <span class="comment-composer__count" id="commentCount">0/1000</span>
                            <button type="button" class="comment-composer__submit" id="commentSubmit">Kirim</button>
                        </div>
                        <p class="comment-composer__error" id="commentError"></p>
                    </div>
                @else
                    <div class="comment-composer__guest">
                        <p>Kamu harus login dulu untuk komentar.</p>
                        <a class="comment-composer__login" href="{{ route('login') }}">Login</a>
                    </div>
                @endauth
            </div>

            <div class="comment-list" id="commentList">
                @forelse($comments as $comment)
                    <article class="comment-card" data-comment-id="{{ $comment->id }}">
                        <div class="comment-card__avatar">
                            <img src="{{ resolveMedia($comment->user->avatar_path, '/images/avatar-placeholder.png') }}" alt="{{ $comment->user->username }}">
                        </div>
                        <div class="comment-card__body">
                            <div class="comment-card__header">
                                <span class="comment-card__user">{{ $comment->user->username }}</span>
                                <span class="comment-card__time">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="comment-card__content">{!! nl2br(e($comment->content)) !!}</div>
                        </div>
                    </article>

                    @foreach($comment->replies as $reply)
                        <article class="comment-card comment-card--reply" data-comment-id="{{ $reply->id }}">
                            <div class="comment-card__avatar">
                                <img src="{{ resolveMedia($reply->user->avatar_path, '/images/avatar-placeholder.png') }}" alt="{{ $reply->user->username }}">
                            </div>
                            <div class="comment-card__body">
                                <div class="comment-card__header">
                                    <span class="comment-card__user">{{ $reply->user->username }}</span>
                                    <span class="comment-card__time">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="comment-card__content">{!! nl2br(e($reply->content)) !!}</div>
                            </div>
                        </article>
                    @endforeach
                @empty
                    <p class="comment-empty" id="commentEmpty">Belum ada komentar.</p>
                @endforelse
            </div>
        </div>
        
        <!-- Related Series Section -->
        @if(isset($relatedManga) && $relatedManga->isNotEmpty())
        <div class="manga-related-section">
            <div class="manga-related-section__header">
                <h2>Related Series</h2>
            </div>
            
            <div class="related-grid">
                @foreach($relatedManga->take(7) as $related)
                    <a href="{{ route('manga.detail', $related->slug) }}" class="related-card">
                        <div class="related-card__cover">
                            <img src="{{ resolveMedia($related->cover_path) }}" alt="{{ $related->title }}">
                        </div>
                        <div class="related-card__info">
                            <h3 class="related-card__title">{{ $related->title }}</h3>
                            <div class="related-card__rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="star {{ $i <= round($related->rating) ? 'star--filled' : '' }}">★</span>
                                @endfor
                                <span>{{ number_format($related->rating, 1) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
        
    </div>
</div>

<style>
/* ============================================
   MANGA DETAIL PAGE - Responsive Layout
   ============================================ */
.manga-detail-page {
    min-height: 100vh;
    padding: var(--spacing-xl) 0;
    background: var(--bg-primary);
}

.manga-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

/* ============================================
   HERO SECTION - Landscape (Desktop) / Portrait (Mobile)
   ============================================ */
.manga-hero {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: var(--spacing-2xl);
    margin-bottom: var(--spacing-2xl);
    background: var(--bg-secondary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
}

.manga-hero__cover {
    position: relative;
}

.manga-hero__img {
    width: 100%;
    aspect-ratio: 3/4;
    object-fit: cover;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}

.manga-hero__bookmark {
    width: 100%;
    margin-top: var(--spacing-md);
    padding: var(--spacing-md);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
}

.manga-hero__bookmark:hover {
    background: var(--primary);
    border-color: var(--primary);
    transform: translateY(-2px);
}

.manga-hero__bookmark.active {
    background: var(--success);
    border-color: var(--success);
}

.manga-hero__content {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.manga-hero__title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-xs);
    line-height: 1.2;
}

.manga-hero__alt {
    font-size: 1rem;
    color: var(--text-tertiary);
    margin: 0;
}

/* Genres */
.manga-genres {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.manga-genre-tag {
    padding: var(--spacing-xs) var(--spacing-md);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    color: var(--text-secondary);
    transition: all var(--transition-fast);
}

.manga-genre-tag:hover {
    background: var(--primary-soft);
    border-color: var(--primary);
    color: var(--primary);
}

/* Synopsis */
.manga-synopsis__title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.manga-synopsis__text {
    font-size: 0.95rem;
    line-height: 1.7;
    color: var(--text-secondary);
    margin: 0;
}

/* Chapter Action Buttons */
.manga-chapter-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
}

.btn-chapter {
    padding: var(--spacing-lg) var(--spacing-xl);
    border-radius: var(--radius-md);
    border: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-xs);
    text-decoration: none;
    transition: all var(--transition-fast);
    font-weight: 600;
}

.btn-chapter--first {
    background: var(--bg-elevated);
    border: 1px solid var(--border-medium);
    color: var(--text-primary);
}

.btn-chapter--new {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-chapter:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn-chapter__label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-chapter__number {
    font-size: 1.1rem;
}

/* Info Sidebar */
.manga-info {
    padding: var(--spacing-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
}

.manga-info__rating {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid var(--border-subtle);
}

.rating-stars {
    display: flex;
    gap: var(--spacing-xs);
}

.star {
    color: var(--text-tertiary);
    font-size: 1.1rem;
}

.star--filled {
    color: #F5C518;
}

.rating-value {
    font-weight: 700;
    color: var(--text-primary);
}

.manga-info__list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    margin: 0;
}

.manga-info__item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--border-subtle);
}

.manga-info__item:last-child {
    border-bottom: none;
}

.manga-info__item dt {
    font-size: 0.85rem;
    color: var(--text-tertiary);
    font-weight: 500;
}

.manga-info__item dd {
    font-size: 0.9rem;
    color: var(--text-primary);
    font-weight: 600;
    margin: 0;
    text-align: right;
}

/* ============================================
   CHAPTER LIST SECTION
   ============================================ */
.manga-chapters-section {
    margin-bottom: var(--spacing-2xl);
    background: var(--bg-secondary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
}

.manga-chapters-section__header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-lg);
}

.chapter-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-md);
    max-height: 520px;
    overflow-y: auto;
    padding-right: var(--spacing-sm);
}

.chapter-list::-webkit-scrollbar {
    width: 8px;
}

.chapter-list::-webkit-scrollbar-track {
    background: var(--bg-tertiary);
    border-radius: 999px;
}

.chapter-list::-webkit-scrollbar-thumb {
    background: var(--border-medium);
    border-radius: 999px;
}

.chapter-list::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}

.chapter-item {
    padding: var(--spacing-md) var(--spacing-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    text-decoration: none;
    transition: all var(--transition-fast);
}

.chapter-item:hover {
    background: var(--bg-elevated);
    border-color: var(--primary);
    transform: translateY(-2px);
}

.chapter-item--read {
    opacity: 0.6;
}

.chapter-item__number {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95rem;
}

.chapter-item__date {
    font-size: 0.8rem;
    color: var(--text-tertiary);
}

/* ============================================
   COMMENTS SECTION
   ============================================ */
.manga-comments-section {
    margin-bottom: var(--spacing-2xl);
    background: var(--bg-secondary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-xl);
    padding: var(--spacing-2xl);
}

.manga-comments-section__header {
    margin-bottom: var(--spacing-lg);
}

.manga-comments-section__header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-xs);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.manga-comments-section__subtitle {
    margin: 0;
    color: var(--text-tertiary);
    font-size: 0.95rem;
}

.comment-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    padding: 0 var(--spacing-sm);
    height: 28px;
    border-radius: 999px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.comment-composer {
    display: grid;
    grid-template-columns: 48px 1fr;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
}

.comment-composer__avatar img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--border-medium);
}

.comment-composer__input {
    width: 100%;
    background: var(--bg-primary);
    border: 1px solid var(--border-medium);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    padding: var(--spacing-md);
    resize: vertical;
    min-height: 120px;
}

.comment-composer__input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}

.comment-composer__footer {
    margin-top: var(--spacing-sm);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.comment-composer__count {
    font-size: 0.85rem;
    color: var(--text-tertiary);
}

.comment-composer__submit {
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-md);
    border: none;
    background: var(--primary);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.comment-composer__submit:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
}

.comment-composer__submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.comment-composer__error {
    margin-top: var(--spacing-sm);
    color: var(--danger);
    font-size: 0.85rem;
    min-height: 1.2em;
}

.comment-composer__guest {
    width: 100%;
    padding: var(--spacing-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-md);
}

.comment-composer__login {
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--radius-md);
    background: var(--primary);
    color: white;
    font-weight: 600;
    text-decoration: none;
}

.comment-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
    max-height: 520px;
    overflow-y: auto;
    padding-right: var(--spacing-sm);
}

.comment-list::-webkit-scrollbar {
    width: 8px;
}

.comment-list::-webkit-scrollbar-track {
    background: var(--bg-tertiary);
    border-radius: 999px;
}

.comment-list::-webkit-scrollbar-thumb {
    background: var(--border-medium);
    border-radius: 999px;
}

.comment-list::-webkit-scrollbar-thumb:hover {
    background: var(--primary);
}

.comment-card {
    display: grid;
    grid-template-columns: 40px 1fr;
    gap: var(--spacing-md);
    padding: var(--spacing-md) var(--spacing-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
}

.comment-card--reply {
    margin-left: 44px;
    background: var(--bg-elevated);
}

.comment-card__avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--border-medium);
}

.comment-card__header {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xs);
}

.comment-card__user {
    font-weight: 600;
    color: var(--text-primary);
}

.comment-card__time {
    font-size: 0.8rem;
    color: var(--text-tertiary);
}

.comment-card__content {
    color: var(--text-secondary);
    line-height: 1.6;
    white-space: pre-wrap;
}

.comment-empty {
    color: var(--text-tertiary);
    margin: 0;
}

/* ============================================
   RELATED SERIES SECTION
   ============================================ */
.manga-related-section {
    margin-bottom: var(--spacing-2xl);
}

.manga-related-section__header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 var(--spacing-lg);
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: var(--spacing-md);
}

.related-card {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    text-decoration: none;
    transition: transform var(--transition-fast);
}

.related-card:hover {
    transform: translateY(-4px);
}

.related-card__cover {
    position: relative;
    aspect-ratio: 3/4;
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.related-card__cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-card__title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-card__rating {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.8rem;
    color: var(--text-tertiary);
}

.related-card__rating .star {
    font-size: 0.9rem;
}

/* ============================================
   MOBILE RESPONSIVE - Portrait Layout
   ============================================ */
@media (max-width: 768px) {
    .manga-detail-container {
        padding: 0 var(--spacing-sm);
    }
    
    .manga-hero {
        grid-template-columns: 1fr;
        padding: var(--spacing-lg);
        gap: var(--spacing-lg);
    }
    
    .manga-hero__cover {
        max-width: 200px;
        margin: 0 auto;
    }
    
    .manga-hero__title {
        font-size: 1.5rem;
    }
    
    .manga-chapter-actions {
        grid-template-columns: 1fr;
    }
    
    .manga-chapters-section {
        padding: var(--spacing-lg);
    }
    
    .chapter-list {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-sm);
        max-height: 360px;
    }

    .manga-comments-section {
        padding: var(--spacing-lg);
    }

    .comment-composer {
        grid-template-columns: 1fr;
    }

    .comment-composer__avatar {
        display: none;
    }

    .comment-list {
        max-height: 420px;
    }

    .comment-card--reply {
        margin-left: 20px;
    }
    
    .chapter-item {
        padding: var(--spacing-sm) var(--spacing-md);
    }
    
    .related-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-sm);
    }
}

@media (max-width: 480px) {
    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function toggleBookmark(slug) {
    @auth
        fetch(`/manga/${slug}/bookmark`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                return Promise.reject(new Error('Unauthorized'));
            }
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || 'Request failed');
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data || !data.success) {
                console.warn('Bookmark update failed', data);
                return;
            }
            const btn = document.querySelector('.manga-hero__bookmark');
            if (data.bookmarked) {
                btn.classList.add('active');
                btn.textContent = 'Bookmarked';
            } else {
                btn.classList.remove('active');
                btn.textContent = 'Bookmark';
            }
        })
        .catch(error => {
            console.warn('Bookmark request failed', error);
        });
    @else
        window.location.href = '{{ route("login") }}';
    @endauth
}

const commentInput = document.getElementById('commentInput');
const commentCount = document.getElementById('commentCount');
const commentSubmit = document.getElementById('commentSubmit');
const commentError = document.getElementById('commentError');
const commentList = document.getElementById('commentList');
const commentTotal = document.getElementById('commentTotal');
const commentEmpty = document.getElementById('commentEmpty');
const commentMaxLength = 1000;
const storageBase = '{{ asset('storage') }}';
const avatarFallback = '{{ asset('images/avatar-placeholder.png') }}';

function getAvatarUrl(path) {
    if (!path) {
        return avatarFallback;
    }
    if (path.startsWith('http')) {
        return path;
    }
    return `${storageBase}/${path}`;
}

function updateCommentCount() {
    if (!commentInput || !commentCount) {
        return;
    }
    commentCount.textContent = `${commentInput.value.length}/${commentMaxLength}`;
}

function buildCommentCard(comment) {
    const card = document.createElement('article');
    card.className = 'comment-card';
    card.dataset.commentId = comment.id;

    const avatarWrap = document.createElement('div');
    avatarWrap.className = 'comment-card__avatar';
    const avatarImg = document.createElement('img');
    avatarImg.src = getAvatarUrl(comment.user?.avatar_path);
    avatarImg.alt = comment.user?.username || 'User';
    avatarWrap.appendChild(avatarImg);

    const body = document.createElement('div');
    body.className = 'comment-card__body';

    const header = document.createElement('div');
    header.className = 'comment-card__header';

    const user = document.createElement('span');
    user.className = 'comment-card__user';
    user.textContent = comment.user?.username || 'User';

    const time = document.createElement('span');
    time.className = 'comment-card__time';
    time.textContent = 'baru saja';

    header.appendChild(user);
    header.appendChild(time);

    const content = document.createElement('div');
    content.className = 'comment-card__content';
    content.textContent = comment.content || '';

    body.appendChild(header);
    body.appendChild(content);

    card.appendChild(avatarWrap);
    card.appendChild(body);

    return card;
}

if (commentInput) {
    updateCommentCount();
    commentInput.addEventListener('input', updateCommentCount);
}

if (commentSubmit && commentInput) {
    commentSubmit.addEventListener('click', () => {
        const content = commentInput.value.trim();
        if (!content) {
            commentError.textContent = 'Komentar tidak boleh kosong.';
            return;
        }
        commentError.textContent = '';
        commentSubmit.disabled = true;

        fetch('/api/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                manga_id: {{ $manga->id }},
                content: content
            })
        })
        .then(response => {
            if (response.status === 401) {
                window.location.href = '{{ route("login") }}';
                return Promise.reject(new Error('Unauthorized'));
            }
            if (!response.ok) {
                return response.text().then(text => {
                    let message = text || 'Request failed';
                    try {
                        const data = JSON.parse(text);
                        message = data.message || data.error || message;
                    } catch (error) {
                        // ignore JSON parse errors
                    }
                    throw new Error(message);
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data || !data.success) {
                throw new Error('Request failed');
            }
            const card = buildCommentCard(data.comment);
            if (commentEmpty) {
                commentEmpty.remove();
            }
            commentList.prepend(card);
            commentInput.value = '';
            updateCommentCount();
            if (commentTotal) {
                const total = parseInt(commentTotal.textContent || '0', 10);
                commentTotal.textContent = total + 1;
            }
        })
        .catch(error => {
            console.warn('Comment request failed', error);
            const message = error && error.message ? error.message : 'Gagal mengirim komentar. Coba lagi.';
            commentError.textContent = message;
        })
        .finally(() => {
            commentSubmit.disabled = false;
        });
    });
}
</script>
@endsection

