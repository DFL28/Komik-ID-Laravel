

<?php $__env->startSection('content'); ?>
<div class="genre-page">
    <div class="genre-header">
        <h1 class="genre-header__title">📚 Browse by Genre</h1>
        <p class="genre-header__subtitle">
            <?php if($genre): ?>
                Showing <?php echo e($manga->total()); ?> titles in <strong><?php echo e($genre); ?></strong>
            <?php else: ?>
                Select a genre to filter
            <?php endif; ?>
        </p>
    </div>
    
    <!-- Genre Filter Pills -->
    <?php if(!empty($genres)): ?>
    <div class="genre-filter">
        <a href="<?php echo e(route('genre')); ?>" 
           class="genre-pill <?php echo e(!$genre ? 'genre-pill--active' : ''); ?>">
            All Genres
        </a>
        <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('genre', ['genre' => $g])); ?>" 
               class="genre-pill <?php echo e($genre === $g ? 'genre-pill--active' : ''); ?>">
                <?php echo e($g); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>
    
    <!-- Manga Grid -->
    <div class="manga-grid">
        <?php $__empty_1 = true; $__currentLoopData = $manga; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('manga.detail', $item->slug)); ?>" class="manga-card">
                <div class="manga-card__cover">
                    <img src="<?php echo e(resolveMedia($item->cover_path)); ?>" alt="<?php echo e($item->title); ?>" loading="lazy">
                    <?php if($item->status === 'completed'): ?>
                        <span class="manga-card__badge">Complete</span>
                    <?php endif; ?>
                </div>
                <div class="manga-card__info">
                    <h3 class="manga-card__title"><?php echo e($item->title); ?></h3>
                    <?php if($item->genres): ?>
                        <p class="manga-card__genres"><?php echo e(Str::limit($item->genres, 30)); ?></p>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state">
                <p>Tidak ada manga untuk genre <?php echo e($genre ?? 'ini'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <div class="pagination">
        <?php echo e($manga->appends(['genre' => $genre])->links()); ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Komik-ID-Laravel\resources\views/genre.blade.php ENDPATH**/ ?>