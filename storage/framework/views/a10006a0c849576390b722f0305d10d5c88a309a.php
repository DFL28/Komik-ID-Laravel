

<?php $__env->startSection('content'); ?>
<div class="reader-container" id="reader">
    
    <!-- Top Navigation Bar (Fixed & Compact) -->
    <div class="reader-header" id="readerHeader">
        <div class="reader-header__left">
            <a href="<?php echo e(route('manga.detail', $manga->slug)); ?>" class="btn-icon" title="Back to Manga">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="reader-header__title">
                <h1 class="manga-title"><?php echo e($manga->title); ?></h1>
                <span class="chapter-title">Chapter <?php echo e($chapter->chapter_number); ?></span>
            </div>
        </div>
        
        <div class="reader-header__right">
            <div class="nav-group">
                <?php if($prevChapter): ?>
                    <a href="<?php echo e(route('chapter.read', [$manga->slug, $prevChapter->chapter_number])); ?>" class="btn-nav" title="Previous Chapter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                        <span class="d-none-mobile">Prev</span>
                    </a>
                <?php else: ?>
                    <button class="btn-nav disabled" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                <?php endif; ?>
                
                <!-- Custom Chapter Dropdown -->
                <div class="custom-dropdown" id="chapterDropdown">
                    <button class="dropdown-trigger" onclick="toggleChapterDropdown()">
                        <span class="current-chapter">Chapter <?php echo e($chapter->chapter_number); ?></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-arrow">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    
                    <div class="dropdown-menu">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search chapter..." id="chapterSearch" onkeyup="filterChapters()">
                        </div>
                        <div class="dropdown-items custom-scrollbar">
                            <?php $__currentLoopData = $allChapters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('chapter.read', [$manga->slug, $ch->chapter_number])); ?>" 
                                   class="dropdown-item <?php echo e($ch->chapter_number == $chapter->chapter_number ? 'active' : ''); ?>"
                                   data-val="<?php echo e($ch->chapter_number); ?>">
                                    Chapter <?php echo e($ch->chapter_number); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <?php if($nextChapter): ?>
                    <a href="<?php echo e(route('chapter.read', [$manga->slug, $nextChapter->chapter_number])); ?>" class="btn-nav" title="Next Chapter">
                        <span class="d-none-mobile">Next</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                    </a>
                <?php else: ?>
                    <button class="btn-nav disabled" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                <?php endif; ?>
            </div>

            <button class="btn-icon" onclick="toggleSettings()" title="Settings">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Progress Indicator -->
    <div class="reader-progress" id="progressBar">
        <div class="reader-progress__fill" id="progressFill"></div>
    </div>
    
    <!-- Images Container -->
    <div class="reader-content" id="readerImages" onclick="toggleHeader()">
        <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $imageUrl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="page-container" data-page="<?php echo e($index + 1); ?>">
                <img src="<?php echo e($imageUrl); ?>" 
                     alt="Page <?php echo e($index + 1); ?>" 
                     loading="lazy"
                     class="page-image"
                     onerror="this.src='/images/image-error.png'; this.parentElement.classList.add('error')">
                 <div class="page-number">Page <?php echo e($index + 1); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state-reader">
                <div class="empty-icon">⚠️</div>
                <h2>No images found</h2>
                <p>Could not load images for this chapter.</p>
                <div class="empty-actions">
                     <button onclick="location.reload()" class="btn btn--primary">Retry Load</button>
                     <a href="<?php echo e(route('manga.detail', $manga->slug)); ?>" class="btn btn--outline">Back to Manga</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bottom Navigation (Large Buttons) -->
    <div class="reader-footer">
        <div class="nav-buttons-large">
            <?php if($prevChapter): ?>
                <a href="<?php echo e(route('chapter.read', [$manga->slug, $prevChapter->chapter_number])); ?>" class="btn-large prev">
                    <span class="label">Previous Chapter</span>
                    <span class="val">Chapter <?php echo e($prevChapter->chapter_number); ?></span>
                </a>
            <?php else: ?>
                <div class="btn-large disabled">
                    <span class="label">First Chapter</span>
                </div>
            <?php endif; ?>

            <?php if($nextChapter): ?>
                <a href="<?php echo e(route('chapter.read', [$manga->slug, $nextChapter->chapter_number])); ?>" class="btn-large next">
                    <span class="label">Next Chapter</span>
                    <span class="val">Chapter <?php echo e($nextChapter->chapter_number); ?></span>
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('manga.detail', $manga->slug)); ?>" class="btn-large finish">
                    <span class="label">Finished</span>
                    <span class="val">Back to Manga</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Settings Panel (Side Drawer) -->
    <div class="settings-drawer" id="settingsDrawer">
        <div class="settings-header">
            <h3>Reader Settings</h3>
            <button class="btn-icon" onclick="toggleSettings()">✕</button>
        </div>
        <div class="settings-body">
            <div class="setting-item">
                <label>Image Fit</label>
                <div class="btn-group">
                    <button class="btn-option active" data-fit="width" onclick="setFit('width')">Fit Width</button>
                    <button class="btn-option" data-fit="height" onclick="setFit('height')">Fit Height</button>
                    <button class="btn-option" data-fit="original" onclick="setFit('original')">Original</button>
                </div>
            </div>
            <div class="setting-item">
                <label>Reading Mode</label>
                <div class="btn-group">
                    <button class="btn-option active" data-mode="webtoon" onclick="setMode('webtoon')">Webtoon (Scroll)</button>
                    <button class="btn-option" data-mode="single" onclick="setMode('single')">Single Page</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating overlay to close settings -->
    <div class="settings-overlay" id="settingsOverlay" onclick="toggleSettings()"></div>
    
</div>

<style>
/* CSS VARIABLES */
:root {
    --header-height: 60px;
    --bg-reader: #121212;
    --bg-header: rgba(18, 18, 18, 0.95);
    --text-main: #ffffff;
    --accent: #00d9ff;
    --accent-hover: #00b3d1;
    --border-light: rgba(255,255,255,0.1);
}

body {
    background-color: var(--bg-reader);
    margin: 0;
    overflow-x: hidden;
}

.reader-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    color: var(--text-main);
}

/* HEADER STYLE */
.reader-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--header-height);
    background: var(--bg-header);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 16px;
    z-index: 1000;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.reader-header.hidden {
    transform: translateY(-100%);
}

.reader-header__left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    overflow: hidden;
}

.reader-header__title {
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.manga-title {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chapter-title {
    font-size: 0.75rem;
    color: #aaaaaa;
}

.reader-header__right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.nav-group {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
    padding: 4px;
    border: 1px solid var(--border-light);
}

.btn-nav {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    color: white;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 4px;
    transition: background 0.2s;
    border: none;
    background: transparent;
    cursor: pointer;
}

.btn-nav:hover:not(.disabled) {
    background: rgba(255,255,255,0.1);
    color: var(--accent);
}

.btn-nav.disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* CUSTOM DROPDOWN */
.custom-dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    color: white;
    border: none;
    border-left: 1px solid var(--border-light);
    border-right: 1px solid var(--border-light);
    padding: 6px 12px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    min-width: 120px;
    justify-content: space-between;
}

.dropdown-trigger:hover {
    background: rgba(255,255,255,0.05);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-top: 8px;
    background: #1e1e1e;
    border: 1px solid #333;
    border-radius: 8px;
    width: 200px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s;
    z-index: 2000;
}

.custom-dropdown.open .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.dropdown-search {
    padding: 8px;
    border-bottom: 1px solid #333;
}

.dropdown-search input {
    width: 100%;
    background: #111;
    border: 1px solid #333;
    padding: 6px 10px;
    border-radius: 4px;
    color: white;
    font-size: 0.8rem;
    outline: none;
}

.dropdown-search input:focus {
    border-color: var(--accent);
}

.dropdown-items {
    max-height: 250px; /* Limit height to approx 6-7 items */
    overflow-y: auto;
}

.dropdown-item {
    display: block;
    padding: 8px 16px;
    color: #ccc;
    text-decoration: none;
    font-size: 0.85rem;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background: #333;
    color: white;
}

.dropdown-item.active {
    background: var(--accent);
    color: black;
    font-weight: 700;
}

/* CUSTOM SCROLLBAR - MODERN & THIN */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #111;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #444;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #666;
}

.btn-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-icon:hover {
    background: rgba(255,255,255,0.1);
}

/* PROGRESS BAR */
.reader-progress {
    position: fixed;
    top: var(--header-height);
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    z-index: 1000;
}

.reader-progress__fill {
    height: 100%;
    background: var(--accent);
    width: 0%;
    transition: width 0.1s;
}

/* CONTENT AREA */
.reader-content {
    margin-top: var(--header-height);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 80vh;
    padding-bottom: 60px;
    background-color: #000;
}

.page-container {
    width: 100%;
    max-width: 850px; /* Optimal reading width */
    margin: 0 auto;
    position: relative;
    display: flex;
    justify-content: center;
    background-color: #000;
}

.page-image {
    max-width: 100%;
    height: auto;
    display: block;
    user-select: none;
}

/* FIT MODES */
.reader-content.fit-height .page-image {
    max-height: 100vh;
    width: auto;
}

.reader-content.fit-original .page-image {
    max-width: none;
}

.reader-content.fit-width .page-image {
    width: 100%;
}

.page-number {
    position: absolute;
    bottom: 0;
    right: 0;
    background: rgba(0,0,0,0.5);
    color: white;
    padding: 2px 6px;
    font-size: 10px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
}

.page-container:hover .page-number {
    opacity: 1;
}

/* FOOTER NAV */
.reader-footer {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px 80px;
    width: 100%;
}

.nav-buttons-large {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.btn-large {
    background: #252525;
    padding: 20px;
    border-radius: 12px;
    text-decoration: none;
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: transform 0.2s, background 0.2s;
    border: 1px solid transparent;
}

.btn-large:hover:not(.disabled) {
    background: #333;
    transform: translateY(-2px);
    border-color: var(--accent);
}

.btn-large.prev .val { color: #aaafff; }
.btn-large.next .val { color: var(--accent); }
.btn-large.finish { background: var(--accent); color: black; }
.btn-large.finish .val { color: black; font-weight: 800; }

.btn-large .label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.7;
    margin-bottom: 4px;
}

.btn-large .val {
    font-size: 1.1rem;
    font-weight: 700;
}

.btn-large.disabled {
    opacity: 0.3;
    cursor: default;
    transform: none;
}

/* SETTINGS DRAWER */
.settings-drawer {
    position: fixed;
    top: 0;
    right: -320px;
    bottom: 0;
    width: 320px;
    background: #1e1e1e;
    z-index: 2000;
    box-shadow: -4px 0 20px rgba(0,0,0,0.5);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 20px;
}

.settings-drawer.open {
    transform: translateX(-320px);
}

.settings-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
}

.settings-drawer.open + .settings-overlay {
    opacity: 1;
    pointer-events: auto;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 15px;
}

.settings-header h3 { margin: 0; font-size: 1.2rem; }

.setting-item {
    margin-bottom: 25px;
}

.setting-item label {
    display: block;
    margin-bottom: 10px;
    font-size: 0.9rem;
    color: #cccccc;
}

.btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.btn-option {
    flex: 1;
    padding: 8px 12px;
    background: transparent;
    border: 1px solid #444;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-option.active {
    background: var(--accent);
    color: black;
    border-color: var(--accent);
    font-weight: 600;
}

/* MOBILE RESPONSIVE */
@media (max-width: 768px) {
    .d-none-mobile { display: none; }
    
    .reader-header__left {
        gap: 8px;
    }
    
    .btn-nav {
         padding: 6px 8px;
    }
    
    .chapter-select {
        max-width: 60px;
    }
    
    .nav-buttons-large {
        grid-template-columns: 1fr;
    }
}

/* EMPTY STATE */
.empty-state-reader {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
    text-align: center;
    color: #666;
}

.empty-icon { font-size: 3rem; margin-bottom: 16px; }
.empty-actions { display: flex; gap: 10px; margin-top: 20px; }
.btn { padding: 10px 20px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
.btn--primary { background: var(--accent); color: black; }
.btn--outline { background: transparent; border: 1px solid #444; color: white; }

</style>

<script>
// State
let headerVisible = true;
let lastScrollY = window.scrollY;

function toggleHeader() {
    headerVisible = !headerVisible;
    const header = document.getElementById('readerHeader');
    
    if (headerVisible) {
        header.classList.remove('hidden');
    } else {
        header.classList.add('hidden');
    }
}

function toggleSettings() {
    document.getElementById('settingsDrawer').classList.toggle('open');
}

// Custom Dropdown Logic
function toggleChapterDropdown() {
    const dropdown = document.getElementById('chapterDropdown');
    dropdown.classList.toggle('open');
    
    // Focus search input when opening
    if(dropdown.classList.contains('open')) {
        setTimeout(() => document.getElementById('chapterSearch').focus(), 100);
        
        // Scroll to active item
        const activeItem = dropdown.querySelector('.dropdown-item.active');
        if(activeItem) {
            activeItem.scrollIntoView({ block: 'center' });
        }
    }
}

// Close Dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('chapterDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});

// Filter Chapters
function filterChapters() {
    const input = document.getElementById('chapterSearch');
    const filter = input.value.toLowerCase();
    const items = document.querySelectorAll('.dropdown-item');
    
    items.forEach(item => {
        const text = item.textContent || item.innerText;
        if (text.toLowerCase().indexOf(filter) > -1) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
}

function navigateToChapter(chapter) {
    if(!chapter) return;
    // Construct simplified url manually or better yet, using proper route handling
    const currentUrl = window.location.href;
    const baseUrl = currentUrl.substring(0, currentUrl.indexOf('/chapter/'));
    window.location.href = `${baseUrl}/chapter/${chapter}`;
}

// Settings Logic
function setFit(mode) {
    const container = document.getElementById('readerImages');
    container.classList.remove('fit-width', 'fit-height', 'fit-original');
    container.classList.add(`fit-${mode}`);
    
    // Update Active Button
    document.querySelectorAll('[data-fit]').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-fit="${mode}"]`).classList.add('active');
    
    // Save preference
    localStorage.setItem('reader_fit', mode);
}

// Auto Hide Header on Scroll Down
window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;
    const header = document.getElementById('readerHeader');
    
    // Update Progress Bar
    const docHeight = document.body.scrollHeight - window.innerHeight;
    const progress = (currentScrollY / docHeight) * 100;
    document.getElementById('progressFill').style.width = progress + '%';
    
    // Header Logic
    if (currentScrollY > lastScrollY && currentScrollY > 100) {
        // Scrolling Down
        if(headerVisible) {
            header.classList.add('hidden');
            headerVisible = false;
        }
    } else if (currentScrollY < lastScrollY) {
        // Scrolling Up
        if(!headerVisible) {
            header.classList.remove('hidden');
            headerVisible = true;
        }
    }
    
    lastScrollY = currentScrollY;
});


// Initialization
document.addEventListener('DOMContentLoaded', () => {
    // Load persisted settings
    const savedFit = localStorage.getItem('reader_fit') || 'width';
    setFit(savedFit);
    
    // Keyboard Navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            const prev = document.querySelector('.btn-large.prev');
            if(prev) prev.click();
        } else if (e.key === 'ArrowRight') {
            const next = document.querySelector('.btn-large.next');
            if(next) next.click();
        }
    });
});

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Komik-ID-Laravel\resources\views/reader.blade.php ENDPATH**/ ?>