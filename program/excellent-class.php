<?php
$base_url = '../';
$pageTitle = 'program-unggulan';
$subPageTitle = 'excellent-class';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excellent Class | SMKS SUKAPURA</title>
    <meta name="description" content="Excellent Class SMKS SUKAPURA — Program unggulan untuk siswa berprestasi dengan pembelajaran intensif dan pembinaan akademik berkualitas tinggi.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- ── HERO ── -->
    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Excellent Class SMKS Sukapura">
        <div class="background-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-star"></i> PROGRAM UNGGULAN</div>
            <h1 class="hero-heading">Excellent Class</h1>
            <p class="hero-sub">SMKS Sukapura Kab. Tasikmalaya</p>
        </div>
    </header>

    <main class="exc-main">

        <!-- ── INTRO SECTION ── -->
        <section class="exc-intro-section">
            <div class="exc-intro-grid">
                <!-- Foto -->
                <div class="exc-photo-wrap reveal-left">
                    <img src="../assets/prog/exce.webp" alt="Foto Excellent Class SMKS Sukapura" class="exc-photo">
                    <div class="exc-photo-badge"><i class="ph-bold ph-certificate"></i> Kelas Unggulan</div>
                </div>
                <!-- Deskripsi -->
                <div class="exc-intro-text reveal-right">
                    <div class="exc-section-badge"><i class="ph-bold ph-info"></i> TENTANG PROGRAM</div>
                    <h2 class="exc-title">Apa itu Excellent Class?</h2>
                    <p class="exc-desc">
                        <strong>Excellent Class</strong> adalah program kelas unggulan yang dirancang khusus untuk siswa-siswi SMKS Sukapura yang memiliki motivasi tinggi, semangat belajar, dan potensi akademik luar biasa.
                    </p>
                    <p class="exc-desc">
                        Program ini menghadirkan pendampingan intensif dari guru-guru terbaik, metode pembelajaran inovatif, serta kurikulum yang diperkaya dengan materi pengembangan diri untuk mempersiapkan siswa menjadi lulusan yang <strong>berprestasi, berkarakter, dan siap berkarir</strong>.
                    </p>
                    <div class="exc-highlight-row">
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">45+</span>
                            <span class="exc-highlight-label">Siswa per Angkatan</span>
                        </div>
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">100%</span>
                            <span class="exc-highlight-label">Bimbingan Intensif</span>
                        </div>
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">3×</span>
                            <span class="exc-highlight-label">Sesi Belajar Tambahan</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── KEUNGGULAN ── -->
        <section class="exc-features-section">
            <div class="exc-section-badge reveal"><i class="ph-bold ph-lightning"></i> KEUNGGULAN PROGRAM</div>
            <h2 class="exc-title reveal">Mengapa Memilih Excellent Class?</h2>
            <p class="exc-sub reveal">Kami memberikan pengalaman belajar terbaik yang tidak akan Anda temukan di tempat lain.</p>

            <div class="exc-features-grid">
                <div class="exc-feature-card card-yellow reveal reveal-delay-1">
                    <div class="exc-feature-icon"><i class="ph-bold ph-chalkboard-teacher"></i></div>
                    <h3>Guru Berpengalaman</h3>
                    <p>Diampu oleh tenaga pendidik pilihan dengan kompetensi tinggi dan dedikasi penuh dalam membimbing siswa berprestasi.</p>
                </div>
                <div class="exc-feature-card card-blue reveal reveal-delay-2">
                    <div class="exc-feature-icon"><i class="ph-bold ph-book-open"></i></div>
                    <h3>Kurikulum Diperkaya</h3>
                    <p>Materi ajar diperkaya dengan konten pengembangan karakter, literasi, dan numerasi di atas standar kurikulum reguler.</p>
                </div>
                <div class="exc-feature-card card-green reveal reveal-delay-3">
                    <div class="exc-feature-icon"><i class="ph-bold ph-trophy"></i></div>
                    <h3>Persiapan Kompetisi</h3>
                    <p>Siswa dibimbing untuk aktif mengikuti lomba akademik, olimpiade, dan kompetisi keterampilan di tingkat kabupaten hingga nasional.</p>
                </div>
                <div class="exc-feature-card card-pink reveal reveal-delay-1">
                    <div class="exc-feature-icon"><i class="ph-bold ph-users-three"></i></div>
                    <h3>Kelas Kecil & Efektif</h3>
                    <p>Rasio siswa-guru yang ideal memastikan setiap siswa mendapat perhatian penuh dan bimbingan personal yang maksimal.</p>
                </div>
                <div class="exc-feature-card card-purple reveal reveal-delay-2">
                    <div class="exc-feature-icon"><i class="ph-bold ph-clock-countdown"></i></div>
                    <h3>Jam Belajar Tambahan</h3>
                    <p>Program belajar tambahan di luar jam reguler untuk memperkuat pemahaman dan mempersiapkan ujian dengan lebih matang.</p>
                </div>
                <div class="exc-feature-card card-orange reveal reveal-delay-3">
                    <div class="exc-feature-icon"><i class="ph-bold ph-rocket-launch"></i></div>
                    <h3>Siap Kerja & Kuliah</h3>
                    <p>Lulusan Excellent Class memiliki nilai akademik unggul dan rekam jejak prestasi yang kuat untuk memasuki dunia kerja atau jenjang pendidikan tinggi.</p>
                </div>
            </div>
        </section>

        <!-- ── SYARAT PENDAFTARAN ── -->
        <section class="exc-syarat-section">
            <div class="exc-syarat-inner">
                <div class="exc-syarat-text reveal-left">
                    <div class="exc-section-badge"><i class="ph-bold ph-clipboard-text"></i> PERSYARATAN</div>
                    <h2 class="exc-title">Syarat Pendaftaran</h2>
                    <p class="exc-desc">Berikut adalah persyaratan untuk bergabung dalam program Excellent Class SMKS Sukapura:</p>
                    <ul class="exc-syarat-list">
                        <li><i class="ph-bold ph-check-circle"></i> Siswa berprestasi<strong>5</strong>besar di SMP/MTS</li>
                        <li><i class="ph-bold ph-check-circle"></i> Lulus seleksi tertulis yang diselenggarakan sekolah</li>
                        <li><i class="ph-bold ph-check-circle"></i> Memiliki rekam prestasi akademik atau non-akademik</li>
                        <li><i class="ph-bold ph-check-circle"></i> Surat rekomendasi dari guru wali kelas</li>
                        <li><i class="ph-bold ph-check-circle"></i> Bersedia mengikuti seluruh program dan kegiatan tambahan</li>
                        <li><i class="ph-bold ph-check-circle"></i> Mendapat persetujuan orang tua / wali</li>
                    </ul>
                </div>
                <div class="exc-syarat-cta reveal-right">
                    <div class="exc-cta-card">
                        <i class="ph-bold ph-envelope-simple exc-cta-icon"></i>
                        <h3>Tertarik Mendaftar?</h3>
                        <p>Hubungi kami untuk informasi lebih lanjut mengenai jadwal seleksi dan pendaftaran Excellent Class.</p>
                        <a href="https://wa.me/6282119277417?text=Halo%2C%20saya%20ingin%20bertanya%20mengenai%20program%20Excellent%20Class%20SMKS%20Sukapura." target="_blank" class="exc-cta-btn">
                            <i class="ph-bold ph-whatsapp-logo"></i> Hubungi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>