<?php
$pageTitle = "beranda";

// Ambil info SPMB terkini dari sistem database SPMB
$spmbSettings = [];
if (file_exists(__DIR__ . '/spmb/config.php')) {
    require_once __DIR__ . '/spmb/config.php';
    if (function_exists('get_all_settings')) {
        try {
            $spmbSettings = get_all_settings();
        } catch (Exception $e) {
            // fallback
        }
    }
}

$statusInfo = function_exists('get_spmb_status_info') ? get_spmb_status_info($spmbSettings) : [
    'isOpen' => true,
    'label' => 'Sedang Dibuka',
    'badgeColor' => '#16A34A',
    'note' => ''
];
$isSpmbBuka = $statusInfo['isOpen'];
$spmbTahun = $spmbSettings['tahun_ajaran'] ?? '2027-2028';
$spmbGelombang = $spmbSettings['gelombang'] ?? 'Gelombang 1';
$spmbPeriode = $spmbSettings['periode_gelombang'] ?? '';
$spmbJudul = !empty($spmbSettings['pengumuman_header']) ? $spmbSettings['pengumuman_header'] : 'Pendaftaran Peserta Didik Baru (SPMB)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | SMKS SUKAPURA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
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
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>

<?php include 'components/navbar.php'; ?>

<div class="hero-section">
    <div class="carousel">
        <div class="carousel-inner">
            <div class="carousel-slide active">
                <img src="assets/depan.webp" alt="SMKS Sukapura" class="img-building-front">
                <div class="carousel-caption">
                    <span class="caption-tag">BELAJAR & BERKARYA</span>
                    <h2>Tempat Tumbuh Bakat dan Prestasi</h2>
                    <p>Kami menghadirkan pendidikan berkualitas yang mengembangkan akademik, keterampilan, dan nilai karakter untuk masa depan yang lebih cerah.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="assets/bawah.webp" alt="Fasilitas SMKS Sukapura" class="img-building-courtyard">
                <div class="carousel-caption">
                    <span class="caption-tag">FASILITAS UNGGULAN</span>
                    <h2>Fasilitas Praktik Modern</h2>
                    <p>Mendukung pembelajaran interaktif berbasis standar industri terbaru untuk semua kompetensi keahlian.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="assets/welcome.webp" alt="Kemitraan SMKS Sukapura" class="img-banner">
                <div class="carousel-caption">
                    <span class="caption-tag">KARIR & MASA DEPAN</span>
                    <h2>Kemitraan Industri Luas</h2>
                    <p>Menjamin peluang magang dan penyerapan kerja lulusan terbaik di berbagai perusahaan terkemuka.</p>
                </div>
            </div>
        </div>


        <!-- Indicators -->
        <div class="carousel-indicators">
            <span class="indicator active" data-slide="0"></span>
            <span class="indicator" data-slide="1"></span>
            <span class="indicator" data-slide="2"></span>
        </div>
    </div>

    <!-- Floating Hero Stats Bar -->
    <div class="hero-stats-wrapper">
        <div class="hero-stats-container">
            <div class="stat-item">
                <div class="stat-value">A</div>
                <div class="stat-label">Akreditasi</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">1300+</div>
                <div class="stat-label">Siswa</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">80+</div>
                <div class="stat-label">Guru & Staf</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">10+</div>
                <div class="stat-label">Ekstrakurikuler</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">10</div>
                <div class="stat-label">Jurusan</div>
            </div>
        </div>
    </div>
</div>

<main style="width: 92%; max-width: 1200px; margin: 75px auto 50px; min-height: 400px;">
    <!-- SPMB BANNER ANNOUNCEMENT (DYNAMIC FROM ADMIN) -->
    <div style="margin-top: 15px; margin-bottom: 35px; background: #FFE600; border: 2.5px solid #000; border-radius: 14px; box-shadow: 4px 4px 0px #000; padding: 14px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; background: #0A4D68; color: #FFE600; border: 2px solid #000; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; box-shadow: 2px 2px 0px #000; flex-shrink: 0;">
                <i class="ph-bold ph-megaphone"></i>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 3px;">
                    <span style="font-size: 0.75rem; font-weight: 800; background: #FFF; border: 1.5px solid #000; padding: 2px 8px; border-radius: 999px; text-transform: uppercase;">
                        TA <?php echo htmlspecialchars($spmbTahun); ?> &bull; <?php echo htmlspecialchars($spmbGelombang); ?>
                    </span>
                    <span style="font-size: 0.72rem; font-weight: 800; background: <?php echo $statusInfo['badgeColor']; ?>; color: #FFF; border: 1.5px solid #000; padding: 2px 7px; border-radius: 999px; text-transform: uppercase;">
                        <?php echo htmlspecialchars($statusInfo['label']); ?>
                    </span>
                </div>
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 1.15rem; font-weight: 800; color: #000; margin: 0; line-height: 1.2;">
                    <?php echo htmlspecialchars($spmbJudul); ?>
                </h3>
                <?php if (!empty($spmbPeriode)): ?>
                    <div style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-top: 3px; display: flex; align-items: center; gap: 5px;">
                        <i class="ph-bold ph-calendar"></i> Periode: <?php echo htmlspecialchars($spmbPeriode); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($isSpmbBuka): ?>
                <a href="spmb/daftar.php" style="background: #0A4D68; color: #FFF; font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 0.88rem; padding: 9px 18px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-pencil-simple"></i> Daftar Sekarang
                </a>
                <a href="spmb/index.php" style="background: #FFF; color: #000; font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 0.88rem; padding: 9px 18px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    Info Lengkap <i class="ph-bold ph-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="spmb/index.php" style="background: #0A4D68; color: #FFF; font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 0.88rem; padding: 9px 18px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-info"></i> Info &amp; Alur SPMB
                </a>
                <a href="spmb/cek-status.php" style="background: #FFF; color: #000; font-family: 'Space Grotesk', sans-serif; font-weight: 800; font-size: 0.88rem; padding: 9px 18px; border: 2px solid #000; border-radius: 8px; box-shadow: 2.5px 2.5px 0px #000; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-magnifying-glass"></i> Cek Status
                </a>
            <?php endif; ?>
        </div>
    </div>

    <section class="sambutan-section">
        <div class="section-header reveal">
            <h2 class="section-title">Sambutan Kepala Sekolah</h2>
            <p class="section-subtitle">SMK Sukapura Kab. Tasikmalaya</p>
        </div>

        <div class="sambutan-grid">
            <div class="sambutan-left reveal-left">
                <div class="kepala-card">
                    <div class="kepala-img-wrapper">
                        <img src="assets/kepala.png" alt="Dedi Ah Kurniadi, S.Pd., M.Pd." class="kepala-img">
                    </div>
                    <div class="kepala-info">
                        <h3 class="kepala-name">Dedi Ah Kurniadi, S.Pd., M.Pd.</h3>
                        <span class="kepala-title">KEPALA SEKOLAH</span>
                    </div>
                </div>
            </div>

            <div class="sambutan-right reveal-right">
                <div class="sambutan-content">
                    <div class="quote-icon">&ldquo;</div>
                    <div class="greeting-header">
                        <h3 class="greeting-opening">Assalamu'alaikum Warahmatullahi Wabarakatuh,</h3>
                        <p class="greeting-sub">Salam sejahtera untuk kita semua,</p>
                    </div>
                    <div class="sambutan-body">
                        <p>Puji syukur kita panjatkan ke hadirat Allah SWT atas limpahan rahmat dan karunia-Nya sehingga website sekolah ini dapat hadir sebagai sarana informasi, komunikasi, dan inspirasi bagi seluruh warga sekolah serta masyarakat luas.</p>
                        <p>Website ini kami hadirkan sebagai wujud komitmen untuk mendukung <strong>pendidikan yang adaptif</strong> terhadap perkembangan teknologi. Melalui platform ini, kami berharap dapat memberikan akses informasi yang cepat, akurat, dan transparan terkait kegiatan, prestasi, dan layanan sekolah.</p>
                        <p>Kami juga mengundang seluruh siswa, guru, orang tua, dan masyarakat untuk berpartisipasi aktif dalam mengisi dan memanfaatkan website ini demi kemajuan pendidikan. Semoga melalui kerja sama dan sinergi, kita dapat mencetak <strong>generasi yang unggul, berkarakter, dan berprestasi</strong>.</p>
                        <p>Terima kasih atas dukungan dan kepercayaan yang telah diberikan kepada sekolah kami. Mari bersama-sama membangun pendidikan yang lebih baik.</p>
                    </div>
                    <p class="greeting-closing">Waalaikumsalam Warahmatullahi Wabarakatuh.</p>
                    <div class="sambutan-footer-note">
                        <span class="note-location"><i class="ph-bold ph-map-pin"></i> Kab. Tasikmalaya, Jawa Barat</span>
                        <span class="note-tag">SMK SUKAPURA: BISA • BEDA • JUARA • LUAR BIASA!</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="prestasi-section">
        <div class="section-header center reveal">
            <span class="section-badge badge-yellow">PRESTASI & SANG JUARA</span>
            <h2 class="section-title">Prestasi SMK Sukapura</h2>
            <p class="section-subtitle">Ukiran Kebanggaan dan Raihan Juara Siswa-Siswi SMK Sukapura</p>
        </div>

        <div class="prestasi-grid">
            <!-- Card 1: Kiki Nopiansyah -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-1">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/kiki.jpg" alt="Kiki Nopiansyah - Pasanggiri Mojang Jajaka Sukapura" class="prestasi-img">
                    <span class="prestasi-category">MOJANG JAJAKA</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Wakil Gandes</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Mojang Jajaka Sukapura</h3>
                    <p class="prestasi-caption">Terpilih sebagai Pemenang Nominasi Wakil Gandes dalam ajang Pemilihan Duta Pariwisata, Budaya, dan Ekonomi Kreatif.</p>
                </div>
            </div>

            <!-- Card 2: Shota Abdullah -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-2">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/shota.jpg" alt="Shota Abdullah - Pasanggiri Mojang Jajaka Sukapura" class="prestasi-img">
                    <span class="prestasi-category">MOJANG JAJAKA</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Pemenang Wakil 2</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Mojang Jajaka Sukapura</h3>
                    <p class="prestasi-caption">Terpilih sebagai Pemenang Nominasi Wakil 2 dalam ajang Pemilihan Duta Pariwisata, Budaya, dan Ekonomi Kreatif.</p>
                </div>
            </div>

            <!-- Card 3: OSIS -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-3">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/osis.jpg" alt="OSIS SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">ORGANISASI SISWA</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-medal"></i> Apresiasi</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Penghargaan Kepemimpinan OSIS</h3>
                    <p class="prestasi-caption">Penghargaan untuk pengurus OSIS SMK Sukapura atas dedikasi dan suksesnya berbagai program kerja kreatif.</p>
                </div>
            </div>

            <!-- Card 4: Paskibra 1 -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-1">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/paskibra.jpg" alt="Paskibra Pasuja SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">PASUJA PASKIBRA</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Juara Utama</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Juara Utama Paskibra Sukapura</h3>
                    <p class="prestasi-caption">Tim Paskibra SMK Sukapura (Pasuja) berhasil mendulang prestasi gemilang dalam kejuaraan baris-berbaris.</p>
                </div>
            </div>

            <!-- Card 5: Paskibra 2 -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-2">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/paskibra2.jpg" alt="Trofi Paskibra SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">PASUJA PASKIBRA</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Trofi Kemenangan</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Trofi Kebanggaan Paskibra</h3>
                    <p class="prestasi-caption">Penyerahan piala penghargaan tingkat kabupaten dan provinsi atas ketangkasan formasi tim Pasuja.</p>
                </div>
            </div>

            <!-- Card 6: Volly Putri -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-3">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/vollypi.jpg" alt="Tim Voli Putri SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">OLAHRAGA VOLI</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Juara Voli PI</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Juara Tim Bola Voli Putri</h3>
                    <p class="prestasi-caption">Prestasi luar biasa tim bola voli putri SMK Sukapura merebut gelar juara dalam turnamen antar pelajar.</p>
                </div>
            </div>

            <!-- Card 7: Volly Putra -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-1">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/vollypa.jpg" alt="Tim Voli Putra SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">OLAHRAGA VOLI</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Juara Voli PA</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Juara Tim Bola Voli Putra</h3>
                    <p class="prestasi-caption">Semangat juang dan kekompakan tim bola voli putra SMK Sukapura membuahkan trofi kejuaraan regional.</p>
                </div>
            </div>

            <!-- Card 8: MGMP PAI -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-2">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/mgmp.jpg" alt="Juara Pentas MGMP PAI SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">LOMBA PAI</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-trophy"></i> Juara MGMP PAI</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Juara Ajang Pentas MGMP PAI</h3>
                    <p class="prestasi-caption">Prestasi membanggakan siswa-siswi SMK Sukapura dalam Pekan Keterampilan dan Seni Pendidikan Agama Islam (Pentas PAI) MGMP.</p>
                </div>
            </div>

            <!-- Card 9: Poster Digital -->
            <div class="prestasi-card reveal reveal-scale reveal-delay-3">
                <div class="prestasi-img-wrapper">
                    <img src="assets/prestasi/poster.jpg" alt="Juara Lomba Poster Digital SMK Sukapura" class="prestasi-img">
                    <span class="prestasi-category">DESAIN DIGITAL</span>
                    <span class="prestasi-rank"><i class="ph-bold ph-palette"></i> Juara Poster Digital</span>
                </div>
                <div class="prestasi-content">
                    <h3 class="prestasi-title">Juara Lomba Desain Poster Digital</h3>
                    <p class="prestasi-caption">Kreativitas dan keahlian siswa SMK Sukapura dalam merancang poster digital artistik yang edukatif dan inspiratif.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="keahlian-section" id="keahlian">
        <div class="section-header center reveal">
            <span class="section-badge badge-yellow"><i class="ph-bold ph-graduation-cap"></i> PROGRAM KEAHLIAN</span>
            <h2 class="section-title">Konsentrasi Keahlian Unggulan</h2>
            <p class="section-subtitle">10 Program Keahlian Siap Kerja Berbasis Industri &amp; Teknologi di SMKS Sukapura</p>
        </div>

        <div class="keahlian-grid">
            <div class="keahlian-card reveal reveal-scale reveal-delay-1">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-blue">TI</span>
                    <span class="keahlian-code">PPLG</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/pplg.png" alt="Pengembangan Perangkat Lunak & Gim (PPLG)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">PPLG</h3>
                    <p class="keahlian-desc">Pengembangan Perangkat Lunak &amp; Gim</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-2">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-purple">TI</span>
                    <span class="keahlian-code">TJKT</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/tjkt.png" alt="Teknik Jaringan Komputer & Telekomunikasi (TJKT)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">TJKT</h3>
                    <p class="keahlian-desc">Teknik Jaringan Komputer &amp; Telekomunikasi</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-3">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-pink">TI</span>
                    <span class="keahlian-code">DKV</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/dkv.png" alt="Desain Komunikasi Visual (DKV)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">DKV</h3>
                    <p class="keahlian-desc">Desain Komunikasi Visual</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-4">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-green">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">MPLB</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/mp.png" alt="Manajemen Perkantoran & Layanan Bisnis (MP)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">MPLB</h3>
                    <p class="keahlian-desc">Manajemen Perkantoran &amp; Layanan Bisnis</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-5">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-yellow">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">AKL</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/ak.png" alt="Akuntansi & Keuangan Lembaga (AK)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">AKL</h3>
                    <p class="keahlian-desc">Akuntansi &amp; Keuangan Lembaga</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-1">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-orange">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">BD</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/bd.png" alt="Bisnis Digital (BD)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">BD</h3>
                    <p class="keahlian-desc">Pemasaran &amp; E-Commerce</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-2">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-dark">BISNIS MANAJEMEN</span>
                    <span class="keahlian-code">AB</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/ab.png" alt="Teknik Bodi Otomotif (AB)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">AB</h3>
                    <p class="keahlian-desc">Bisnis Pertanian</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-3">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-red">PARAWISATA</span>
                    <span class="keahlian-code">DPB</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/dpb.png" alt="Desain & Produksi Busana (DPB)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">DPB</h3>
                    <p class="keahlian-desc">Desain &amp; Produksi Busana</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-4">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-teal">PARIWISATA</span>
                    <span class="keahlian-code">KLN</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/kln.png" alt="Kuliner (KLN)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">Kuliner</h3>
                    <p class="keahlian-desc">Tata Boga &amp; Seni Kuliner</p>
                </div>
            </div>

            <div class="keahlian-card reveal reveal-scale reveal-delay-5">
                <div class="keahlian-card-top">
                    <span class="keahlian-tag tag-dark">OTOMOTIF</span>
                    <span class="keahlian-code">TSM</span>
                </div>
                <div class="keahlian-img-box">
                    <img src="assets/jurusan/tsm.png" alt="Teknik Sepeda Motor (TSM)" class="keahlian-img">
                </div>
                <div class="keahlian-card-body">
                    <h3 class="keahlian-title">TSM</h3>
                    <p class="keahlian-desc">Teknik Sepeda Motor </p>
                </div>
            </div>
        </div>
    </section>

    <section class="mitra-section">
        <div class="section-header center reveal">
            <h2 class="section-title">Mitra Kerja Sama</h2>
            <p class="section-subtitle">Dunia Usaha & Dunia Industri Terkemuka</p>
        </div>

        <div class="mitra-carousel-wrapper reveal">
            <button class="mitra-control prev" id="mitra-prev" aria-label="Mitra Sebelumnya">
                <i class="ph-bold ph-caret-left"></i>
            </button>
            <div class="mitra-carousel-track-container" id="mitra-track-container">
                <div class="mitra-carousel-track" id="mitra-track">
                    <div class="mitra-card"><img src="assets/mitra/abl.png" alt="ABL"></div>
                    <div class="mitra-card"><img src="assets/mitra/ahm.webp" alt="AHM"></div>
                    <div class="mitra-card"><img src="assets/mitra/bni.png" alt="BNI"></div>
                    <div class="mitra-card"><img src="assets/mitra/btn.webp" alt="BTN"></div>
                    <div class="mitra-card"><img src="assets/mitra/byu.webp" alt="by.U"></div>
                    <div class="mitra-card"><img src="assets/mitra/gamelab.png" alt="Gamelab"></div>
                    <div class="mitra-card"><img src="assets/mitra/joyday.webp" alt="Joyday"></div>
                    <div class="mitra-card"><img src="assets/mitra/pnm.webp" alt="PNM"></div>
                    <div class="mitra-card"><img src="assets/mitra/prilude.png" alt="Prilude"></div>
                    <div class="mitra-card"><img src="assets/mitra/rabbani.png" alt="Rabbani"></div>
                    <div class="mitra-card"><img src="assets/mitra/radar.png" alt="Radar"></div>
                    <div class="mitra-card"><img src="assets/mitra/suzuki.webp" alt="Suzuki"></div>
                    <div class="mitra-card"><img src="assets/mitra/telkom.webp" alt="Telkom Indonesia"></div>
                </div>
            </div>
            <button class="mitra-control next" id="mitra-next" aria-label="Mitra Selanjutnya">
                <i class="ph-bold ph-caret-right"></i>
            </button>
        </div>
    </section>
</main>

<?php include 'components/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>