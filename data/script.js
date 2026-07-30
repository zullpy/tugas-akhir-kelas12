// Interactive JS for Data Guru & Staff Page - SMKS SUKAPURA
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('teacher-search');
    const clearBtn = document.getElementById('clear-search');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const viewTableBtn = document.getElementById('view-table-btn');
    const viewGridBtn = document.getElementById('view-grid-btn');
    const tableView = document.getElementById('table-view');
    const gridView = document.getElementById('grid-view');
    const tableRows = document.querySelectorAll('.guru-row');
    const gridCards = document.querySelectorAll('.guru-card');
    const noResults = document.getElementById('no-results');
    const queryTerm = document.getElementById('query-term');
    const resetFilterBtn = document.getElementById('reset-filter-btn');

    let currentFilter = 'all';
    let searchQuery = '';

    // ==========================================
    // FILTER & SEARCH LOGIC
    // ==========================================
    function filterItems() {
        let visibleCount = 0;

        // Filter Table Rows
        tableRows.forEach(row => {
            const category = row.getAttribute('data-category');
            const searchData = row.getAttribute('data-search');

            const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
            const matchesSearch = searchQuery === '' || searchData.includes(searchQuery.toLowerCase());

            if (matchesCategory && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter Grid Cards
        gridCards.forEach(card => {
            const category = card.getAttribute('data-category');
            const searchData = card.getAttribute('data-search');

            const matchesCategory = (currentFilter === 'all') || (category === currentFilter);
            const matchesSearch = searchQuery === '' || searchData.includes(searchQuery.toLowerCase());

            if (matchesCategory && matchesSearch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle No Results Message
        if (visibleCount === 0) {
            noResults.style.display = 'block';
            queryTerm.textContent = searchQuery || currentFilter;
        } else {
            noResults.style.display = 'none';
        }
    }

    // Live Search Input Event
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value.trim();
            if (clearBtn) {
                clearBtn.style.display = searchQuery ? 'block' : 'none';
            }
            filterItems();
        });
    }

    // Clear Search Input Button
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchQuery = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
            filterItems();
        });
    }

    // Category Tab Filtering
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.getAttribute('data-filter');
            filterItems();
        });
    });

    // Reset Filters
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchQuery = '';
            if (clearBtn) clearBtn.style.display = 'none';

            tabButtons.forEach(b => b.classList.remove('active'));
            tabButtons[0].classList.add('active');
            currentFilter = 'all';

            filterItems();
        });
    }

    // ==========================================
    // VIEW SWITCHER (TABLE VS GRID)
    // ==========================================
    if (viewTableBtn && viewGridBtn && tableView && gridView) {
        viewTableBtn.addEventListener('click', () => {
            viewTableBtn.classList.add('active');
            viewGridBtn.classList.remove('active');
            tableView.style.display = 'block';
            gridView.style.display = 'none';
        });

        viewGridBtn.addEventListener('click', () => {
            viewGridBtn.classList.add('active');
            viewTableBtn.classList.remove('active');
            gridView.style.display = 'block';
            tableView.style.display = 'none';
        });
    }
});
