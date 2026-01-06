(function() {
  'use strict';
  
  // Admin sidebar toggle (mobile)
  const adminMenuToggle = document.getElementById('adminMenuToggle');
  const adminSidebar = document.getElementById('adminSidebar');
  const adminSidebarOverlay = document.getElementById('adminSidebarOverlay');
  
  const toggleAdminSidebar = (open) => {
    if (!adminSidebar) return;
    const shouldOpen = typeof open === 'boolean' ? open : !adminSidebar.classList.contains('is-open');
    adminSidebar.classList.toggle('is-open', shouldOpen);
    if (adminSidebarOverlay) {
      adminSidebarOverlay.classList.toggle('is-visible', shouldOpen);
    }
    // Prevent body scroll when sidebar is open on mobile
    if (shouldOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  };
  
  if (adminMenuToggle) {
    adminMenuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleAdminSidebar();
    });
  }
  
  if (adminSidebarOverlay) {
    adminSidebarOverlay.addEventListener('click', () => toggleAdminSidebar(false));
  }
  
  // Close sidebar when clicking outside on mobile
  document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024 && adminSidebar && adminSidebar.classList.contains('is-open')) {
      if (!adminSidebar.contains(e.target) && !adminMenuToggle.contains(e.target)) {
        toggleAdminSidebar(false);
      }
    }
  });
  
  // Close sidebar on escape key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && adminSidebar && adminSidebar.classList.contains('is-open')) {
      toggleAdminSidebar(false);
    }
  });
  
  // Delete confirmation
  const deleteForms = document.querySelectorAll('.delete-form, form[action*="/delete"]');
  deleteForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      if (!confirm('Apakah Anda yakin ingin menghapus manga ini? Tindakan ini tidak dapat dibatalkan.')) {
        e.preventDefault();
        return false;
      }
    });
  });
  
  // Chapter search and sort
  const chapterSearch = document.getElementById('chapterSearch');
  const chapterSort = document.getElementById('chapterSort');
  const adminChapterList = document.getElementById('adminChapterList');
  
  if (adminChapterList) {
    const originalItems = Array.from(adminChapterList.querySelectorAll('.admin-chapter-item'));
    
    const filterAndSort = () => {
      const query = chapterSearch ? chapterSearch.value.toLowerCase() : '';
      const sortBy = chapterSort ? chapterSort.value : 'asc';
      
      let filtered = originalItems.filter(item => {
        const number = item.dataset.chapterNumber || '';
        const title = item.querySelector('.admin-chapter-item__title')?.textContent || '';
        return number.includes(query) || title.toLowerCase().includes(query);
      });
      
      // Sort
      filtered.sort((a, b) => {
        const numA = parseFloat(a.dataset.chapterNumber) || 0;
        const numB = parseFloat(b.dataset.chapterNumber) || 0;
        const dateA = new Date(a.dataset.created || 0);
        const dateB = new Date(b.dataset.created || 0);
        
        if (sortBy === 'desc') return numB - numA;
        if (sortBy === 'newest') return dateB - dateA;
        if (sortBy === 'oldest') return dateA - dateB;
        return numA - numB; // asc
      });
      
      adminChapterList.innerHTML = '';
      filtered.forEach(item => adminChapterList.appendChild(item));
    };
    
    if (chapterSearch) {
      chapterSearch.addEventListener('input', filterAndSort);
    }
    if (chapterSort) {
      chapterSort.addEventListener('change', filterAndSort);
    }
  }

  // Batch selection for manga list
  const batchForm = document.getElementById('batchForm');
  if (batchForm) {
    const checkboxes = Array.from(batchForm.querySelectorAll('.batch-checkbox'));
    const selectAll = document.getElementById('batchSelectAll');
    const countLabel = document.getElementById('selectedCount');
    const applyBtn = document.getElementById('batchApply');
    const typeSelect = document.getElementById('batchType');
    const countrySelect = document.getElementById('batchCountry');

    const getSelectedCount = () => checkboxes.filter(cb => cb.checked).length;

    const updateState = () => {
      const count = getSelectedCount();
      if (countLabel) {
        countLabel.textContent = `${count} dipilih`;
      }
      if (selectAll) {
        selectAll.checked = count > 0 && count === checkboxes.length;
      }
      if (applyBtn) {
        const hasChange = (typeSelect && typeSelect.value) || (countrySelect && countrySelect.value);
        applyBtn.disabled = !count || !hasChange;
      }
    };

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));

    if (selectAll) {
      selectAll.addEventListener('change', () => {
        const checked = selectAll.checked;
        checkboxes.forEach(cb => {
          cb.checked = checked;
        });
        updateState();
      });
    }

    [typeSelect, countrySelect].forEach(select => {
      if (!select) return;
      select.addEventListener('change', updateState);
    });

    batchForm.addEventListener('submit', (e) => {
      const selected = getSelectedCount();
      if (!selected) {
        e.preventDefault();
        alert('Pilih minimal satu manga.');
        return;
      }
      const hasChange = (typeSelect && typeSelect.value) || (countrySelect && countrySelect.value);
      if (!hasChange) {
        e.preventDefault();
        alert('Pilih jenis atau negara yang ingin diubah.');
      }
    });

    updateState();
  }
})();
