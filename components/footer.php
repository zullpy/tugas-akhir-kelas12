<!-- components/footer.php -->
<?php
// Dynamic calculation of $base_url based on script directory depth relative to project root
$root_dir = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$script_dir = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'] ?? '')));

if ($root_dir && $script_dir && strpos($script_dir, $root_dir) === 0) {
    $relative_path = trim(substr($script_dir, strlen($root_dir)), '/');
    if ($relative_path === '') {
        $base_url = '';
    } else {
        $depth = count(explode('/', $relative_path));
        $base_url = str_repeat('../', $depth);
    }
} else {
    if (!isset($base_url)) {
        $base_url = '';
    }
}
?>
<style>
/* Footer Styles - Dark Navy Theme */
.site-footer {
    width: 100%;
    background-color: var(--nb-navy-dark, #053B50);
    color: var(--nb-white, #FFFFFF);
    border-top: var(--border-thick, 3.5px solid #000000);
    padding: 44px 0 36px 0;
    font-family: var(--font-body, 'Plus Jakarta Sans', sans-serif);
    margin-top: 60px;
}

.site-footer .footer-container {
    width: 92%;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 40px;
}

/* Left Section */
.footer-left {
    flex: 1 1 500px;
    max-width: 580px;
}

.footer-brand-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
}

.footer-logo-img {
    height: 52px;
    width: auto;
    object-fit: contain;
    background: #FFFFFF;
    padding: 4px 8px;
    border: var(--border-thin, 2px solid #000000);
    border-radius: var(--radius-btn, 10px);
    box-shadow: 2px 2px 0px #000000;
}

.footer-brand-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.footer-school-title {
    font-family: var(--font-heading, 'Space Grotesk', sans-serif);
    font-size: 1.15rem;
    font-weight: 800;
    color: #FFFFFF;
    line-height: 1.2;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: -0.01em;
}

.footer-school-sub {
    font-family: var(--font-heading, 'Space Grotesk', sans-serif);
    font-size: 1.15rem;
    font-weight: 800;
    color: #FFFFFF;
    line-height: 1.2;
    margin: 0 0 8px 0;
    text-transform: uppercase;
    letter-spacing: -0.01em;
}

.footer-badge {
    display: inline-block;
    background: #F37023;
    color: #FFFFFF;
    font-size: 0.68rem;
    font-weight: 800;
    font-style: italic;
    letter-spacing: 0.03em;
    padding: 4px 14px;
    border-radius: 50px;
    text-transform: uppercase;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.footer-description {
    font-size: 0.84rem;
    font-weight: 500;
    line-height: 1.6;
    color: #E2E8F0;
    margin-bottom: 24px;
    text-align: justify;
}

/* Social Links */
.footer-social-links {
    display: flex;
    align-items: center;
    gap: 12px;
}

.footer-social-links a.social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: var(--nb-yellow, #FFE600);
    color: #000000;
    border: var(--border-medium, 2.5px solid #000000);
    border-radius: var(--radius-btn, 10px);
    box-shadow: var(--shadow-sm, 3px 3px 0px #000000);
    transition: all 0.2s ease;
    text-decoration: none;
}

.footer-social-links a.social-btn:hover {
    transform: translate(-2px, -2px);
    box-shadow: 4px 4px 0px #000000;
    color: #FFFFFF;
}

.footer-social-links a.social-youtube:hover {
    background-color: #FF0000;
}

.footer-social-links a.social-facebook:hover {
    background-color: #1877F2;
}

.footer-social-links a.social-instagram:hover {
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
}

.footer-social-links a.social-tiktok:hover {
    background-color: #000000;
}

/* Right Section - Kompetensi Keahlian */
.footer-right {
    flex: 1 1 450px;
    max-width: 560px;
}

.footer-section-header {
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2.5px solid #38BDF8;
    display: block;
    width: 100%;
}

.footer-section-title {
    font-family: var(--font-heading, 'Space Grotesk', sans-serif);
    font-size: 1.1rem;
    font-weight: 800;
    color: #FFFFFF;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin: 0;
}

.keahlian-columns {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px 20px;
}

.keahlian-item {
    font-size: 0.85rem;
    font-weight: 600;
    color: #38BDF8;
    text-decoration: none;
    line-height: 1.45;
    transition: color 0.15s ease, transform 0.15s ease;
    display: block;
}

.keahlian-item:hover {
    color: var(--nb-yellow, #FFE600);
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 899px) {
    .site-footer .footer-container {
        flex-direction: column;
        gap: 32px;
    }
    
    .footer-left, .footer-right {
        max-width: 100%;
        flex: 1 1 100%;
    }
}

@media (max-width: 576px) {
    .keahlian-columns {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .footer-brand-header {
        flex-direction: row;
        align-items: flex-start;
    }
    
    .footer-school-title, .footer-school-sub {
        font-size: 1rem;
    }
}
</style>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Left Column: School Header, Description & Social Links -->
        <div class="footer-left">
            <div class="footer-brand-header">
                <img src="<?php echo $base_url; ?>assets/logo2.webp" alt="Logo SMKS SUKAPURA" class="footer-logo-img" onerror="this.onerror=null; this.src='<?php echo $base_url; ?>assets/favicon.ico';">
                <div class="footer-brand-info">
                    <h3 class="footer-school-title">SMK PUSAT KEUNGGULAN (PK)</h3>
                    <h4 class="footer-school-sub">SUKAPURA KAB. TASIKMALAYA</h4>
                    <span class="footer-badge">THE BEST VOCATIONAL SCHOOL IN TASIKMALAYA</span>
                </div>
            </div>
            <p class="footer-description">
                SMK PK (Pusat Keunggulan) merupakan program pengembangan SMK dengan kompetensi keahlian (Busana) dalam peningkatan kualitas dan kinerja, yang diperkuat melalui kemitraan dan penyelarasan dengan dunia usaha, dunia industri, dan dunia kerja, yang akhirnya menjadi SMK rujukan yang dapat berfungsi sebagai sekolah penggerak dan pusat peningkatan kualitas dan kinerja SMK.
            </p>
            <div class="footer-social-links">
                <a href="https://www.facebook.com/people/Smks-Sukapura-Kab-Tasikmalaya/61555166803090/" target="_blank" aria-label="Facebook" class="social-btn social-facebook">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="https://www.instagram.com/smksukapurakab.tasikmalaya/" target="_blank" aria-label="Instagram" class="social-btn social-instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="https://www.youtube.com/@smksukapurakab.tasikmalaya" target="_blank" aria-label="YouTube" class="social-btn social-youtube">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>
                <a href="https://www.tiktok.com/@smksukapurakabtas" target="_blank" aria-label="TikTok" class="social-btn social-tiktok">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.57-1.32 1.55-1.32 2.55 0 1.25.72 2.41 1.83 2.97.98.49 2.15.53 3.16.14 1.05-.4 1.84-1.29 2.12-2.37.15-.56.17-1.14.17-1.71V0z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Right Column: Kompetensi Keahlian -->
        <div class="footer-right">
            <div class="footer-section-header">
                <h3 class="footer-section-title">KOMPETENSI KEAHLIAN</h3>
            </div>
            <div class="keahlian-columns">
                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Teknik Jaringan Komputer dan Telekomunikasi (TJKT)</a>
                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Teknik Sepeda Motor (TSM)</a>

                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Pemodelan Perangkat Lunak dan Gim (PPLG)</a>
                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Desain Komunikasi Visual (DKV)</a>

                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Bisnis Digital (BD)</a>
                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Akuntansi (AK)</a>

                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Manajemen Perkantoran (MP)</a>
                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Kuliner (KLN)</a>

                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Desain Produksi Busana (DPB)</a>
                <a href="<?php echo $base_url; ?>index.php#keahlian" class="keahlian-item">Agribisnis (AB)</a>
            </div>
        </div>
    </div>
</footer>
