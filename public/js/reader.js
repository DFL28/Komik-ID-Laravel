(function() {
  'use strict';
  
  const readerNavbar = document.getElementById('readerNavbar');
  const readerPages = document.getElementById('readerPages');

  if (readerNavbar) {
    let lastScrollY = window.scrollY;
    let navbarHidden = false;
    const handleScroll = () => {
      const current = window.scrollY;
      if (current > lastScrollY + 10 && current > 80 && !navbarHidden) {
        readerNavbar.classList.add('is-hidden');
        navbarHidden = true;
      } else if (current < lastScrollY - 10 && navbarHidden) {
        readerNavbar.classList.remove('is-hidden');
        navbarHidden = false;
      }
      lastScrollY = current;
    };
    document.addEventListener('scroll', handleScroll, { passive: true });
  }
  
  // Zoom controls (desktop only)
  if (window.innerWidth >= 768) {
    const zoomIn = document.getElementById('zoomIn');
    const zoomOut = document.getElementById('zoomOut');
    const zoomReset = document.getElementById('zoomReset');
    const zoomIndicator = document.getElementById('zoomIndicator');
    const readerImages = document.querySelectorAll('.reader__pages img, .reader__pages-stream img');
    
    const minZoom = 50;
    const maxZoom = 200;
    const zoomStep = 10;
    
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
    
    applyZoom(currentZoom);
    
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
  
  // Live reader streaming
  const liveReader = document.getElementById('liveReader');
  function removeDuplicateImages () {
    const readerRoot = document.getElementById('readerPages');
    if (!readerRoot) return;
    
    const images = Array.from(readerRoot.querySelectorAll('img.chapter-page'));
    if (!images.length) return;
    
    const seen = new Map();
    images.forEach((img) => {
      const src = img.src || img.getAttribute('src') || '';
      const dataPath = img.getAttribute('data-image-path') || '';
      const pageNum = img.getAttribute('data-page-number');
      const normalizedSrc = src ? src.split('?')[0].toLowerCase() : '';
      const key = pageNum ? `page-${pageNum}` : (dataPath || normalizedSrc);
      
      if (seen.has(key)) {
        img.remove();
      } else {
        seen.set(key, img);
      }
    });
  }

  if (liveReader && window.EventSource) {
    const chapterId = liveReader.dataset.chapterId;
    const container = document.getElementById('liveReaderContainer');
    const statusEl = document.getElementById('liveReaderStatus');
    
    // Track rendered images to prevent duplicates - use multiple keys for better detection
    const renderedImages = new Set();
    const renderedPageNumbers = new Set();
    const pageElements = new Map();
    
    // Also check existing images in DOM (for non-streaming mode that might have rendered)
    if (container) {
      const existingImgs = container.querySelectorAll('img.chapter-page');
      existingImgs.forEach(img => {
        const src = img.src || img.getAttribute('src') || '';
        const dataPath = img.getAttribute('data-image-path') || '';
        const pageNum = img.getAttribute('data-page-number');
        
        if (src) {
          const normalized = src.replace(/\/+$/, '').toLowerCase();
          renderedImages.add(normalized);
          if (dataPath) {
            renderedImages.add(dataPath.toLowerCase());
          }
          if (pageNum) {
            const numStr = String(pageNum);
            renderedPageNumbers.add(numStr);
            pageElements.set(numStr, img);
          }
        }
      });
    }
    
    const buildSrc = (imagePath) => {
      if (!imagePath) return '';
      if (imagePath.startsWith('http')) return imagePath;
      if (imagePath.startsWith('/media/')) return imagePath;
      return `/media/${imagePath}`;
    };
    
    const renderImage = (imagePath, pageNumber) => {
      if (!imagePath || !container) return;
      const pageKeyNumber = (pageNumber === 0 || pageNumber) ? String(pageNumber) : null;

      if (pageKeyNumber && renderedPageNumbers.has(pageKeyNumber)) {
        const existingByNumber = container.querySelector(`img.chapter-page[data-page-number="${pageKeyNumber}"]`);
        if (existingByNumber) {
          existingByNumber.src = buildSrc(imagePath);
          existingByNumber.setAttribute('data-image-path', imagePath);
        }
        return;
      }
      
      // Normalize image path for comparison - multiple methods
      const normalized1 = imagePath.trim().replace(/\/+$/, '').toLowerCase().replace(/^\/+/, '');
      const normalized2 = imagePath.trim().replace(/\/+/g, '/').toLowerCase();
      const filename = imagePath.split('/').pop().split('?')[0].toLowerCase();
      const fullSrc = buildSrc(imagePath);
      
      // Extract pathname
      let pathname = '';
      try {
        if (fullSrc.startsWith('http')) {
          pathname = new URL(fullSrc).pathname.toLowerCase();
        } else {
          pathname = fullSrc.split('?')[0].toLowerCase();
        }
      } catch (e) {
        pathname = fullSrc.split('?')[0].toLowerCase();
      }
      
      // Check page number first - if same page number already rendered, skip
      if (pageKeyNumber && renderedPageNumbers.has(pageKeyNumber)) {
        const existingByNumber = pageElements.get(pageKeyNumber);
        if (existingByNumber) {
          existingByNumber.src = buildSrc(imagePath);
          existingByNumber.setAttribute('data-image-path', imagePath);
        }
        return;
      }
      
      // Check if image already exists in DOM - check multiple variations
      const existingImages = container.querySelectorAll('img.chapter-page');
      for (const existingImg of existingImages) {
        const existingSrc = existingImg.src || '';
        const existingDataPath = existingImg.getAttribute('data-image-path') || '';
        const existingPageNum = existingImg.getAttribute('data-page-number');
        const existingPathname = existingSrc ? existingSrc.split('?')[0].toLowerCase() : '';
        const existingFilename = existingSrc.split('/').pop().split('?')[0].toLowerCase();
        
        // Check page number first - replace instead of duplicate
        if (pageKeyNumber && existingPageNum && parseInt(existingPageNum, 10) === parseInt(pageKeyNumber, 10)) {
          existingImg.src = fullSrc;
          existingImg.setAttribute('data-image-path', normalized1);
          pageElements.set(pageKeyNumber, existingImg);
          return;
        }
        
        if (existingSrc === fullSrc || 
            existingPathname === pathname ||
            existingFilename === filename ||
            existingDataPath === normalized1 ||
            existingDataPath === normalized2 ||
            existingSrc.endsWith(normalized1) ||
            existingSrc.endsWith(normalized2)) {
          return;
        }
      }
      
      // Prevent duplicate images in tracking set - check all variations
      const keys = [normalized1, normalized2, filename, pathname, fullSrc.toLowerCase()];
      for (const key of keys) {
        if (renderedImages.has(key)) {
          console.log('Skipping duplicate image (in tracking):', imagePath);
          return;
        }
      }
      
      // Mark all variations as seen
      keys.forEach(key => renderedImages.add(key));
      if (pageKeyNumber) {
        renderedPageNumbers.add(pageKeyNumber);
      }
      
      const img = document.createElement('img');
      img.src = fullSrc;
      img.loading = 'lazy';
      img.className = 'chapter-page';
      img.style.display = 'block';
      img.style.width = '100%';
      img.style.height = 'auto';
      img.alt = `Page ${pageNumber || renderedImages.size}`;
      img.setAttribute('data-image-path', normalized1);
      if (pageNumber) {
        img.setAttribute('data-page-number', pageNumber);
      }
      if (pageKeyNumber) {
        pageElements.set(pageKeyNumber, img);
      }
      container.appendChild(img);
      removeDuplicateImages();
    };
    
    // Don't render initial data - EventSource will send all pages including existing ones
    // This prevents duplicate images
    
    const source = new EventSource(`/api/chapters/${chapterId}/stream`);
    source.addEventListener('page', (event) => {
      const payload = JSON.parse(event.data);
      renderImage(payload.imagePath, payload.pageNumber);
      if (statusEl) {
        statusEl.textContent = `Memuat halaman ${payload.pageNumber || renderedImages.size}...`;
      }
    });
    
    source.addEventListener('done', () => {
      if (statusEl) {
        statusEl.textContent = 'Semua halaman berhasil dimuat.';
        setTimeout(() => {
          if (statusEl) statusEl.style.display = 'none';
        }, 2000);
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
  
  // Additional check: Remove duplicate images from DOM after page load
  // Run on page load and after a short delay
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', removeDuplicateImages);
  } else {
    removeDuplicateImages();
  }
  
  // Also run after a delay to catch dynamically loaded images
  setTimeout(removeDuplicateImages, 1000);
  setTimeout(removeDuplicateImages, 3000);
})();
