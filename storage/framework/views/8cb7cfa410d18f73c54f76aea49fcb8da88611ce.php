

<?php $__env->startSection('content'); ?>
<div class="home-page">
    
    <!-- Latest Updates Section -->
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">📅 Update Terbaru</h2>
            <a href="<?php echo e(route('latest')); ?>" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            <?php $__empty_1 = true; $__currentLoopData = $latestUpdates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('manga.detail', $item->slug)); ?>" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="<?php echo e(resolveMedia($item->cover_path)); ?>" alt="<?php echo e($item->title); ?>" loading="lazy">
                        <span class="manga-card__badge manga-card__badge--update">NEW</span>
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title"><?php echo e($item->title); ?></h3>
                        <p class="manga-card__chapter"><?php echo e(timeAgo($item->last_chapter_at)); ?></p>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty-state">Belum ada update terbaru.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Manhwa Section -->
    <?php if($manhwa->isNotEmpty()): ?>
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">🇰🇷 Manhwa</h2>
            <a href="<?php echo e(route('type', 'manhwa')); ?>" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            <?php $__currentLoopData = $manhwa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('manga.detail', $item->slug)); ?>" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="<?php echo e(resolveMedia($item->cover_path)); ?>" alt="<?php echo e($item->title); ?>" loading="lazy">
                        <?php if($item->status === 'completed'): ?>
                            <span class="manga-card__badge manga-card__badge--complete">Complete</span>
                        <?php endif; ?>
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title"><?php echo e($item->title); ?></h3>
                        <?php if($item->last_chapter_at): ?>
                            <p class="manga-card__chapter"><?php echo e(timeAgo($item->last_chapter_at)); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Manhua Section -->
    <?php if($manhua->isNotEmpty()): ?>
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">🇨🇳 Manhua</h2>
            <a href="<?php echo e(route('type', 'manhua')); ?>" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            <?php $__currentLoopData = $manhua; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('manga.detail', $item->slug)); ?>" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="<?php echo e(resolveMedia($item->cover_path)); ?>" alt="<?php echo e($item->title); ?>" loading="lazy">
                        <?php if($item->status === 'completed'): ?>
                            <span class="manga-card__badge manga-card__badge--complete">Complete</span>
                        <?php endif; ?>
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title"><?php echo e($item->title); ?></h3>
                        <?php if($item->last_chapter_at): ?>
                            <p class="manga-card__chapter"><?php echo e(timeAgo($item->last_chapter_at)); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Manga Section -->
    <?php if($manga->isNotEmpty()): ?>
    <section class="home-section">
        <div class="home-section__header">
            <h2 class="home-section__title">🇯🇵 Manga</h2>
            <a href="<?php echo e(route('type', 'manga')); ?>" class="home-section__link">Lihat Semua →</a>
        </div>
        <div class="manga-grid">
            <?php $__currentLoopData = $manga; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('manga.detail', $item->slug)); ?>" class="manga-card">
                    <div class="manga-card__cover">
                        <img src="<?php echo e(resolveMedia($item->cover_path)); ?>" alt="<?php echo e($item->title); ?>" loading="lazy">
                        <?php if($item->status === 'completed'): ?>
                            <span class="manga-card__badge manga-card__badge--complete">Complete</span>
                        <?php endif; ?>
                    </div>
                    <div class="manga-card__info">
                        <h3 class="manga-card__title"><?php echo e($item->title); ?></h3>
                        <?php if($item->last_chapter_at): ?>
                            <p class="manga-card__chapter"><?php echo e(timeAgo($item->last_chapter_at)); ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
    <?php endif; ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Komik-ID-Laravel\resources\views/home.blade.php ENDPATH**/ ?>