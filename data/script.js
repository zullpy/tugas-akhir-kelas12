// Interactive JS for Data Guru & Data Siswa Page - SMKS SUKAPURA
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('teacher-search') || document.getElementById('siswa-search') || document.getElementById('kelas-search') || document.querySelector('.search-box input');
    const clearBtn = document.getElementById('clear-search');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const jurusanSelect = document.getElementById('jurusan-select');
    const viewTableBtn = document.getElementById('view-table-btn');
    const viewGridBtn = document.getElementById('view-grid-btn');
    const tableView = document.getElementById('table-view');
    const gridView = document.getElementById('grid-view');
    const tableRows = document.querySelectorAll('.guru-row, .siswa-row, .kelas-row, .ekstra-row');
    const gridCards = document.querySelectorAll('.guru-card, .siswa-card, .kelas-card, .ekstra-card');
    const noResults = document.getElementById('no-results');
    const queryTerm = document.getElementById('query-term');
    const resetFilterBtn = document.getElementById('reset-filter-btn');

    // Load More Elements
    const loadMoreWrapper = document.getElementById('load-more-wrapper');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreBadge = document.getElementById('load-more-badge');

    const INITIAL_LIMIT = 6;
    let visibleLimit = INITIAL_LIMIT;
    let currentFilter = 'all';
    let currentJurusan = 'all';
    let searchQuery = '';

    // ==========================================
    // FILTER & SEARCH & PAGINATION LOGIC
    // ==========================================
    function filterItems(resetLimit = false) {
        if (resetLimit) {
            visibleLimit = INITIAL_LIMIT;
        }

        const isTableView = tableView && tableView.style.display !== 'none';
        const matchingCards = [];
        const matchingRows = [];

        // Filter Grid Cards
        gridCards.forEach(card => {
            const category = card.getAttribute('data-category');
            const jurusan = card.getAttribute('data-jurusan');
            const searchData = card.getAttribute('data-search') || '';

            const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
            const matchesJurusan = !jurusanSelect || (currentJurusan === 'all') || (jurusan === currentJurusan);
            const matchesSearch = searchQuery === '' || searchData.includes(searchQuery.toLowerCase());

            if (matchesCategory && matchesJurusan && matchesSearch) {
                matchingCards.push(card);
            } else {
                card.style.display = 'none';
                card.classList.remove('revealed');
            }
        });

        // Filter Table Rows
        tableRows.forEach(row => {
            const category = row.getAttribute('data-category');
            const jurusan = row.getAttribute('data-jurusan');
            const searchData = row.getAttribute('data-search') || '';

            const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
            const matchesJurusan = !jurusanSelect || (currentJurusan === 'all') || (jurusan === currentJurusan);
            const matchesSearch = searchQuery === '' || searchData.includes(searchQuery.toLowerCase());

            if (matchesCategory && matchesJurusan && matchesSearch) {
                matchingRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        const totalMatching = Math.max(matchingCards.length, matchingRows.length);

        // Display Grid Cards up to visibleLimit
        matchingCards.forEach((card, index) => {
            if (index < visibleLimit) {
                const wasHidden = card.style.display === 'none';
                card.style.display = 'flex';
                card.style.setProperty('--card-index', (index % INITIAL_LIMIT).toString());

                if (wasHidden || resetLimit) {
                    card.classList.remove('revealed');
                    void card.offsetWidth; // trigger reflow
                    card.classList.add('revealed');
                }
            } else {
                card.style.display = 'none';
                card.classList.remove('revealed');
            }
        });

        // Display Table Rows: Always show ALL matching rows in Table View
        matchingRows.forEach((row) => {
            row.style.display = '';
        });

        // Toggle No Results Message
        if (totalMatching === 0) {
            if (noResults) noResults.style.display = 'block';
            if (queryTerm) queryTerm.textContent = searchQuery || (currentJurusan !== 'all' ? currentJurusan : currentFilter);
        } else {
            if (noResults) noResults.style.display = 'none';
        }

        // Update Load More Controls (Only for Grid View)
        if (isTableView) {
            if (loadMoreWrapper) loadMoreWrapper.style.display = 'none';
        } else {
            updateLoadMoreControls(matchingCards.length);
        }
    }

    function updateLoadMoreControls(totalMatchingCards) {
        if (!loadMoreWrapper || !loadMoreBtn) return;

        if (totalMatchingCards <= INITIAL_LIMIT) {
            loadMoreWrapper.style.display = 'none';
        } else {
            loadMoreWrapper.style.display = 'flex';
            const btnIcon = loadMoreBtn.querySelector('.btn-icon');
            const btnText = loadMoreBtn.querySelector('.btn-text');

            if (visibleLimit < totalMatchingCards) {
                const shownCount = Math.min(visibleLimit, totalMatchingCards);
                const remaining = totalMatchingCards - shownCount;
                if (btnText) btnText.textContent = 'Lihat Selengkapnya';
                if (btnIcon) btnIcon.className = 'ph-bold ph-caret-down btn-icon';
                if (loadMoreBadge) loadMoreBadge.textContent = `Menampilkan ${shownCount} dari ${totalMatchingCards}`;
                loadMoreBtn.classList.remove('expanded');
            } else {
                if (btnText) btnText.textContent = 'Tampilkan Lebih Sedikit';
                if (btnIcon) btnIcon.className = 'ph-bold ph-caret-up btn-icon';
                if (loadMoreBadge) loadMoreBadge.textContent = `Semua ${totalMatchingCards} Data Ditampilkan`;
                loadMoreBtn.classList.add('expanded');
            }
        }
    }

    // Load More Click Listener
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            let totalMatching = 0;
            gridCards.forEach(card => {
                const category = card.getAttribute('data-category');
                const jurusan = card.getAttribute('data-jurusan');
                const searchData = card.getAttribute('data-search') || '';

                const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
                const matchesJurusan = !jurusanSelect || (currentJurusan === 'all') || (jurusan === currentJurusan);
                const matchesSearch = searchQuery === '' || searchData.includes(searchQuery.toLowerCase());
                if (matchesCategory && matchesJurusan && matchesSearch) totalMatching++;
            });

            if (visibleLimit < totalMatching) {
                visibleLimit += INITIAL_LIMIT;
                filterItems(false);
            } else {
                visibleLimit = INITIAL_LIMIT;
                filterItems(false);
                const targetEl = document.getElementById('grid-view') || document.querySelector('.controls-section');
                if (targetEl) {
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    }

    // Live Search Input Event
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.trim();
            if (clearBtn) {
                clearBtn.style.display = searchQuery ? 'block' : 'none';
            }
            filterItems(true);
        });
    }

    // Clear Search Input Button
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            searchQuery = '';
            clearBtn.style.display = 'none';
            if (searchInput) searchInput.focus();
            filterItems(true);
        });
    }

    // Category / Grade Tab Filtering
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.getAttribute('data-filter');
            filterItems(true);
        });
    });

    // Jurusan Dropdown Filtering (If present)
    if (jurusanSelect) {
        jurusanSelect.addEventListener('change', (e) => {
            currentJurusan = e.target.value;
            filterItems(true);
        });
    }

    // Reset Filters
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            searchQuery = '';
            if (clearBtn) clearBtn.style.display = 'none';

            tabButtons.forEach(b => b.classList.remove('active'));
            if (tabButtons[0]) tabButtons[0].classList.add('active');
            currentFilter = 'all';

            if (jurusanSelect) {
                jurusanSelect.value = 'all';
                currentJurusan = 'all';
            }

            filterItems(true);
        });
    }

    // Initial run
    filterItems(true);

    // ==========================================
    // VIEW SWITCHER (TABLE VS GRID)
    // ==========================================
    if (viewTableBtn && viewGridBtn && tableView && gridView) {
        viewTableBtn.addEventListener('click', () => {
            viewTableBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
            tableView.style.display = 'block';
            gridView.style.display = 'none';
            filterItems(false);
        });

        viewGridBtn.addEventListener('click', () => {
            viewGridBtn.classList.add('active');
            viewTableBtn.classList.remove('active');
            gridView.style.display = 'block';
            tableView.style.display = 'none';
            gridCards.forEach(card => card.classList.add('revealed'));
            filterItems(false);
        });
    }

    // ============================================
    // SCROLL REVEAL ANIMATION (PROACTIVE & RELIABLE)
    // ============================================
    const revealSelectors = '.reveal, .reveal-left, .reveal-right, .reveal-scale';
    const revealElements = document.querySelectorAll(revealSelectors);

    if (revealElements.length > 0) {
        const checkReveals = () => {
            revealElements.forEach((el) => {
                const rect = el.getBoundingClientRect();
                if (rect.top <= window.innerHeight + 150 && rect.bottom >= -100) {
                    el.classList.add('revealed');
                }
            });
        };

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0,
            rootMargin: '150px 0px 150px 0px'
        });

        revealElements.forEach((el) => revealObserver.observe(el));

        checkReveals();
        window.addEventListener('scroll', checkReveals, { passive: true });
    }
});
