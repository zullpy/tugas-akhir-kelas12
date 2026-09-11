<?php
// spmb/index.php
// Portal Penerimaan Siswa Baru (SPMB) SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'beranda';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

// Statistik pendaftar live
$totalPendaftar = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar`")->fetchColumn();
$statusInfo = get_spmb_status_info($settings);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMB SMKS SUKAPURA | Penerimaan Siswa Baru TA <?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027'); ?></title>
    <meta name="description" content="Portal Resmi Penerimaan Peserta Didik Baru (SPMB) SMKS Sukapura Kabupaten Tasikmalaya. Daftar online mudah, cepat, dan transparan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="spmb.css">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <!-- HERO SECTION -->
    <header class="spmb-hero">
        <div class="spmb-container">
            <div class="spmb-hero-inner">
                <div class="spmb-hero-left">
                    <div class="spmb-badge-pill">
                        <i class="ph-bold ph-sparkle"></i> SPMB TA <?php echo htmlspecialchars($settings['tahun_ajaran'] ?? '2026/2027'); ?>
                    </div>
                    <h1 class="spmb-hero-title">
                        Wujudkan Masa Depan <span>Hebat &amp; Siap Kerja</span> Bersama SMKS Sukapura!
                    </h1>
                    <p class="spmb-hero-subtitle">
                        Pendaftaran Siswa Baru resmi dibuka secara online. Pilih keahlian impianmu dari 10 jurusan unggulan berstandar industri dan raih prestasimu.
                    </p>
                    <div class="spmb-hero-actions">
                        <?php if ($statusInfo['isOpen']): ?>
                            <a href="daftar.php" class="spmb-btn spmb-btn-primary">
                                <i class="ph-bold ph-pencil-simple-line"></i> Daftar Sekarang
                            </a>
                        <?php else: ?>
                            <a href="#alur" class="spmb-btn spmb-btn-secondary" style="background:#F1F5F9; color:#475569;">
                                <i class="ph-bold ph-clock"></i> <?php echo htmlspecialchars($statusInfo['label']); ?>
                            </a>
                        <?php endif; ?>
                        <a href="cek-status.php" class="spmb-btn spmb-btn-secondary">
                            <i class="ph-bold ph-magnifying-glass"></i> Cek Status Pendaftaran
                        </a>
                        <a href="#alur" class="spmb-btn spmb-btn-warning">
                            <i class="ph-bold ph-info"></i> Alur &amp; Syarat
                        </a>
                    </div>
                </div>
                
                <div class="spmb-hero-right">
                    <div class="spmb-hero-card">
                        <div class="spmb-hero-card-header">
                            <h3 class="spmb-hero-card-title">
                                <i class="ph-bold ph-calendar-check"></i> Status SPMB
                            </h3>
                            <span class="spmb-wave-badge"><?php echo htmlspecialchars($settings['gelombang'] ?? 'Gelombang 1'); ?></span>
                        </div>
                        <ul class="spmb-info-list">
                            <li>
                                <i class="ph-bold ph-clock"></i>
                                <div>
                                    <strong>Periode Pendaftaran:</strong><br>
                                    <span><?php echo htmlspecialchars($settings['periode_gelombang'] ?? '01 Feb - 30 Apr 2026'); ?></span>
                                </div>
                            </li>
                            <li style="align-items: flex-start;">
                                <i class="ph-bold ph-money" style="margin-top: 2px;"></i>
                                <div style="width: 100%;">
                                    <strong>Biaya Pendaftaran:</strong>
                                    <ul style="list-style: none; padding-left: 0; margin: 4px 0 0 0; display: flex; flex-direction: column; gap: 3px;">
                                        <li style="display: flex; align-items: flex-start; gap: 6px; font-size: 0.83rem; color: var(--nb-navy); font-weight: 800; line-height: 1.35;">
                                            <span style="color: #0A4D68; font-size: 0.95rem; line-height: 1;">•</span>
                                            <span>Hanya 100.000 (Reguler)</span>
                                        </li>
                                        <li style="display: flex; align-items: flex-start; gap: 6px; font-size: 0.83rem; color: var(--nb-navy); font-weight: 800; line-height: 1.35;">
                                            <span style="color: #0A4D68; font-size: 0.95rem; line-height: 1;">•</span>
                                            <span>50.000 (Prestasi)</span>
                                        </li>
                                        <li style="display: flex; align-items: flex-start; gap: 6px; font-size: 0.83rem; color: var(--nb-navy); font-weight: 800; line-height: 1.35;">
                                            <span style="color: #0A4D68; font-size: 0.95rem; line-height: 1;">•</span>
                                            <span>Gratis (Yatim/Piatu) + Mendapatkan Baju Putih Abu</span>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <i class="ph-bold ph-student"></i>
                                <div>
                                    <strong>Status Pendaftaran:</strong><br>
                                    <span style="color:<?php echo $statusInfo['badgeColor']; ?>; font-weight:800;">● <?php echo htmlspecialchars($statusInfo['label']); ?></span>
                                </div>
                            </li>
                            <li>
                                <i class="ph-bold ph-phone-call"></i>
                                <div>
                                    <strong>Hotline Panitia SPMB:</strong><br>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $settings['hotline_wa'] ?? '6281234567890'); ?>" target="_blank" style="color:var(--nb-navy); text-decoration:underline;">
                                        <?php echo htmlspecialchars($settings['hotline_wa'] ?? '0812-3456-7890'); ?> (WhatsApp)
                                    </a>
                                </div>
                            </li>
                        </ul>
                        <a href="daftar.php" class="spmb-btn spmb-btn-primary" style="width:100%;">
                            <i class="ph-bold ph-arrow-right"></i> Masuk Formulir Registrasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- STATS COUNTER -->
    <section class="spmb-stats-bar">
        <div class="spmb-container">
            <div class="spmb-stats-grid">
                <div class="spmb-stat-box">
                    <div class="spmb-stat-icon"><i class="ph-bold ph-users-four"></i></div>
                    <div>
                        <div class="spmb-stat-num"><?php echo (int)$totalPendaftar; ?></div>
                        <div class="spmb-stat-label">Calon Siswa Terdaftar</div>
                    </div>
                </div>
                <div class="spmb-stat-box">
                    <div class="spmb-stat-icon" style="background:var(--nb-green-light);"><i class="ph-bold ph-graduation-cap"></i></div>
                    <div>
                        <div class="spmb-stat-num">10</div>
                        <div class="spmb-stat-label">Kompetensi Keahlian</div>
                    </div>
                </div>
                <div class="spmb-stat-box">
                    <div class="spmb-stat-icon" style="background:var(--nb-yellow-light);"><i class="ph-bold ph-buildings"></i></div>
                    <div>
                        <div class="spmb-stat-num">50+</div>
                        <div class="spmb-stat-label">Mitra Industri Nasional</div>
                    </div>
                </div>
                <div class="spmb-stat-box">
                    <div class="spmb-stat-icon" style="background:var(--nb-purple-light);"><i class="ph-bold ph-certificate"></i></div>
                    <div>
                        <div class="spmb-stat-num">95%</div>
                        <div class="spmb-stat-label">Lulusan Bekerja &amp; Kuliah</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ALUR PENDAFTARAN -->
    <section class="spmb-section" id="alur">
        <div class="spmb-container">
            <div class="spmb-section-header">
                <div class="spmb-section-badge"><i class="ph-bold ph-path"></i> Panduan SPMB</div>
                <h2 class="spmb-section-title">4 Langkah Mudah Mendaftar</h2>
                <p class="spmb-section-desc">Ikuti tahapan proses pendaftaran calon siswa baru dari awal hingga pengumuman hasil seleksi resmi.</p>
            </div>
            <div class="spmb-steps-grid">
                <div class="spmb-step-card">
                    <div class="spmb-step-number">1</div>
                    <div class="spmb-step-icon"><i class="ph-bold ph-pencil-line"></i></div>
                    <h3 class="spmb-step-title">Isi Formulir Online</h3>
                    <p class="spmb-step-desc">Lengkapi data pribadi, asal sekolah SMP/MTs, data orang tua, dan pilih jurusan keahlian yang diminati.</p>
                </div>
                <div class="spmb-step-card">
                    <div class="spmb-step-number">2</div>
                    <div class="spmb-step-icon"><i class="ph-bold ph-file-text"></i></div>
                    <h3 class="spmb-step-title">Cetak Kartu Pendaftaran</h3>
                    <p class="spmb-step-desc">Dapatkan Nomor Registrasi resmi secara otomatis dan cetak kartu bukti pendaftaran peserta SPMB.</p>
                </div>
                <div class="spmb-step-card">
                    <div class="spmb-step-number">3</div>
                    <div class="spmb-step-icon"><i class="ph-bold ph-clipboard-text"></i></div>
                    <h3 class="spmb-step-title">Verifikasi &amp; Tes Bakat</h3>
                    <p class="spmb-step-desc">Panitia memverifikasi berkas dan calon siswa mengikuti tes peminatan atau wawancara minat bakat.</p>
                </div>
                <div class="spmb-step-card">
                    <div class="spmb-step-number">4</div>
                    <div class="spmb-step-icon"><i class="ph-bold ph-seal-check"></i></div>
                    <h3 class="spmb-step-title">Pengumuman &amp; Daftar Ulang</h3>
                    <p class="spmb-step-desc">Cek hasil pengumuman kelulusan di portal SPMB, lalu lakukan proses daftar ulang dengan membawa kelengkapan berkas fisik persyaratan ke sekolah.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PILIHAN JURUSAN / PROGRAM KEAHLIAN -->
    <section class="spmb-section" style="background-color: var(--nb-white); border-top: var(--border-thick); border-bottom: var(--border-thick);" id="jurusan">
        <div class="spmb-container">
            <div class="spmb-section-header">
                <div class="spmb-section-badge"><i class="ph-bold ph-squares-four"></i> Program Keahlian</div>
                <h2 class="spmb-section-title">10 Jurusan Masa Depan di SMKS Sukapura</h2>
                <p class="spmb-section-desc">Pilih jurusan sesuai minat bakatmu dengan fasilitas laboratorium modern dan sertifikasi keahlian teruji.</p>
            </div>
            <div class="spmb-jurusan-grid">
                <?php foreach ($DAFTAR_JURUSAN as $kode => $j): ?>
                    <div class="spmb-jurusan-card">
                        <div>
                            <div class="spmb-jurusan-header">
                                <div class="spmb-jurusan-logo-wrap">
                                    <img src="<?php echo $base_url; ?>assets/jurusan/<?php echo strtolower($kode); ?>.png" alt="Logo <?php echo htmlspecialchars($kode); ?>" class="spmb-jurusan-logo-img">
                                </div>
                                <div class="spmb-jurusan-meta">
                                    <span class="spmb-jurusan-code" style="background-color: <?php echo $j['badge']; ?>; color: #fff;">
                                        <?php echo htmlspecialchars($kode); ?>
                                    </span>
                                    <span class="spmb-jurusan-kuota">Kuota: <?php echo $j['kuota']; ?> Siswa</span>
                                </div>
                            </div>
                            <h3 class="spmb-jurusan-title"><?php echo htmlspecialchars($j['nama']); ?></h3>
                        </div>
                        <a href="daftar.php?jurusan=<?php echo urlencode($kode); ?>" class="spmb-btn spmb-btn-secondary spmb-btn-sm" style="margin-top:14px; width:100%;">
                            Pilih Jurusan Ini <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- JALUR PENDAFTARAN & SYARAT -->
    <section class="spmb-section">
        <div class="spmb-container">
            <div class="spmb-dual-grid">
                <!-- Box Jalur -->
                <div class="spmb-info-box">
                    <h3 class="spmb-info-box-title">
                        <i class="ph-bold ph-path" style="color:var(--nb-navy);"></i> Jalur Pendaftaran Tersedia
                    </h3>
                    <ul class="spmb-check-list">
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>
                                <strong>Jalur Reguler:</strong><br>
                                Seleksi umum bagi lulusan SMP/MTs berdasarkan nilai rapor dan tes pemetaan minat bakat kejuruan.
                            </div>
                        </li>
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>
                                <strong>Jalur Prestasi:</strong><br>
                                Diperuntukkan bagi siswa berprestasi akademik (peringkat kelas/juara lomba) maupun non-akademik (olahraga, seni, tahfidz Al-Qur'an).
                            </div>
                        </li>
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>
                                <strong>Jalur Yatim / Piatu:</strong><br>
                                Program prioritas dan bantuan khusus pendidikan bagi calon peserta didik yang berstatus yatim, piatu, atau yatim piatu.
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Box Syarat Berkas -->
                <div class="spmb-info-box">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px; margin-bottom:14px;">
                        <h3 class="spmb-info-box-title" style="margin-bottom:0;">
                            <i class="ph-bold ph-file-text" style="color:var(--nb-red);"></i> Persyaratan Berkas Dokumen
                        </h3>
                        <span style="font-size:0.75rem; font-weight:800; background:#E0F2FE; color:#0369A1; border:1.5px solid #0284C7; padding:3px 8px; border-radius:6px; white-space:nowrap;">
                            <i class="ph-bold ph-clock"></i> Dibawa Saat Daftar Ulang
                        </span>
                    </div>

                    <ul class="spmb-check-list">
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>Fotokopi Ijazah / Surat Keterangan Lulus (SKL) dari SMP/MTs yang telah dilegalisir (2 lembar).</div>
                        </li>
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>Fotokopi Kartu Keluarga (KK) dan Akta Kelahiran calon peserta didik (2 lembar).</div>
                        </li>
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>Nomor Induk Siswa Nasional (NISN) aktif yang terdaftar di Kemendikbudristek.</div>
                        </li>
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>Pas Foto berwarna terbaru ukuran 3x4 (3 lembar) dengan latar belakang warna merah/biru.</div>
                        </li>
                        <li>
                            <i class="ph-bold ph-check"></i>
                            <div>Sertifikat piagam kejuaraan asli &amp; fotokopi (khusus pendaftar Jalur Prestasi).</div>
                        </li>
                    </ul>

                    <div style="margin-top:16px; padding:12px 14px; background:#FFFDF0; border:1.5px solid #000; border-left:5px solid #EAB308; border-radius:8px; box-shadow:2px 2px 0 #000; font-size:0.85rem; line-height:1.45; color:#1E293B; display:flex; align-items:flex-start; gap:10px;">
                        <i class="ph-bold ph-info" style="color:#D97706; font-size:1.3rem; flex-shrink:0; margin-top:2px;"></i>
                        <div>
                            <strong>Keterangan Pelaksanaan:</strong><br>
                            Seluruh berkas fisik di atas <u>wajib dibawa dan diserahkan saat proses <strong>Daftar Ulang</strong></u> ke sekretariat panitia SPMB di kampus SMKS Sukapura setelah dinyatakan lulus seleksi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION BANNER -->
    <section class="spmb-section" style="padding-top:0;">
        <div class="spmb-container">
            <div style="background:var(--nb-yellow); border:var(--border-thick); border-radius:var(--radius-card); box-shadow:var(--shadow-xl); padding:50px 30px; text-align:center; position:relative; overflow:hidden;">
                <h2 style="font-family:var(--font-heading); font-size:clamp(1.8rem, 3.5vw, 2.6rem); font-weight:800; margin-bottom:14px;">
                    Jangan Lewatkan Kesempatan Emas Ini!
                </h2>
                <p style="font-size:1.1rem; font-weight:600; max-width:650px; margin:0 auto 28px;">
                    Kuota setiap jurusan terbatas! Daftarkan dirimu hari ini juga dan amankan bangku pendidikan vokasi terbaik di SMKS Sukapura.
                </p>
                <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
                    <a href="daftar.php" class="spmb-btn spmb-btn-primary" style="font-size:1.1rem; padding:16px 36px;">
                        <i class="ph-bold ph-pencil-simple"></i> Isi Formulir Sekarang
                    </a>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $settings['hotline_wa'] ?? '6281234567890'); ?>" target="_blank" class="spmb-btn spmb-btn-secondary" style="font-size:1.1rem; padding:16px 36px;">
                        <i class="ph-bold ph-whatsapp-logo"></i> Konsultasi via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include '../components/footer.php'; ?>
</body>
</html>
