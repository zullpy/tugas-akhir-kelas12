// Neubrutalism Interactive Logic
if (!window.scriptInitialized) {
    window.scriptInitialized = true;

    const initScript = () => {
        // ============================================
        // 1. NAVBAR TOGGLE & DROPDOWNS
        // ============================================
        const navToggle = document.getElementById('nav-toggle');
        const navLinks = document.getElementById('nav-links');
        const dropdownItems = document.querySelectorAll('.has-dropdown');

        // Toggle Mobile Navigation
        if (navToggle && navLinks) {
            navToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = navLinks.classList.toggle('show-links');
                navToggle.classList.toggle('open', isOpen);
                navToggle.setAttribute('aria-expanded', isOpen);
            });
        }

        // Toggle Dropdowns on Click (Mobile & Touch support)
        dropdownItems.forEach((item) => {
            const link = item.querySelector('.nav-link');
            if (link) {
                link.addEventListener('click', (e) => {
                    // If on mobile view, toggle sub-menu on click
                    if (window.innerWidth <= 992) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Close other dropdowns
                        dropdownItems.forEach((otherItem) => {
                            if (otherItem !== item) {
                                otherItem.classList.remove('open');
                            }
                        });

                        item.classList.toggle('open');
                    }
                });
            }
        });

        // Close mobile nav & dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#navbar')) {
                if (navLinks && navLinks.classList.contains('show-links')) {
                    navLinks.classList.remove('show-links');
                    if (navToggle) {
                        navToggle.classList.remove('open');
                        navToggle.setAttribute('aria-expanded', 'false');
                    }
                }

                dropdownItems.forEach((item) => {
                    item.classList.remove('open');
                });
            }
        });

        // Handle Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (navLinks && navLinks.classList.contains('show-links')) {
                    navLinks.classList.remove('show-links');
                    if (navToggle) {
                        navToggle.classList.remove('open');
                        navToggle.setAttribute('aria-expanded', 'false');
                    }
                }
                dropdownItems.forEach((item) => {
                    item.classList.remove('open');
                });
            }
        });

        // ============================================
        // 2. HERO CAROUSEL LOGIC
        // ============================================
        const carouselSlides = document.querySelectorAll('.carousel-slide');
        const carouselIndicators = document.querySelectorAll('.carousel-indicators .indicator');
        const prevBtn = document.querySelector('.carousel-control.prev');
        const nextBtn = document.querySelector('.carousel-control.next');

        if (carouselSlides.length > 0) {
            let currentSlide = 0;
            let slideInterval;
            const intervalTime = 5000; // 5 seconds per slide

            function updateCarousel(newIndex) {
                if (carouselSlides[currentSlide]) carouselSlides[currentSlide].classList.remove('active');
                if (carouselIndicators[currentSlide]) carouselIndicators[currentSlide].classList.remove('active');

                currentSlide = newIndex;
                if (currentSlide < 0) currentSlide = carouselSlides.length - 1;
                if (currentSlide >= carouselSlides.length) currentSlide = 0;

                if (carouselSlides[currentSlide]) carouselSlides[currentSlide].classList.add('active');
                if (carouselIndicators[currentSlide]) carouselIndicators[currentSlide].classList.add('active');
            }

            function nextSlide() {
                updateCarousel(currentSlide + 1);
            }

            function prevSlide() {
                updateCarousel(currentSlide - 1);
            }

            function resetInterval() {
                clearInterval(slideInterval);
                slideInterval = setInterval(nextSlide, intervalTime);
            }

            if (prevBtn && nextBtn) {
                prevBtn.addEventListener('click', () => {
                    prevSlide();
                    resetInterval();
                });

                nextBtn.addEventListener('click', () => {
                    nextSlide();
                    resetInterval();
                });
            }

            carouselIndicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => {
                    updateCarousel(index);
                    resetInterval();
                });
            });

            slideInterval = setInterval(nextSlide, intervalTime);
        }

        // ============================================
        // 3. MITRA CAROUSEL LOGIC
        // ============================================
        const mitraContainer = document.getElementById('mitra-track-container');
        const mitraTrack = document.getElementById('mitra-track');
        const mitraPrev = document.getElementById('mitra-prev');
        const mitraNext = document.getElementById('mitra-next');

        if (mitraContainer && mitraTrack && mitraPrev && mitraNext) {
            let mitraAutoScrollTimer;
            const autoScrollInterval = 3500;

            function getScrollStep() {
                const firstCard = mitraTrack.querySelector('.mitra-card');
                if (!firstCard) return 200;
                const cardWidth = firstCard.offsetWidth;
                return (cardWidth + 20) * 2;
            }

            function scrollMitra(direction) {
                const step = getScrollStep();
                const maxScroll = mitraContainer.scrollWidth - mitraContainer.clientWidth;

                if (direction === 'next') {
                    if (mitraContainer.scrollLeft >= maxScroll - 15) {
                        mitraContainer.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        mitraContainer.scrollBy({ left: step, behavior: 'smooth' });
                    }
                } else {
                    if (mitraContainer.scrollLeft <= 15) {
                        mitraContainer.scrollTo({ left: maxScroll, behavior: 'smooth' });
                    } else {
                        mitraContainer.scrollBy({ left: -step, behavior: 'smooth' });
                    }
                }
            }

            mitraNext.addEventListener('click', () => {
                scrollMitra('next');
                resetMitraAutoScroll();
            });

            mitraPrev.addEventListener('click', () => {
                scrollMitra('prev');
                resetMitraAutoScroll();
            });

            function startMitraAutoScroll() {
                mitraAutoScrollTimer = setInterval(() => {
                    scrollMitra('next');
                }, autoScrollInterval);
            }

            function resetMitraAutoScroll() {
                clearInterval(mitraAutoScrollTimer);
                startMitraAutoScroll();
            }

            const mitraWrapper = document.querySelector('.mitra-carousel-wrapper');
            if (mitraWrapper) {
                mitraWrapper.addEventListener('mouseenter', () => clearInterval(mitraAutoScrollTimer));
                mitraWrapper.addEventListener('mouseleave', () => startMitraAutoScroll());
            }

            startMitraAutoScroll();
        }

        // ============================================
        // 4. SCROLL REVEAL (Intersection Observer)
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
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScript);
    } else {
        initScript();
    }
}
