document.addEventListener('DOMContentLoaded', () => {
    // "Lihat Selengkapnya" Expand / Collapse Logic per Section
    const seeMoreBtns = document.querySelectorAll('.btn-see-more');

    seeMoreBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const section = btn.closest('.gallery-category-section');
            if (!section) return;

            const hiddenPhotos = section.querySelectorAll('.gallery-card.hidden-photo, .gallery-card.expanded-photo');
            const isExpanded = btn.classList.contains('expanded');

            if (isExpanded) {
                hiddenPhotos.forEach(card => {
                    card.classList.remove('expanded-photo');
                    card.classList.add('hidden-photo');
                });
                btn.classList.remove('expanded');
                btn.innerHTML = '<span>Lihat Selengkapnya</span> <i class="ph-bold ph-caret-down"></i>';
            } else {
                hiddenPhotos.forEach(card => {
                    card.classList.remove('hidden-photo');
                    card.classList.add('expanded-photo');
                });
                btn.classList.add('expanded');
                btn.innerHTML = '<span>Sembunyikan</span> <i class="ph-bold ph-caret-up"></i>';
            }
        });
    });

    // Lightbox Modal Logic (Pure Full Preview, No Caption Text)
    const modal = document.getElementById('lightbox-modal');
    if (!modal) return;

    const modalImg = document.getElementById('lightbox-img');
    const closeBtn = document.getElementById('lightbox-close');
    const prevBtn = document.getElementById('lightbox-prev');
    const nextBtn = document.getElementById('lightbox-next');

    let visibleCards = [];
    let currentIndex = -1;

    function getVisibleCards() {
        return Array.from(document.querySelectorAll('.gallery-card')).filter(card => {
            const style = window.getComputedStyle(card);
            return style.display !== 'none' && !card.classList.contains('hidden-photo');
        });
    }

    function showImage(index) {
        visibleCards = getVisibleCards();
        if (visibleCards.length === 0) return;
        if (index < 0) index = visibleCards.length - 1;
        if (index >= visibleCards.length) index = 0;

        currentIndex = index;
        const card = visibleCards[currentIndex];
        const img = card.querySelector('img');
        const categoryTitle = card.getAttribute('data-category-title') || 'Dokumentasi Kegiatan';

        if (img) {
            modalImg.src = img.src;
            const titleElem = document.getElementById('lightbox-category-title');
            if (titleElem) {
                titleElem.innerHTML = '<i class="ph-bold ph-image"></i> ' + categoryTitle;
            }
            modal.classList.add('active');
        }
    }

    document.addEventListener('click', (e) => {
        const card = e.target.closest('.gallery-card');
        if (card && !card.classList.contains('hidden-photo')) {
            visibleCards = getVisibleCards();
            const index = visibleCards.indexOf(card);
            showImage(index);
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            modal.classList.remove('active');
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showImage(currentIndex - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showImage(currentIndex + 1);
        });
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.classList.contains('lightbox-body')) {
            modal.classList.remove('active');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (!modal.classList.contains('active')) return;
        if (e.key === 'Escape') modal.classList.remove('active');
        if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
        if (e.key === 'ArrowRight') showImage(currentIndex + 1);
    });
});
