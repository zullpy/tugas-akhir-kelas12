<?php
$base_url = '../';
$pageTitle = 'program-unggulan';
$subPageTitle = 'atlet-class';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atlet Class | SMKS SUKAPURA</title>
    <meta name="description" content="Atlet Class SMKS SUKAPURA — Program unggulan untuk siswa atlet berprestasi dengan keseimbangan antara olahraga dan akademik.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="style.css?v=<?php echo @filemtime(__DIR__ . '/style.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- ── HERO ── -->
    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Atlet Class SMKS Sukapura">
        <div class="background-overlay atlet-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-medal"></i> PROGRAM UNGGULAN</div>
            <h1 class="hero-heading">Atlet Class</h1>
            <p class="hero-sub">SMKS Sukapura Kab. Tasikmalaya</p>
        </div>
    </header>

    <main class="exc-main">

        <!-- ── INTRO SECTION ── -->
        <section class="exc-intro-section">
            <div class="exc-intro-grid">
                <!-- Foto -->
                <div class="exc-photo-wrap reveal-left">
                    <img src="../assets/prog/atlet.webp" alt="Foto Atlet Class SMKS Sukapura" class="exc-photo">
                    <div class="exc-photo-badge"><i class="ph-bold ph-trophy"></i> Kelas Atlet</div>
                </div>
                <!-- Deskripsi -->
                <div class="exc-intro-text reveal-right">
                    <div class="exc-section-badge atlet-badge"><i class="ph-bold ph-info"></i> TENTANG PROGRAM</div>
                    <h2 class="exc-title">Apa itu Atlet Class?</h2>
                    <p class="exc-desc">
                        <strong>Atlet Class</strong> adalah program kelas khusus bagi siswa-siswi SMKS Sukapura yang memiliki bakat dan prestasi di bidang olahraga. Program ini hadir untuk menjawab tantangan nyata: bagaimana seorang atlet bisa tetap berprestasi akademik tanpa mengorbankan karir olahraganya.
                    </p>
                    <p class="exc-desc">
                        Dengan jadwal belajar yang fleksibel, pembimbing akademik khusus, dan dukungan penuh dari sekolah, siswa atlet dapat <strong>berlatih, berlomba, sekaligus meraih prestasi akademik</strong> secara bersamaan.
                    </p>
                    <div class="exc-highlight-row">
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">30+</span>
                            <span class="exc-highlight-label">Siswa Atlet Aktif</span>
                        </div>
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">1</span>
                            <span class="exc-highlight-label">Cabang Olahraga</span>
                        </div>
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">★ Prov</span>
                            <span class="exc-highlight-label">Prestasi Tertinggi</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── KEUNGGULAN ── -->
        <section class="exc-features-section">
            <div class="exc-section-badge atlet-badge reveal"><i class="ph-bold ph-lightning"></i> KEUNGGULAN PROGRAM</div>
            <h2 class="exc-title reveal">Mengapa Memilih Atlet Class?</h2>
            <p class="exc-sub reveal">Program yang dirancang agar atlet tidak harus memilih antara olahraga dan pendidikan.</p>

            <div class="exc-features-grid">
                <div class="exc-feature-card card-yellow reveal reveal-delay-1">
                    <div class="exc-feature-icon"><i class="ph-bold ph-calendar-dots"></i></div>
                    <h3>Jadwal Fleksibel</h3>
                    <p>Jam belajar disesuaikan dengan jadwal latihan dan pertandingan, sehingga siswa tidak tertinggal materi akademik.</p>
                </div>
                <div class="exc-feature-card card-blue reveal reveal-delay-2">
                    <div class="exc-feature-icon"><i class="ph-bold ph-person-simple-run"></i></div>
                    <h3>Waktu Latihan Terjaga</h3>
                    <p>Sekolah memberikan keleluasaan penuh untuk mengikuti sesi latihan rutin bersama pelatih tanpa hambatan akademik.</p>
                </div>
                <div class="exc-feature-card card-green reveal reveal-delay-3">
                    <div class="exc-feature-icon"><i class="ph-bold ph-medal"></i></div>
                    <h3>Dukungan Kompetisi</h3>
                    <p>Siswa difasilitasi mengikuti kejuaraan dari tingkat kabupaten, provinsi, hingga nasional dengan dukungan penuh sekolah.</p>
                </div>
                <div class="exc-feature-card card-pink reveal reveal-delay-1">
                    <div class="exc-feature-icon"><i class="ph-bold ph-chalkboard-teacher"></i></div>
                    <h3>Pembimbing Akademik Khusus</h3>
                    <p>Setiap siswa atlet mendapat pendamping akademik yang membantu mengejar ketertinggalan materi saat jadwal padat.</p>
                </div>
                <div class="exc-feature-card card-purple reveal reveal-delay-2">
                    <div class="exc-feature-icon"><i class="ph-bold ph-heartbeat"></i></div>
                    <h3>Kesehatan & Nutrisi</h3>
                    <p>Perhatian khusus diberikan pada kondisi fisik siswa, termasuk panduan nutrisi dan pemulihan pasca latihan intensif.</p>
                </div>
                <div class="exc-feature-card card-orange reveal reveal-delay-3">
                    <div class="exc-feature-icon"><i class="ph-bold ph-rocket-launch"></i></div>
                    <h3>Karir Ganda</h3>
                    <p>Lulusan Atlet Class siap melanjutkan karir di bidang olahraga profesional sekaligus memiliki ijazah SMK yang diakui.</p>
                </div>
            </div>
        </section>

        <!-- ── SYARAT PENDAFTARAN ── -->
        <section class="exc-syarat-section">
            <div class="exc-syarat-inner">
                <div class="exc-syarat-text reveal-left">
                    <div class="exc-section-badge atlet-badge"><i class="ph-bold ph-clipboard-text"></i> PERSYARATAN</div>
                    <h2 class="exc-title">Syarat Pendaftaran</h2>
                    <p class="exc-desc">Berikut adalah persyaratan untuk bergabung dalam program Atlet Class SMKS Sukapura:</p>
                    <ul class="exc-syarat-list">
                        <li><i class="ph-bold ph-check-circle"></i> Memiliki prestasi di cabang olahraga tingkat kecamatan atau lebih tinggi</li>
                        <li><i class="ph-bold ph-check-circle"></i> Terdaftar sebagai anggota aktif club atau pelatnas daerah</li>
                        <li><i class="ph-bold ph-check-circle"></i> Melampirkan sertifikat / piagam kejuaraan olahraga</li>
                        <li><i class="ph-bold ph-check-circle"></i> Surat keterangan dari pelatih atau pengurus cabang olahraga</li>
                        <li><i class="ph-bold ph-check-circle"></i> Bersedia mengikuti aturan program dan ketentuan akademik sekolah</li>
                        <li><i class="ph-bold ph-check-circle"></i> Mendapat persetujuan orang tua / wali</li>
                    </ul>
                </div>
                <div class="exc-syarat-cta reveal-right">
                    <div class="exc-cta-card atlet-cta-card">
                        <i class="ph-bold ph-trophy exc-cta-icon"></i>
                        <h3>Siap Bergabung?</h3>
                        <p>Hubungi kami untuk informasi lebih lanjut mengenai jadwal seleksi dan pendaftaran Atlet Class.</p>
                        <a href="https://wa.me/6282119277417?text=Halo%2C%20saya%20ingin%20bertanya%20mengenai%20program%20Atlet%20Class%20SMKS%20Sukapura." target="_blank" class="exc-cta-btn atlet-cta-btn">
                            <i class="ph-bold ph-whatsapp-logo"></i> Hubungi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="<?php echo $base_url; ?>script.js?v=<?php echo @filemtime(__DIR__ . '/../script.js') ?: time(); ?>"></script>
</body>
</html>