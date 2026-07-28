// Neubrutalism Navbar Interactive Logic
document.addEventListener('DOMContentLoaded', () => {
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

    // Carousel Logic
    const carouselSlides = document.querySelectorAll('.carousel-slide');
    const carouselIndicators = document.querySelectorAll('.carousel-indicators .indicator');
    const prevBtn = document.querySelector('.carousel-control.prev');
    const nextBtn = document.querySelector('.carousel-control.next');
    
    if (carouselSlides.length > 0) {
        let currentSlide = 0;
        let slideInterval;
        const intervalTime = 5000; // 5 seconds per slide
        
        function updateCarousel(newIndex) {
            // Remove active class from current
            carouselSlides[currentSlide].classList.remove('active');
            carouselIndicators[currentSlide].classList.remove('active');
            
            // Update index
            currentSlide = newIndex;
            if (currentSlide < 0) currentSlide = carouselSlides.length - 1;
            if (currentSlide >= carouselSlides.length) currentSlide = 0;
            
            // Add active class to new
            carouselSlides[currentSlide].classList.add('active');
            carouselIndicators[currentSlide].classList.add('active');
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
        
        // Event Listeners for controls
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
        
        // Event Listeners for indicators
        carouselIndicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                updateCarousel(index);
                resetInterval();
            });
        });
        
        // Start Auto-Slide
        slideInterval = setInterval(nextSlide, intervalTime);
    }
});
