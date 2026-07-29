<?php
$base_url = '../';
$pageTitle = 'profil';
$subPageTitle = 'visi-misi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VISI & MISI | SMKS SUKAPURA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"
    />
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"
    />
    <link
        rel="stylesheet"
        type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"
    />
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Visi Misi">
        <div class="background-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-tree-structure"></i> PROFIL SEKOLAH</div>
            <h1 class="hero-heading">Visi & Misi</h1>
            <p class="hero-sub">SMKS Sukapura Kab. Tasikmalaya</p>
        </div>
    </header>

    <main class="visi-misi-container">
        <!-- Header area with watermark -->
        <!-- <div class="profile-header">
            <h1 class="title">VISI DAN MISI</h1>
        </div> -->

        <!-- VISI SECTION -->
        <section class="visi-section">
            <div class="visi-badge reveal-left">
                <i class="ph-bold ph-compass"></i> VISI SEKOLAH
            </div>
            <div class="visi-card reveal">
                <blockquote class="visi-text">
                    "Menjadikan sekolah yang unggul dengan lulusan yang <span class="highlight-yellow">Berprestasi</span>, <span class="highlight-green">Disiplin</span>, <span class="highlight-blue">Berkarakter baik</span>, <span class="highlight-pink">Inovatif</span>, <span class="highlight-purple">Kreatif</span>, dan <span class="highlight-red">siap berkarir</span>."
                </blockquote>
                <div class="visi-tags">
                    <span class="tag-pill"><i class="ph-bold ph-trophy"></i> Berprestasi</span>
                    <span class="tag-pill"><i class="ph-bold ph-shield-check"></i> Disiplin</span>
                    <span class="tag-pill"><i class="ph-bold ph-heart"></i> Berkarakter Baik</span>
                    <span class="tag-pill"><i class="ph-bold ph-lightbulb"></i> Inovatif</span>
                    <span class="tag-pill"><i class="ph-bold ph-palette"></i> Kreatif</span>
                    <span class="tag-pill"><i class="ph-bold ph-rocket-launch"></i> Siap Berkarir</span>
                </div>
            </div>
        </section>

        <!-- MISI SECTION -->
        <section class="misi-section">
            <div class="misi-header reveal">
                <div class="misi-badge">
                    <i class="ph-bold ph-target"></i> MISI SEKOLAH
                </div>
                <h2 class="misi-title">Langkah Strategis &amp; Komitmen Kami</h2>
            </div>

            <div class="misi-grid">
                <!-- Card 1 -->
                <div class="misi-card reveal reveal-delay-1">
                    <div class="misi-number">01</div>
                    <div class="misi-icon-box color-1">
                        <i class="ph-bold ph-book-open"></i>
                    </div>
                    <div class="misi-content">
                        <h3>Lingkungan Islami &amp; Akhlaq</h3>
                        <p>Mewujudkan Lingkungan Pendidikan yang Islami dan berakhlaq mulia bagi seluruh warga sekolah.</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="misi-card reveal reveal-delay-2">
                    <div class="misi-number">02</div>
                    <div class="misi-icon-box color-2">
                        <i class="ph-bold ph-shield-check"></i>
                    </div>
                    <div class="misi-content">
                        <h3>Budaya Disiplin</h3>
                        <p>Mewujudkan Budaya Disiplin Sekolah yang konsisten dan berintegritas tinggi.</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="misi-card reveal reveal-delay-3">
                    <div class="misi-number">03</div>
                    <div class="misi-icon-box color-3">
                        <i class="ph-bold ph-trophy"></i>
                    </div>
                    <div class="misi-content">
                        <h3>Semangat Keunggulan</h3>
                        <p>Menumbuhkan semangat keunggulan dan kompetitif yang kreatif dan inovatif bagi seluruh warga sekolah.</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="misi-card reveal reveal-delay-1">
                    <div class="misi-number">04</div>
                    <div class="misi-icon-box color-4">
                        <i class="ph-bold ph-lightbulb"></i>
                    </div>
                    <div class="misi-content">
                        <h3>Pembelajaran Inovatif</h3>
                        <p>Mewujudkan Kegiatan pendidikan yang bermutu dan inovatif berbasis proyek (PjBL) serta berbasis masalah (PBL).</p>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="misi-card reveal reveal-delay-2">
                    <div class="misi-number">05</div>
                    <div class="misi-icon-box color-5">
                        <i class="ph-bold ph-users-three"></i>
                    </div>
                    <div class="misi-content">
                        <h3>Manajemen Transparan</h3>
                        <p>Mewujudkan manajemen yang transparan, akuntabel, efektif dan partisipatif.</p>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="misi-card reveal reveal-delay-3">
                    <div class="misi-number">06</div>
                    <div class="misi-icon-box color-6">
                        <i class="ph-bold ph-rocket-launch"></i>
                    </div>
                    <div class="misi-content">
                        <h3>Siap Berkarier</h3>
                        <p>Menyiapkan Lulusan Yang Siap Berkarier dan berdaya saing tinggi di dunia usaha dan industri.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include '../components/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>
