

<?php $__env->startSection('content'); ?>
<div class="admin-container">
    <h1>Admin Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Manga</h3>
            <p class="stat-number"><?php echo e($stats['total_manga']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Chapter</h3>
            <p class="stat-number"><?php echo e($stats['total_chapters']); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="stat-number"><?php echo e($stats['total_users']); ?></p>
        </div>
    </div>

    <div class="admin-section">
        <h2>Manga Terbaru</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Chapters</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $stats['recent_manga']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manga): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><a href="<?php echo e(route('manga.detail', $manga->slug)); ?>"><?php echo e($manga->title); ?></a></td>
                        <td><?php echo e($manga->status); ?></td>
                        <td><?php echo e($manga->chapters_count ?? 0); ?></td>
                        <td><?php echo e(timeAgo($manga->updated_at)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <div class="admin-actions">
        <a href="<?php echo e(route('admin.scraper')); ?>" class="btn btn-primary">Kelola Scraper</a>
        <a href="<?php echo e(route('admin.users')); ?>" class="btn btn-secondary">Kelola Users</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['isAdmin' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Komik-ID-Laravel\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>