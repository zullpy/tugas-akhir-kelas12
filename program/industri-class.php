<?php
$base_url = '../';
$pageTitle = 'program-unggulan';
$subPageTitle = 'industri-class';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industri Class | SMKS SUKAPURA</title>
    <meta name="description" content="Industri Class SMKS SUKAPURA — Program unggulan berbasis industri yang menghubungkan siswa langsung dengan dunia kerja melalui magang dan kolaborasi mitra industri.">
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

    <!-- HERO -->
    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Industri Class SMKS Sukapura">
        <div class="background-overlay industri-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-factory"></i> PROGRAM UNGGULAN</div>
            <h1 class="hero-heading">Industri Class</h1>
            <p class="hero-sub">SMKS Sukapura Kab. Tasikmalaya</p>
        </div>
    </header>

    <main class="exc-main">

        <!-- INTRO SECTION -->
        <section class="exc-intro-section">
            <div class="exc-intro-grid">
                <div class="exc-photo-wrap reveal-left">
                    <img src="../assets/prog/indrustri.webp" alt="Foto Industri Class SMKS Sukapura" class="exc-photo">
                    <div class="exc-photo-badge industri-photo-badge"><i class="ph-bold ph-factory"></i> Kelas Industri</div>
                </div>
                <div class="exc-intro-text reveal-right">
                    <div class="exc-section-badge industri-badge"><i class="ph-bold ph-info"></i> TENTANG PROGRAM</div>
                    <h2 class="exc-title">Apa itu Industri Class?</h2>
                    <p class="exc-desc">
                        <strong>Industri Class</strong> adalah program kelas unggulan yang dirancang khusus untuk menjembatani dunia pendidikan dengan dunia industri secara nyata. Siswa dipersiapkan menjadi tenaga kerja profesional yang siap diserap langsung oleh mitra industri terkemuka.
                    </p>
                    <p class="exc-desc">
                        Melalui kolaborasi erat dengan perusahaan-perusahaan mitra, program ini menghadirkan <strong>kurikulum berbasis industri, magang terstruktur, sertifikasi kompetensi, dan rekrutmen langsung</strong> sehingga lulusan memiliki nilai lebih di pasar kerja.
                    </p>
                    <div class="exc-highlight-row">
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">10+</span>
                            <span class="exc-highlight-label">Mitra Industri</span>
                        </div>
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">6 Bln</span>
                            <span class="exc-highlight-label">Program Magang</span>
                        </div>
                        <div class="exc-highlight-item">
                            <span class="exc-highlight-num">95%</span>
                            <span class="exc-highlight-label">Terserap Industri</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- KEUNGGULAN -->
        <section class="exc-features-section">
            <div class="exc-section-badge industri-badge reveal"><i class="ph-bold ph-lightning"></i> KEUNGGULAN PROGRAM</div>
            <h2 class="exc-title reveal">Mengapa Memilih Industri Class?</h2>
            <p class="exc-sub reveal">Program yang menjembatani pendidikan vokasi dengan kebutuhan nyata dunia industri modern.</p>

            <div class="exc-features-grid">
                <div class="exc-feature-card card-yellow reveal reveal-delay-1">
                    <div class="exc-feature-icon"><i class="ph-bold ph-buildings"></i></div>
                    <h3>Kurikulum Industri</h3>
                    <p>Materi pembelajaran disusun bersama mitra industri agar selaras dengan standar kompetensi dan kebutuhan kerja terkini.</p>
                </div>
                <div class="exc-feature-card card-blue reveal reveal-delay-2">
                    <div class="exc-feature-icon"><i class="ph-bold ph-briefcase"></i></div>
                    <h3>Magang Terstruktur</h3>
                    <p>Program magang 6 bulan di perusahaan mitra dengan pembimbing lapangan berpengalaman dan evaluasi berkala.</p>
                </div>
                <div class="exc-feature-card card-green reveal reveal-delay-3">
                    <div class="exc-feature-icon"><i class="ph-bold ph-seal-check"></i></div>
                    <h3>Sertifikasi Kompetensi</h3>
                    <p>Siswa difasilitasi mengikuti uji kompetensi bersertifikat nasional untuk meningkatkan daya saing di dunia kerja.</p>
                </div>
                <div class="exc-feature-card card-pink reveal reveal-delay-1">
                    <div class="exc-feature-icon"><i class="ph-bold ph-handshake"></i></div>
                    <h3>Rekrutmen Langsung</h3>
                    <p>Lulusan terbaik mendapat peluang rekrutmen langsung oleh mitra industri tanpa proses seleksi umum yang panjang.</p>
                </div>
                <div class="exc-feature-card card-purple reveal reveal-delay-2">
                    <div class="exc-feature-icon"><i class="ph-bold ph-chalkboard-teacher"></i></div>
                    <h3>Guru Tamu Industri</h3>
                    <p>Praktisi industri aktif hadir sebagai guru tamu untuk berbagi pengalaman dan pengetahuan langsung dari lapangan.</p>
                </div>
                <div class="exc-feature-card card-orange reveal reveal-delay-3">
                    <div class="exc-feature-icon"><i class="ph-bold ph-rocket-launch"></i></div>
                    <h3>Siap Kerja Hari Pertama</h3>
                    <p>Lulusan Industri Class memiliki pengalaman kerja nyata, kompetensi terstandar, dan jaringan profesional sejak dini.</p>
                </div>
            </div>
        </section>

        <!-- MITRA INDUSTRI -->
        <section class="exc-mitra-section">
            <div class="exc-section-badge industri-badge reveal"><i class="ph-bold ph-network"></i> KEMITRAAN</div>
            <h2 class="exc-title reveal">Mitra Industri Kami</h2>
            <p class="exc-sub reveal">Berkolaborasi dengan perusahaan terkemuka untuk memastikan lulusan siap bersaing di tingkat nasional.</p>
            <div class="exc-mitra-grid reveal">
                <div class="exc-mitra-card">
                    <i class="ph-bold ph-factory exc-mitra-icon"></i>
                    <span>Pabrik Tekstil</span>
                </div>
                <div class="exc-mitra-card">
                    <i class="ph-bold ph-device-mobile exc-mitra-icon"></i>
                    <span>Perusahaan Teknologi</span>
                </div>
                <div class="exc-mitra-card">
                    <i class="ph-bold ph-truck exc-mitra-icon"></i>
                    <span>Logistik &amp; Ekspedisi</span>
                </div>
                <div class="exc-mitra-card">
                    <i class="ph-bold ph-storefront exc-mitra-icon"></i>
                    <span>Retail &amp; UMKM</span>
                </div>
                <div class="exc-mitra-card">
                    <i class="ph-bold ph-hospital exc-mitra-icon"></i>
                    <span>Fasilitas Kesehatan</span>
                </div>
                <div class="exc-mitra-card">
                    <i class="ph-bold ph-bank exc-mitra-icon"></i>
                    <span>Perbankan &amp; Keuangan</span>
                </div>
            </div>
        </section>

        <!-- SYARAT PENDAFTARAN -->
        <section class="exc-syarat-section">
            <div class="exc-syarat-inner">
                <div class="exc-syarat-text reveal-left">
                    <div class="exc-section-badge industri-badge"><i class="ph-bold ph-clipboard-text"></i> PERSYARATAN</div>
                    <h2 class="exc-title">Syarat Pendaftaran</h2>
                    <p class="exc-desc">Berikut adalah persyaratan untuk bergabung dalam program Industri Class SMKS Sukapura:</p>
                    <ul class="exc-syarat-list">
                        <li><i class="ph-bold ph-check-circle"></i> Siswa kelas X yang memiliki nilai rata-rata minimal 75</li>
                        <li><i class="ph-bold ph-check-circle"></i> Lulus seleksi wawancara dan tes kompetensi dasar</li>
                        <li><i class="ph-bold ph-check-circle"></i> Memiliki semangat belajar tinggi dan orientasi kerja</li>
                        <li><i class="ph-bold ph-check-circle"></i> Bersedia mengikuti program magang selama 6 bulan</li>
                        <li><i class="ph-bold ph-check-circle"></i> Surat persetujuan orang tua / wali untuk program magang</li>
                        <li><i class="ph-bold ph-check-circle"></i> Bersedia mematuhi tata tertib mitra industri selama magang</li>
                    </ul>
                </div>
                <div class="exc-syarat-cta reveal-right">
                    <div class="exc-cta-card industri-cta-card">
                        <i class="ph-bold ph-factory exc-cta-icon"></i>
                        <h3>Siap Masuk Industri?</h3>
                        <p>Hubungi kami untuk informasi lebih lanjut mengenai jadwal seleksi dan pendaftaran Industri Class.</p>
                        <a href="https://wa.me/6282119277417?text=Halo%2C%20saya%20ingin%20bertanya%20mengenai%20program%20Industri%20Class%20SMKS%20Sukapura." target="_blank" class="exc-cta-btn industri-cta-btn">
                            <i class="ph-bold ph-whatsapp-logo"></i> Hubungi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="<?php echo $base_url; ?>script.js"></script>
</body>
</html>
