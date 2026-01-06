<?php if($paginator->hasPages()): ?>
    <nav class="pagination-wrapper" role="navigation" aria-label="Pagination Navigation">
        <div class="pagination-info">
            <p class="pagination-info__text">
                Menampilkan 
                <span class="font-semibold"><?php echo e($paginator->firstItem()); ?></span>
                -
                <span class="font-semibold"><?php echo e($paginator->lastItem()); ?></span>
                dari
                <span class="font-semibold"><?php echo e($paginator->total()); ?></span>
                hasil
            </p>
        </div>

        <div class="pagination-controls">
            
            <?php if($paginator->onFirstPage()): ?>
                <span class="pagination-btn pagination-btn--disabled" aria-disabled="true">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="pagination-btn__text">Sebelumnya</span>
                </span>
            <?php else: ?>
                <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="pagination-btn pagination-btn--prev" rel="prev">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="pagination-btn__text">Sebelumnya</span>
                </a>
            <?php endif; ?>

            
            <div class="pagination-numbers">
                <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    
                    <?php if(is_string($element)): ?>
                        <span class="pagination-dots" aria-disabled="true"><?php echo e($element); ?></span>
                    <?php endif; ?>

                    
                    <?php if(is_array($element)): ?>
                        <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $paginator->currentPage()): ?>
                                <span class="pagination-number pagination-number--active" aria-current="page"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>" class="pagination-number"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="pagination-btn pagination-btn--next" rel="next">
                    <span class="pagination-btn__text">Selanjutnya</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            <?php else: ?>
                <span class="pagination-btn pagination-btn--disabled" aria-disabled="true">
                    <span class="pagination-btn__text">Selanjutnya</span>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            <?php endif; ?>
        </div>
    </nav>
<?php endif; ?>

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
<?php /**PATH G:\Komik-ID-Laravel\resources\views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>