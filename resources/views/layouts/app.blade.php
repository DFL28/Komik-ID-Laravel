<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta name="theme-color" content="#0a0a0a">
    <title>{{ $title ?? 'Komik-ID' }} · Komik-ID - Situs Baca Manga Bahasa Indonesia</title>
    <meta name="description" content="Komik-ID - Situs baca manga, manhwa, dan manhua bahasa Indonesia terlengkap dan gratis">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}?v=8.0">
    @if(isset($isReader) && $isReader)
        <link rel="stylesheet" href="{{ asset('css/reader.css') }}?v=7.0">
    @endif
    @if(isset($isAdmin) && $isAdmin)
        <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v=6.0">
    @endif
</head>
<body data-logged="{{ auth()->check() ? 'true' : 'false' }}" 
      data-admin="{{ auth()->check() && auth()->user()->is_admin ? 'true' : 'false' }}"
      @if(isset($isReader) && $isReader) class="reader-body" @endif>
    
    <div class="app-shell{{ isset($isReader) && $isReader ? ' reader-shell' : '' }}">
        @if(!isset($isReader) || !$isReader)
        <header class="navbar">
            <div class="brand">
                <span class="brand__title">Komik-ID</span>
                <span class="brand__tagline">Baca Manga Gratis</span>
            </div>
            <form class="navbar__search navbar__search--desktop" method="get" action="{{ route('search') }}">
                <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Cari judul manga..." aria-label="Cari judul">
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
        @endif

        @if(!isset($isReader) || !$isReader)
        <div class="mobile-search-panel" id="mobileSearchPanel">
            <form class="mobile-search-form" method="get" action="{{ route('search') }}" id="mobileSearchForm">
                <input type="text" name="q" id="mobileSearchInput" placeholder="Cari judul manga..." aria-label="Cari judul">
                <button type="submit" class="icon-button icon-button--ghost" aria-label="Cari">
                    <span class="icon icon-search"></span>
                </button>
                <button type="button" class="icon-button icon-button--ghost" id="mobileSearchClose" aria-label="Tutup pencarian">&times;</button>
            </form>
            <div class="mobile-search-results" id="mobileSearchResults"></div>
        </div>
        @endif

        <aside class="sidebar" id="sidebar">
            <div class="sidebar__header">
                @php
                    $avatarUrl = auth()->check() && auth()->user()->avatar_path 
                        ? resolveMedia(auth()->user()->avatar_path) 
                        : asset('images/avatar-placeholder.svg');
                @endphp
                <img src="{{ $avatarUrl }}" alt="Avatar" class="sidebar__avatar">
                <div class="sidebar__user">
                    <p class="sidebar__username">{{ auth()->check() ? auth()->user()->username : 'Tamu' }}</p>
                    <p class="sidebar__status">{{ auth()->check() ? (auth()->user()->is_admin ? 'Administrator' : 'Pengguna') : 'Belum login' }}</p>
                </div>
                <a href="{{ auth()->check() ? route('profile') : route('login') }}" class="icon-button icon-button--ghost" title="Pengaturan profil">
                    <span class="icon icon-gear"></span>
                </a>
            </div>
            <div class="sidebar__menu">
                <a class="sidebar__link{{ request()->is('/') ? ' is-active' : '' }}" href="{{ route('home') }}">Home</a>
                <a class="sidebar__link{{ request()->is('genre*') ? ' is-active' : '' }}" href="{{ route('genre') }}">Genre</a>
                <a class="sidebar__link" href="{{ route('home') }}?sort=popular">Populer</a>
                <a class="sidebar__link" href="{{ route('home') }}?sort=latest">Terbaru</a>
                <a class="sidebar__link{{ request()->is('bookmark*') ? ' is-active' : '' }}" href="{{ route('bookmarks') }}">Bookmark</a>
                
                @auth
                    <a class="sidebar__link" href="{{ route('profile') }}">Profil</a>
                    @if(auth()->user()->is_admin)
                        <a class="sidebar__link{{ request()->is('admin*') ? ' is-active' : '' }}" href="{{ route('admin.dashboard') }}">Admin Panel</a>
                    @endif
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="sidebar__link sidebar__link--button" type="submit">Logout</button>
                    </form>
                @else
                    <a class="sidebar__link" href="{{ route('login') }}">Login</a>
                @endauth
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        @if(!isset($isReader) || !$isReader)
        <main class="app-main">
        @else
        <main class="reader-main">
        @endif
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/app.js') }}?v=6.0"></script>
    @if(isset($isReader) && $isReader)
        <script src="{{ asset('js/reader.js') }}?v=7.0"></script>
    @endif
    @if(isset($isAdmin) && $isAdmin)
        <script src="{{ asset('js/admin.js') }}?v=6.0"></script>
    @endif
</body>
</html>
