<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta name="theme-color" content="#0a0a0a">
    <title><?php echo e($title ?? 'Komik-ID'); ?> · Komik-ID - Situs Baca Manga Bahasa Indonesia</title>
    <meta name="description" content="Komik-ID - Situs baca manga, manhwa, dan manhua bahasa Indonesia terlengkap dan gratis">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?php echo e(asset('css/main.css')); ?>?v=8.0">
    <?php if(isset($isReader) && $isReader): ?>
        <link rel="stylesheet" href="<?php echo e(asset('css/reader.css')); ?>?v=7.0">
    <?php endif; ?>
    <?php if(isset($isAdmin) && $isAdmin): ?>
        <link rel="stylesheet" href="<?php echo e(asset('css/admin.css')); ?>?v=6.0">
    <?php endif; ?>
</head>
<body data-logged="<?php echo e(auth()->check() ? 'true' : 'false'); ?>" 
      data-admin="<?php echo e(auth()->check() && auth()->user()->is_admin ? 'true' : 'false'); ?>"
      <?php if(isset($isReader) && $isReader): ?> class="reader-body" <?php endif; ?>>
    
    <div class="app-shell<?php echo e(isset($isReader) && $isReader ? ' reader-shell' : ''); ?>">
        <?php if(!isset($isReader) || !$isReader): ?>
        <header class="navbar">
            <div class="brand">
                <span class="brand__title">Komik-ID</span>
                <span class="brand__tagline">Baca Manga Gratis</span>
            </div>
            <form class="navbar__search navbar__search--desktop" method="get" action="<?php echo e(route('search')); ?>">
                <input type="text" name="q" value="<?php echo e($searchQuery ?? ''); ?>" placeholder="Cari judul manga..." aria-label="Cari judul">
                <button type="submit" class="icon-button icon-button--ghost" aria-label="Cari">
                    <span class="icon icon-search"></span>
                </button>
            </form>
            <div class="navbar__icons">
                <button class="icon-button icon-button--ghost navbar__search-toggle" id="mobileSearchToggle" aria-label="Cari">
                    <span class="icon icon-search"></span>
                </button>
                <button class="icon-button" id="sidebarToggle" aria-label="Buka menu">
                    <span class="icon icon-menu"></span>
                </button>
            </div>
        </header>
        <?php endif; ?>

        <?php if(!isset($isReader) || !$isReader): ?>
        <div class="mobile-search-panel" id="mobileSearchPanel">
            <form class="mobile-search-form" method="get" action="<?php echo e(route('search')); ?>" id="mobileSearchForm">
                <input type="text" name="q" id="mobileSearchInput" placeholder="Cari judul manga..." aria-label="Cari judul">
                <button type="submit" class="icon-button icon-button--ghost" aria-label="Cari">
                    <span class="icon icon-search"></span>
                </button>
                <button type="button" class="icon-button icon-button--ghost" id="mobileSearchClose" aria-label="Tutup pencarian">&times;</button>
            </form>
            <div class="mobile-search-results" id="mobileSearchResults"></div>
        </div>
        <?php endif; ?>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar__header">
                <?php
                    $avatarUrl = auth()->check() && auth()->user()->avatar_path 
                        ? resolveMedia(auth()->user()->avatar_path) 
                        : asset('images/avatar-placeholder.svg');
                ?>
                <img src="<?php echo e($avatarUrl); ?>" alt="Avatar" class="sidebar__avatar">
                <div class="sidebar__user">
                    <p class="sidebar__username"><?php echo e(auth()->check() ? auth()->user()->username : 'Tamu'); ?></p>
                    <p class="sidebar__status"><?php echo e(auth()->check() ? (auth()->user()->is_admin ? 'Administrator' : 'Pengguna') : 'Belum login'); ?></p>
                </div>
                <a href="<?php echo e(auth()->check() ? route('profile') : route('login')); ?>" class="icon-button icon-button--ghost" title="Pengaturan profil">
                    <span class="icon icon-gear"></span>
                </a>
            </div>
            <div class="sidebar__menu">
                <a class="sidebar__link<?php echo e(request()->is('/') ? ' is-active' : ''); ?>" href="<?php echo e(route('home')); ?>">Home</a>
                <a class="sidebar__link<?php echo e(request()->is('genre*') ? ' is-active' : ''); ?>" href="<?php echo e(route('genre')); ?>">Genre</a>
                <a class="sidebar__link" href="<?php echo e(route('home')); ?>?sort=popular">Populer</a>
                <a class="sidebar__link" href="<?php echo e(route('home')); ?>?sort=latest">Terbaru</a>
                <a class="sidebar__link<?php echo e(request()->is('bookmark*') ? ' is-active' : ''); ?>" href="<?php echo e(route('bookmarks')); ?>">Bookmark</a>
                
                <?php if(auth()->guard()->check()): ?>
                    <a class="sidebar__link" href="<?php echo e(route('profile')); ?>">Profil</a>
                    <?php if(auth()->user()->is_admin): ?>
                        <a class="sidebar__link<?php echo e(request()->is('admin*') ? ' is-active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">Admin Panel</a>
                    <?php endif; ?>
                    <form method="post" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="sidebar__link sidebar__link--button" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="sidebar__link" href="<?php echo e(route('login')); ?>">Login</a>
                <?php endif; ?>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <?php if(!isset($isReader) || !$isReader): ?>
        <main class="app-main">
        <?php else: ?>
        <main class="reader-main">
        <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <script src="<?php echo e(asset('js/app.js')); ?>?v=6.0"></script>
    <?php if(isset($isReader) && $isReader): ?>
        <script src="<?php echo e(asset('js/reader.js')); ?>?v=7.0"></script>
    <?php endif; ?>
    <?php if(isset($isAdmin) && $isAdmin): ?>
        <script src="<?php echo e(asset('js/admin.js')); ?>?v=6.0"></script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH G:\Komik-ID-Laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>