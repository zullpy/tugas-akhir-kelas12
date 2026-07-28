<!-- components/footer.php -->
<style>
/* Neubrutalism Footer */
footer {
    width: 100%;
    margin-top: 50px;
    background-color: var(--nb-navy-dark, #053B50);
    color: var(--nb-white, #FFFFFF);
    border-top: var(--border-thick, 3.5px solid #000000);
    padding: 32px 0;
    font-family: var(--font-body, 'Plus Jakarta Sans', sans-serif);
}

.footer-container {
    width: 92%;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.footer-logo {
    height: 48px;
    width: auto;
    background: var(--nb-white, #FFFFFF);
    padding: 4px 8px;
    border: var(--border-thin, 2px solid #000000);
    border-radius: var(--radius-btn, 10px);
    box-shadow: 2px 2px 0px #000000;
}

.footer-content {
    flex: 1;
    min-width: 280px;
}

.footer-text {
    font-size: 0.85rem;
    font-weight: 500;
    line-height: 1.5;
    color: #E2E8F0;
}

.social-links {
    display: flex;
    gap: 10px;
}

.social-links a.social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    background-color: var(--nb-yellow, #FFE600);
    color: #000000;
    border: var(--border-medium, 2.5px solid #000000);
    border-radius: var(--radius-btn, 10px);
    box-shadow: var(--shadow-sm, 3px 3px 0px #000000);
    transition: all 0.2s ease;
    text-decoration: none;
}

.social-links a.social-btn:hover {
    transform: translate(-2px, -2px);
    box-shadow: 4px 4px 0px #000000;
    color: #FFFFFF;
}

/* Specific Social Media App Hover Colors */
.social-links a.social-youtube:hover {
    background-color: #FF0000 !important;
}

.social-links a.social-facebook:hover {
    background-color: #1877F2 !important;
}

.social-links a.social-instagram:hover {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%) !important;
}

.social-links a.social-tiktok:hover {
    background-color: #000000 !important;
    box-shadow: 4px 4px 0px #FE2C55 !important;
}
</style>

<footer>
    <div class="footer-container">
        <img src="assets/logo2.webp" alt="Logo SMKS SUKAPURA" class="footer-logo" onerror="this.onerror=null; this.src='assets/favicon.ico';">
        <div class="footer-content">
            <p class="footer-text">SMK PK (Pusat Keunggulan) merupakan program pengembangan SMK dengan kompetensi keahlian (Busana) dalam peningkatan kualitas dan kinerja, yang diperkuat melalui kemitraan dan penyelarasan dengan dunia usaha, dunia industri, dan dunia kerja, yang akhirnya menjadi SMK rujukan yang dapat berfungsi sebagai sekolah penggerak dan pusat peningkatan kualitas dan kinerja SMK.</p>
        </div>
        <div class="social-links">
            <a href="https://www.youtube.com/@smksukapurakab.tasikmalaya" target="_blank" aria-label="YouTube" class="social-btn social-youtube">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
            </a>
            <a href="https://www.facebook.com/people/Smks-Sukapura-Kab-Tasikmalaya/61555166803090/" target="_blank" aria-label="Facebook" class="social-btn social-facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>
            <a href="https://www.instagram.com/smksukapurakab.tasikmalaya/" target="_blank" aria-label="Instagram" class="social-btn social-instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
            <a href="https://www.tiktok.com/@smksukapurakabtas" target="_blank" aria-label="TikTok" class="social-btn social-tiktok">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.57-1.32 1.55-1.32 2.55 0 1.25.72 2.41 1.83 2.97.98.49 2.15.53 3.16.14 1.05-.4 1.84-1.29 2.12-2.37.15-.56.17-1.14.17-1.71V0z"/>
                </svg>
            </a>
        </div>
    </div>
</footer>
