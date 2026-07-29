<?php
$base_url = '../';
$pageTitle = 'profil';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PROFIL | SMKS SUKAPURA</title>
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
        <img src="../assets/bawah.webp" alt="Banner Profil">
        <div class="background-overlay"></div>
    </header>

    <main class="profile-main-container">
        <!-- Header area with watermark -->
        <div class="profile-header">
            <h1 class="title">PROFIL SINGKAT</h1>
        </div>

        <!-- Main content grid -->
        <div class="profile-content-card">
            <div class="profile-logo-wrapper">
                <img src="../assets/favicon.ico" alt="Logo SMKS Sukapura Kab. Tasikmalaya" class="profile-logo-img">
            </div>
            
            <div class="profile-details-wrapper">
                <h2 class="section-title">Identitas Satuan Pendidikan</h2>
                
                <table class="profile-table">
                    <tbody>
                        <tr>
                            <td class="label">Nama</td>
                            <td class="value">SMKS SUKAPURA KAB. TASIKMALAYA</td>
                        </tr>
                        <tr>
                            <td class="label">NPSN</td>
                            <td class="value">69786374</td>
                        </tr>
                        <tr>
                            <td class="label">Alamat</td>
                            <td class="value">Jl. Dalem Wirawangsa Km. 3 Cikalapa Des/Kec. Tanjungjaya Kab. Tasikmalaya</td>
                        </tr>
                        <tr>
                            <td class="label">Kode Pos</td>
                            <td class="value">46184</td>
                        </tr>
                        <tr>
                            <td class="label">Desa / Kelurahan</td>
                            <td class="value">Tanjungjaya</td>
                        </tr>
                        <tr>
                            <td class="label">Kecamatan / Kota (LN)</td>
                            <td class="value">Kec. Tanjungjaya</td>
                        </tr>
                        <tr>
                            <td class="label">Kab. / Kota / Negara (LN)</td>
                            <td class="value">Kab. Tasikmalaya</td>
                        </tr>
                        <tr>
                            <td class="label">Provinsi / Luar Negeri</td>
                            <td class="value">Jawa Barat</td>
                        </tr>
                        <tr>
                            <td class="label">Status Sekolah</td>
                            <td class="value">Swasta</td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Penyelenggaraan</td>
                            <td class="value">5 / Sehari Penuh hari</td>
                        </tr>
                        <tr>
                            <td class="label">Jenjang Pendidikan</td>
                            <td class="value">SMK</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    
    <?php include '../components/footer.php'; ?>
    <script src="script.js"></script>
</body>
</html>