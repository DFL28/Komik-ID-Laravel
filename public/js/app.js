(function () {
  const body = document.body;
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  const mobileSearchPanel = document.getElementById('mobileSearchPanel');
  const mobileSearchToggle = document.getElementById('mobileSearchToggle');
  const mobileSearchClose = document.getElementById('mobileSearchClose');
  const mobileSearchInput = document.getElementById('mobileSearchInput');
  const mobileSearchResults = document.getElementById('mobileSearchResults');
  const readerSidebarToggle = document.getElementById('readerSidebarToggle');

  const toggleSidebar = (open) => {
    if (!sidebar) return;
    const shouldOpen = typeof open === 'boolean' ? open : !sidebar.classList.contains('is-open');
    sidebar.classList.toggle('is-open', shouldOpen);
    sidebarOverlay && sidebarOverlay.classList.toggle('is-visible', shouldOpen);
  };

  const toggleMobileSearch = (open) => {
    if (!mobileSearchPanel) return;
    // Hanya buka/tutup saat tombol diklik, tidak saat scroll
    const shouldOpen = typeof open === 'boolean' ? open : !mobileSearchPanel.classList.contains('is-visible');
    mobileSearchPanel.classList.toggle('is-visible', shouldOpen);
    document.body.classList.toggle('mobile-search-open', shouldOpen);
    if (shouldOpen) {
      mobileSearchInput && setTimeout(() => mobileSearchInput.focus(), 80);
    } else {
      mobileSearchInput && (mobileSearchInput.value = '');
      renderMobileSearchResults([]);
      document.body.classList.remove('mobile-search-results-active');
    }
  };
  
  // Pastikan navbar global selalu visible - tidak ada auto-hide
  // Hanya berlaku untuk navbar global, bukan reader-navbar
  const navbar = document.querySelector('.navbar:not(.reader-navbar)');
  if (navbar) {
    // Hapus class is-hidden jika ada
    navbar.classList.remove('is-hidden');
    // Pastikan navbar selalu visible dengan inline style (highest priority)
    navbar.style.setProperty('transform', 'none', 'important');
    navbar.style.setProperty('opacity', '1', 'important');
    navbar.style.setProperty('visibility', 'visible', 'important');
    navbar.style.setProperty('display', 'flex', 'important');
    
    // Monitor dan prevent class is-hidden dari ditambahkan
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          if (navbar.classList.contains('is-hidden')) {
            navbar.classList.remove('is-hidden');
            navbar.style.setProperty('transform', 'none', 'important');
            navbar.style.setProperty('opacity', '1', 'important');
            navbar.style.setProperty('visibility', 'visible', 'important');
          }
        }
      });
    });
    observer.observe(navbar, { attributes: true, attributeFilter: ['class'] });
    
    // Pastikan tidak ada event listener scroll yang mempengaruhi navbar global
    // Hapus semua event listener scroll yang mungkin menambahkan is-hidden
    const originalAddEventListener = window.addEventListener;
    window.addEventListener = function(type, listener, options) {
      if (type === 'scroll' && listener && typeof listener.toString === 'function') {
        const fnString = listener.toString();
        const targetsReader = fnString.includes('readerNavbar') || fnString.includes('reader-navbar');
        if (fnString.includes('is-hidden') && !targetsReader) {
          return;
        }
      }
      return originalAddEventListener.call(this, type, listener, options);
    };
  }
  
  // Pastikan mobile search tidak terbuka otomatis saat scroll
  // Mobile search hanya terbuka saat tombol search diklik

  sidebarToggle && sidebarToggle.addEventListener('click', () => toggleSidebar(true));
  readerSidebarToggle && readerSidebarToggle.addEventListener('click', () => toggleSidebar(true));
  sidebarOverlay && sidebarOverlay.addEventListener('click', () => toggleSidebar(false));
  mobileSearchToggle && mobileSearchToggle.addEventListener('click', () => toggleMobileSearch());
  mobileSearchClose && mobileSearchClose.addEventListener('click', () => toggleMobileSearch(false));

  document.addEventListener('keyup', (event) => {
    if (event.key === 'Escape') {
      toggleSidebar(false);
      toggleMobileSearch(false);
    }
  });

  const isLoggedIn = body.dataset.logged === 'true';
  const isAdminUser = body.dataset.admin === 'true';
  const avatarPlaceholder = body.dataset.avatar;
  const isReaderPage = body.classList.contains('reader-body');

  // Wait for DOM to be ready
  const commentForm = document.querySelector('.comment-form');
  const commentList = document.querySelector('.comment-list');
  const counter = commentForm ? commentForm.querySelector('.comment-form__counter') : null;
  const loadMoreBtn = document.getElementById('loadMoreComments');
  const commentsMeta = {
    loaded: commentForm ? Number(loadMoreBtn?.dataset.loaded || commentList?.children.length || 0) : 0,
    total: commentForm ? Number(loadMoreBtn?.dataset.total || commentList?.children.length || 0) : 0,
    page: 1
  };

  const updateCounter = () => {
    if (!commentForm || !counter) return;
    const textarea = commentForm.querySelector('textarea');
    const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
    counter.textContent = `${words} / 1 Kata`;
  };

  commentForm && commentForm.addEventListener('input', updateCounter);
  commentForm && updateCounter();

  const resolveMedia = (path) => {
    if (!path) return avatarPlaceholder;
    if (path.startsWith('http')) return path;
    return `/media/${path}`;
  };

  const buildCommentNode = (comment) => {
    if (!comment || !comment.id) {
      console.error('Invalid comment data:', comment);
      return null;
    }
    const li = document.createElement('li');
    li.className = 'comment';
    li.dataset.commentId = comment.id;
    const avatar = resolveMedia(comment.avatar_path);
    const deleteButton = isAdminUser ? '<button class="comment__icon comment__icon--danger" type="button" aria-label="Hapus" data-action="delete">🗑</button>' : '';
    li.innerHTML = `
      <img src="${avatar}" class="comment__avatar" alt="avatar">
      <div class="comment__body">
        <div class="comment__meta">
          <div class="comment__meta-text">
            <span class="comment__author">${comment.username || 'Anonymous'}</span>
            <span class="comment__time">${comment.timeAgo || ''}</span>
          </div>
          <div class="comment__meta-actions">
            <button class="comment__icon" type="button" aria-label="Suka">❤</button>
            <button class="comment__icon" type="button" aria-label="Balas" data-action="reply">💬</button>
            ${deleteButton}
          </div>
        </div>
        <p class="comment__content">${comment.content || ''}</p>
        <ul class="comment__replies"></ul>
      </div>`;
    return li;
  };

  const buildReplyNode = (reply) => {
    if (!reply || !reply.id) return null;
    const li = document.createElement('li');
    li.className = 'comment comment--reply';
    li.dataset.commentId = reply.id;
    const avatar = resolveMedia(reply.avatar_path);
    const deleteButton = isAdminUser ? '<button class="comment__icon comment__icon--danger" type="button" aria-label="Hapus balasan" data-action="delete">🗑</button>' : '';
    li.innerHTML = `
      <img src="${avatar}" class="comment__avatar" alt="avatar">
      <div class="comment__body">
        <div class="comment__meta">
          <div class="comment__meta-text">
            <span class="comment__author">${reply.username || 'Anonymous'}</span>
            <span class="comment__time">${reply.timeAgo || ''}</span>
          </div>
          <div class="comment__meta-actions">
            <button class="comment__icon" type="button" aria-label="Suka">❤</button>
            ${deleteButton}
          </div>
        </div>
        <p class="comment__content">${reply.content || ''}</p>
      </div>`;
    return li;
  };

  const submitComment = async (payload) => {
    try {
      const response = await fetch('/api/comments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify(payload)
      });
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: 'Gagal mengirim komentar' }));
        throw new Error(errorData.message || `Error ${response.status}: Gagal mengirim komentar`);
      }
      
      const data = await response.json();
      if (!data || !data.id) {
        console.error('Invalid response data:', data);
        throw new Error('Response tidak valid dari server');
      }
      
      return data;
    } catch (err) {
      console.error('Error submitting comment:', err);
      throw err;
    }
  };

  commentForm && commentForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!isLoggedIn) {
      window.location.href = '/login';
      return;
    }
    const textarea = commentForm.querySelector('textarea');
    const content = textarea.value.trim();
    if (!content) return;
    const mangaId = commentForm.dataset.mangaId;
    if (!mangaId) {
      alert('Manga ID tidak ditemukan');
      return;
    }
    try {
      const comment = await submitComment({ mangaId, content });
      if (!comment || !comment.id) {
        throw new Error('Gagal membuat komentar: response tidak valid');
      }
      const node = buildCommentNode(comment);
      if (commentList) {
        commentList.prepend(node);
      }
      textarea.value = '';
      updateCounter();
      commentsMeta.loaded += 1;
    } catch (err) {
      alert(err.message || 'Gagal mengirim komentar');
    }
  });

  const createReplyForm = (parentId) => {
    const form = document.createElement('form');
    form.className = 'reply-form';
    form.innerHTML = `
      <textarea placeholder="Balas komentar"></textarea>
      <div class="comment-form__meta">
        <span class="comment-form__counter">0 / 1 Kata</span>
        <button type="submit" class="btn btn--primary">Kirim</button>
      </div>`;
    const textarea = form.querySelector('textarea');
    const counterEl = form.querySelector('.comment-form__counter');
    textarea.addEventListener('input', () => {
      const words = textarea.value.trim() ? textarea.value.trim().split(/\s+/).length : 0;
      counterEl.textContent = `${words} / 1 Kata`;
    });
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!isLoggedIn) {
        window.location.href = '/login';
        return;
      }
      const content = textarea.value.trim();
      if (!content) return;
      try {
        const mangaId = commentForm?.dataset?.mangaId;
        if (!mangaId) {
          alert('Manga ID tidak ditemukan');
          return;
        }
        const comment = await submitComment({ mangaId, content, parentId });
        if (!comment || !comment.id) {
          throw new Error('Gagal membuat balasan: response tidak valid');
        }
        const parentEl = commentList?.querySelector(`[data-comment-id="${parentId}"]`);
        if (!parentEl) {
          throw new Error('Komentar parent tidak ditemukan');
        }
        const repliesEl = parentEl.querySelector('.comment__replies');
        if (!repliesEl) {
          throw new Error('Replies container tidak ditemukan');
        }
        const replyNode = buildReplyNode(comment);
        if (replyNode) {
          repliesEl.appendChild(replyNode);
        }
        form.remove();
      } catch (err) {
        alert(err.message || 'Gagal mengirim balasan');
      }
    });
    return form;
  };

  commentList && commentList.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const action = button.dataset.action;
    const commentEl = button.closest('.comment');
    const commentId = commentEl?.dataset.commentId;
    if (action === 'reply') {
      if (!isLoggedIn) {
        window.location.href = '/login';
        return;
      }
      const repliesContainer = commentEl.querySelector('.comment__replies');
      if (!repliesContainer) return;
      const existing = repliesContainer.querySelector('.reply-form');
      if (existing) {
        existing.remove();
      } else {
        repliesContainer.appendChild(createReplyForm(commentId));
      }
    }
    if (action === 'delete' && commentId) {
      if (!isAdminUser) return;
      if (!confirm('Hapus komentar ini?')) return;
      fetch(`/api/comments/${commentId}`, { method: 'DELETE' })
        .then((resp) => {
          if (!resp.ok) throw new Error('Gagal menghapus');
          commentEl.remove();
        })
        .catch(() => alert('Gagal menghapus komentar'));
    }
    if (action === 'ban') {
      const userId = button.dataset.user;
      fetch(`/admin/users/${userId}/ban`, { method: 'POST' })
        .then((resp) => {
          if (!resp.ok) throw new Error('Gagal ban');
          window.location.reload();
        })
        .catch(() => alert('Gagal memblokir pengguna'));
    }
  });

  // Load more comments button (moved here to avoid duplicate declaration)
  const loadMoreCommentsBtn = document.getElementById('loadMoreComments');
  loadMoreCommentsBtn && loadMoreCommentsBtn.addEventListener('click', async () => {
    if (!commentForm) return;
    const total = Number(loadMoreCommentsBtn.dataset.total);
    commentsMeta.page += 1;
    try {
      const response = await fetch(`/api/comments?mangaId=${commentForm.dataset.mangaId}&page=${commentsMeta.page}`);
      if (!response.ok) throw new Error('Gagal memuat komentar');
      const data = await response.json();
      data.comments.forEach((comment) => {
        const node = buildCommentNode(comment);
        const replies = data.replies[comment.id] || [];
        const repliesEl = node.querySelector('.comment__replies');
        replies.forEach((reply) => {
          const replyNode = buildReplyNode(reply);
          replyNode && repliesEl.appendChild(replyNode);
        });
        commentList.appendChild(node);
      });
      commentsMeta.loaded += data.comments.length;
      if (commentsMeta.loaded >= total || !data.comments.length) {
        loadMoreCommentsBtn.remove();
      } else {
        loadMoreCommentsBtn.dataset.loaded = commentsMeta.loaded;
      }
    } catch (err) {
      console.error(err);
    }
  });

  const liveReader = document.getElementById('liveReader');
  if (liveReader && window.EventSource) {
    const chapterId = liveReader.dataset.chapterId;
    const container = document.getElementById('liveReaderContainer');
    const statusEl = document.getElementById('liveReaderStatus');
    const existing = liveReader.dataset.initial ? JSON.parse(liveReader.dataset.initial) : [];
    const buildSrc = (imagePath) => {
      if (!imagePath) return '';
      if (imagePath.startsWith('http')) return imagePath;
      if (imagePath.startsWith('/media/')) return imagePath;
      return `/media/${imagePath}`;
    };
    const renderImage = (imagePath) => {
      const img = document.createElement('img');
      img.src = buildSrc(imagePath);
      img.loading = 'lazy';
      container.appendChild(img);
    };
    existing.forEach((page) => renderImage(page.image_path));
    const source = new EventSource(`/api/chapters/${chapterId}/stream`);
    source.addEventListener('page', (event) => {
      const payload = JSON.parse(event.data);
      renderImage(payload.imagePath);
      if (statusEl) {
        statusEl.textContent = `Memuat halaman ${payload.pageNumber}...`;
      }
    });
    source.addEventListener('done', () => {
      if (statusEl) {
        statusEl.textContent = 'Semua halaman berhasil dimuat.';
      }
      source.close();
    });
    source.addEventListener('error', () => {
      if (statusEl) {
        statusEl.textContent = 'Gagal memuat beberapa halaman. Silakan refresh.';
      }
      source.close();
    });
  }

  const chapterToggle = document.getElementById('readerChapterToggle');
  const chapterDropdown = document.getElementById('readerChapterDropdown');
  if (chapterToggle && chapterDropdown) {
    const toggleDropdown = (force) => {
      const shouldOpen = typeof force === 'boolean' ? force : !chapterDropdown.classList.contains('is-visible');
      chapterDropdown.classList.toggle('is-visible', shouldOpen);
    };
    chapterToggle.addEventListener('click', () => toggleDropdown());
    document.addEventListener('click', (event) => {
      if (!chapterDropdown.contains(event.target) && !chapterToggle.contains(event.target)) {
        toggleDropdown(false);
      }
    });
  }

  // Navbar tetap visible (tidak auto-hide) - sesuai permintaan user
  // Removed auto-hide functionality

  // Zoom functionality for reader page (desktop only)
  if (isReaderPage && window.innerWidth >= 768) {
    const zoomIn = document.getElementById('zoomIn');
    const zoomOut = document.getElementById('zoomOut');
    const zoomReset = document.getElementById('zoomReset');
    const zoomIndicator = document.getElementById('zoomIndicator');
    const readerImages = document.querySelectorAll('.reader__pages img, .reader__pages-stream img');

    const minZoom = 50;
    const maxZoom = 200;
    const zoomStep = 10;

    // Load saved zoom level from localStorage
    let currentZoom = parseInt(localStorage.getItem('readerZoomLevel')) || 100;

    const applyZoom = (zoom) => {
      currentZoom = Math.max(minZoom, Math.min(maxZoom, zoom));
      readerImages.forEach(img => {
        img.style.transform = `scale(${currentZoom / 100})`;
      });
      if (zoomIndicator) {
        zoomIndicator.textContent = `${currentZoom}%`;
      }
      localStorage.setItem('readerZoomLevel', currentZoom);
    };

    // Apply initial zoom
    applyZoom(currentZoom);

    // Zoom controls
    if (zoomIn) {
      zoomIn.addEventListener('click', () => applyZoom(currentZoom + zoomStep));
    }
    if (zoomOut) {
      zoomOut.addEventListener('click', () => applyZoom(currentZoom - zoomStep));
    }
    if (zoomReset) {
      zoomReset.addEventListener('click', () => applyZoom(100));
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
      if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

      if (e.key === '+' || e.key === '=') {
        e.preventDefault();
        applyZoom(currentZoom + zoomStep);
      } else if (e.key === '-' || e.key === '_') {
        e.preventDefault();
        applyZoom(currentZoom - zoomStep);
      } else if (e.key === '0') {
        e.preventDefault();
        applyZoom(100);
      }
    });
  }

  const escapeHtml = (value = '') => value.replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  }[char]));

  let mobileSearchTimer = null;
  async function performMobileSearch (query) {
    if (!query || query.length < 2) {
      renderMobileSearchResults([]);
      document.body.classList.remove('mobile-search-results-active');
      return;
    }
    try {
      const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
      if (!response.ok) throw new Error('Search failed');
      const data = await response.json();
      renderMobileSearchResults(data.mangas || []);
    } catch (err) {
      console.error(err);
      renderMobileSearchResults([]);
    }
  }

  function renderMobileSearchResults (items) {
    if (!mobileSearchResults) return;
    const query = mobileSearchInput ? mobileSearchInput.value.trim() : '';
    if (!items.length) {
      if (query.length >= 2) {
        mobileSearchResults.innerHTML = `<div class="mobile-search-empty">Tidak ditemukan hasil untuk "<strong>${escapeHtml(query)}</strong>"</div>`;
        mobileSearchResults.classList.add('is-visible');
        document.body.classList.add('mobile-search-results-active');
      } else {
        mobileSearchResults.innerHTML = '';
        mobileSearchResults.classList.remove('is-visible');
        document.body.classList.remove('mobile-search-results-active');
      }
      return;
    }
    const cards = items.map((item) => `
      <article class="manga-card">
        <a href="/manga/${item.slug}" class="manga-card__cover" style="background-image:url('${item.cover ? item.cover : '/images/cover-placeholder.svg'}')">
          <span class="badge badge--country">${item.country || 'ID'}</span>
          ${item.isColor ? '<span class="badge badge--pill">COLOR</span>' : ''}
        </a>
        <div class="manga-card__body">
          <a href="/manga/${item.slug}" class="manga-card__title">${item.title}</a>
          <div class="manga-card__meta">
            <span>Chapter ${item.lastChapter || '-'}</span>
            <span>${item.time || 'Baru'}</span>
          </div>
        </div>
      </article>
    `).join('');
    mobileSearchResults.innerHTML = `<div class="mobile-search-results-grid">${cards}</div>`;
    mobileSearchResults.classList.add('is-visible');
    document.body.classList.add('mobile-search-results-active');
  }

  // Mobile search - live search as you type
  mobileSearchInput && mobileSearchInput.addEventListener('input', () => {
    const query = mobileSearchInput.value.trim();
    if (mobileSearchTimer) clearTimeout(mobileSearchTimer);
    mobileSearchTimer = setTimeout(() => performMobileSearch(query), 300);
  });

  // Mobile search form submission
  const mobileSearchForm = document.getElementById('mobileSearchForm');
  if (mobileSearchForm && mobileSearchInput) {
    mobileSearchForm.addEventListener('submit', function(e) {
      const query = mobileSearchInput.value.trim();
      if (!query) {
        e.preventDefault();
        return false;
      }
      // Let the form submit naturally to /search page
    }, false);
    
    // Handle Enter key - submit form if query is long enough
    mobileSearchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        const query = mobileSearchInput.value.trim();
        if (query && query.length >= 2) {
          // Submit form to go to search page
          e.preventDefault();
          mobileSearchForm.submit();
        } else {
          e.preventDefault();
        }
      }
    }, false);
    
    // Also handle submit button click
    const mobileSearchSubmitButton = mobileSearchForm.querySelector('button[type="submit"]');
    if (mobileSearchSubmitButton) {
      mobileSearchSubmitButton.addEventListener('click', function(e) {
        const query = mobileSearchInput.value.trim();
        if (!query) {
          e.preventDefault();
          return false;
        }
        // Manually submit form to ensure it works
        e.preventDefault();
        mobileSearchForm.submit();
      }, false);
    }
  }

  // Desktop search form - ensure it works correctly
  const desktopSearchForm = document.querySelector('.navbar__search--desktop');
  const desktopSearchInput = desktopSearchForm ? desktopSearchForm.querySelector('input[name="q"]') : null;
  const desktopSearchButton = desktopSearchForm ? desktopSearchForm.querySelector('button[type="submit"]') : null;
  
  if (desktopSearchForm && desktopSearchInput) {
    // Handle form submission - only prevent if query is empty
    desktopSearchForm.addEventListener('submit', function(e) {
      const query = desktopSearchInput.value.trim();
      if (!query) {
        e.preventDefault();
        return false;
      }
      // Let the form submit naturally - don't prevent default
    }, false);
    
    // Handle Enter key in the input
    desktopSearchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        const query = desktopSearchInput.value.trim();
        if (!query) {
          e.preventDefault();
          return false;
        }
        // If query exists, let form submit naturally
      }
    }, false);
    
    // Ensure button click works - manually trigger form submit
    if (desktopSearchButton) {
      desktopSearchButton.addEventListener('click', function(e) {
        const query = desktopSearchInput.value.trim();
        if (!query) {
          e.preventDefault();
          return false;
        }
        // Manually submit form to ensure it works
        e.preventDefault();
        desktopSearchForm.submit();
      }, false);
    }
  }
  
  // Add image loading event listeners for lazy loading
  const images = document.querySelectorAll('img[loading="lazy"]');
  images.forEach(img => {
    img.addEventListener('load', function() {
      this.classList.add('loaded');
    });
    
    img.addEventListener('error', function() {
      // Fallback if image fails to load
      this.classList.add('loaded');
    });
  });
})();
