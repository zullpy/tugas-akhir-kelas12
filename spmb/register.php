<?php
// spmb/register.php
// Pendaftaran Akun Calon Siswa SPMB SMKS SUKAPURA
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'register';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

// Tentukan redirect target setelah registrasi & login otomatis
$redirectTarget = sanitize_input($_GET['redirect'] ?? ($_POST['redirect'] ?? 'daftar.php'));
if (strpos($redirectTarget, '://') !== false || strpos($redirectTarget, '//') === 0) {
    $redirectTarget = 'daftar.php';
}

// Jika sudah login, langsung alihkan
if (!empty($_SESSION['spmb_user_id'])) {
    header("Location: " . $redirectTarget);
    exit;
}

$error = '';
$success = '';

// Hitung batas usia PPDB (Maks 21 tahun per 1 Juli tahun berjalan, Min 13 tahun)
$refYear = (int)date('Y'); // Tahun berjalan (2026 -> rentang kelahiran 2005 s.d. 2013)
$minBirthDate = ($refYear - 21) . '-07-01'; // Maksimal 21 tahun per 1 Juli (2005-07-01)
$maxBirthDate = ($refYear - 13) . '-12-31'; // Minimal 13 tahun (2013-12-31)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap        = sanitize_input($_POST['nama_lengkap'] ?? '');
    $email               = sanitize_input($_POST['email'] ?? '');
    $no_hp               = sanitize_input($_POST['no_hp'] ?? '');
    $tanggal_lahir       = sanitize_input($_POST['tanggal_lahir'] ?? '');
    $password            = $_POST['password'] ?? '';
    $password_konfirmasi = $_POST['password_konfirmasi'] ?? '';

    // Validasi input
    if (empty($nama_lengkap) || empty($email) || empty($no_hp) || empty($tanggal_lahir) || empty($password)) {
        $error = "Harap lengkapi seluruh kolom yang bertanda bintang (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format alamat email tidak valid.";
    } elseif (!is_valid_indonesian_phone($no_hp)) {
        $error = "Nomor HP / WhatsApp tidak valid. Gunakan format nomor Indonesia (contoh: 081234567890).";
    } elseif (!empty($tanggal_lahir) && ($tanggal_lahir < $minBirthDate || $tanggal_lahir > $maxBirthDate)) {
        if ($tanggal_lahir < $minBirthDate) {
            $error = "Usia calon siswa melebihi 21 tahun. Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia maksimal adalah 21 tahun per 1 Juli $refYear.";
        } else {
            $error = "Usia calon siswa kurang dari 13 tahun. Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia minimal adalah 13 tahun.";
        }
    } elseif (strlen($password) < 6) {
        $error = "Password minimal terdiri dari 6 karakter.";
    } elseif ($password !== $password_konfirmasi) {
        $error = "Konfirmasi password tidak cocok dengan password yang dimasukkan.";
    } else {
        // Cek apakah email sudah terdaftar
        $checkStmt = $pdo->prepare("SELECT id FROM `spmb_akun` WHERE `email` = ? LIMIT 1");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $error = "Alamat email sudah terdaftar di sistem. Silakan masuk atau gunakan email lain.";
        } else {
            // Normalisasi nomor HP
            $normalizedPhone = normalize_phone_number($no_hp);
            $hashedPassword  = password_hash($password, PASSWORD_BCRYPT);

            $insertStmt = $pdo->prepare("INSERT INTO `spmb_akun` (`nama_lengkap`, `email`, `no_hp`, `tanggal_lahir`, `password`) VALUES (?, ?, ?, ?, ?)");
            $successInsert = $insertStmt->execute([
                $nama_lengkap,
                $email,
                $normalizedPhone,
                $tanggal_lahir,
                $hashedPassword
            ]);

            if ($successInsert) {
                $newUserId = (int)$pdo->lastInsertId();

                // Auto login user setelah pendaftaran akun berhasil
                $_SESSION['spmb_user_id']    = $newUserId;
                $_SESSION['spmb_user_nama']  = $nama_lengkap;
                $_SESSION['spmb_user_email'] = $email;
                $_SESSION['spmb_user_nohp']  = $normalizedPhone;

                // Alihkan ke formulir pendaftaran
                header("Location: " . $redirectTarget);
                exit;
            } else {
                $error = "Terjadi kesalahan saat menyimpan data akun. Silakan coba kembali.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Calon Siswa | SPMB SMKS SUKAPURA</title>
    <meta name="description" content="Pendaftaran akun calon siswa baru SMKS Sukapura untuk mengisi formulir SPMB.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="spmb.css?v=<?php echo @filemtime(__DIR__ . '/spmb.css') ?: time(); ?>">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
    <style>
        .auth-container {
            max-width: 820px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }
        .auth-card {
            background: #fff;
            border: var(--border-thick);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            padding: 40px 44px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 26px;
        }
        .auth-icon-badge {
            width: 64px;
            height: 64px;
            background: var(--nb-green-light);
            border: var(--border-md);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #065F46;
            margin-bottom: 14px;
        }
        .auth-title {
            font-family: var(--font-heading);
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--nb-navy);
            margin: 0 0 6px;
        }
        .auth-subtitle {
            font-size: 0.95rem;
            color: #64748B;
            margin: 0;
            line-height: 1.45;
        }
        .auth-info-box {
            background: #EFF6FF;
            border: var(--border-thin);
            border-left: 5px solid #2563EB;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 0.88rem;
            color: #1E40AF;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .auth-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 22px;
            row-gap: 18px;
        }
        .auth-col-full {
            grid-column: 1 / -1;
        }
        .spmb-form-group {
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
        }
        .spmb-form-group .spmb-input {
            width: 100%;
        }
        .spmb-form-helper {
            font-size: 0.78rem;
            color: #64748B;
            margin-top: 5px;
            line-height: 1.35;
            display: block;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #64748B;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            padding: 4px;
        }
        .password-toggle-btn:hover {
            color: #000;
        }
        @media (max-width: 680px) {
            .auth-container {
                padding: 0 16px;
                margin: 24px auto 60px;
            }
            .auth-card {
                padding: 28px 20px;
                border-radius: 16px;
            }
            .auth-form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
    </style>
</head>
<body class="spmb-body">
    <?php include __DIR__ . '/header_minimal.php'; ?>

    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon-badge">
                    <i class="ph-bold ph-user-plus"></i>
                </div>
                <h1 class="auth-title">Daftar Akun Baru</h1>
                <p class="auth-subtitle">Buat akun untuk mengisi formulir pendaftaran dan memantau status seleksi</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="spmb-alert spmb-alert-danger" style="margin-bottom: 22px;">
                    <i class="ph-bold ph-warning-circle" style="font-size: 1.4rem; flex-shrink: 0;"></i>
                    <div style="font-size: 0.9rem; line-height: 1.4;"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" autocomplete="on">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectTarget); ?>">

                <div class="auth-form-grid">
                    <!-- ROW 1: Nama Lengkap & Tanggal Lahir -->
                    <div class="spmb-form-group">
                        <label class="spmb-label" for="nama_lengkap">
                            Nama Lengkap <span class="spmb-req">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" class="spmb-input" 
                               placeholder="Contoh: Muhammad Rizki Pratama" 
                               value="<?php echo htmlspecialchars($_POST['nama_lengkap'] ?? ''); ?>" 
                               required autofocus autocomplete="name">
                        <small class="spmb-form-helper">
                            Sesuai ijazah SMP/MTs atau Akta Kelahiran
                        </small>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="tanggal_lahir">
                            Tanggal Lahir <span class="spmb-req">*</span>
                        </label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="spmb-input" 
                               value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>" 
                               min="<?php echo $minBirthDate; ?>" 
                               max="<?php echo $maxBirthDate; ?>" 
                               required>
                        <div id="tgl_lahir_feedback" class="spmb-input-feedback" style="margin-top: 6px;"></div>
                        <small class="spmb-form-helper">
                            Usia wajib 13 s.d. 21 tahun per 1 Juli <?php echo $refYear; ?>
                        </small>
                    </div>

                    <!-- ROW 2: Email & Nomor HP/WA -->
                    <div class="spmb-form-group">
                        <label class="spmb-label" for="email">
                            Alamat Email Aktif <span class="spmb-req">*</span>
                        </label>
                        <input type="email" name="email" id="email" class="spmb-input" 
                               placeholder="nama@email.com" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               required autocomplete="email">
                        <small class="spmb-form-helper">
                            Digunakan untuk login dan verifikasi akun
                        </small>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="no_hp">
                            Nomor HP / WhatsApp Aktif <span class="spmb-req">*</span>
                        </label>
                        <input type="tel" name="no_hp" id="no_hp" class="spmb-input" 
                               placeholder="Contoh: 081234567890" 
                               value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" 
                               required autocomplete="tel">
                        <small class="spmb-form-helper">
                            Nomor aktif untuk informasi jadwal tes &amp; kelulusan
                        </small>
                    </div>

                    <!-- ROW 3: Password & Konfirmasi Password -->
                    <div class="spmb-form-group">
                        <label class="spmb-label" for="password">
                            Password <span class="spmb-req">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="password" id="password" class="spmb-input" 
                                   placeholder="Minimal 6 karakter" 
                                   required autocomplete="new-password"
                                   style="padding-right: 42px;">
                            <button type="button" class="password-toggle-btn" id="togglePasswordBtn" title="Tampilkan/Sembunyikan Password">
                                <i class="ph-bold ph-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <small class="spmb-form-helper">
                            Minimal 6 karakter kombinasi huruf &amp; angka
                        </small>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="password_konfirmasi">
                            Konfirmasi Password <span class="spmb-req">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="password_konfirmasi" id="password_konfirmasi" class="spmb-input" 
                                   placeholder="Ulangi password di atas" 
                                   required autocomplete="new-password"
                                   style="padding-right: 42px;">
                            <button type="button" class="password-toggle-btn" id="togglePasswordKonfirmBtn" title="Tampilkan/Sembunyikan Password">
                                <i class="ph-bold ph-eye" id="togglePasswordKonfirmIcon"></i>
                            </button>
                        </div>
                        <small class="spmb-form-helper">
                            Pastikan sama persis dengan password di samping
                        </small>
                    </div>

                    <!-- ROW 4: Tombol Submit -->
                    <div class="auth-col-full" style="margin-top: 6px;">
                        <button type="submit" class="spmb-btn spmb-btn-primary" style="width: 100%; padding: 15px; font-size: 1.02rem; display: inline-flex; align-items: center; justify-content: center; gap: 10px;">
                            <i class="ph-bold ph-user-check" style="font-size: 1.25rem;"></i> Buat Akun &amp; Lanjutkan Pendaftaran
                        </button>
                        <p style="text-align: center; margin-top: 16px; margin-bottom: 0; font-size: 0.9rem; color: #475569;">
                            Sudah memiliki akun calon siswa? 
                            <a href="login.php<?php echo !empty($redirectTarget) && $redirectTarget !== 'daftar.php' ? '?redirect=' . urlencode($redirectTarget) : ''; ?>" 
                               style="font-weight: 700; color: #0284C7; text-decoration: none;">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/footer_minimal.php'; ?>

    <script>
    function setupPasswordToggle(btnId, inputId, iconId) {
        document.getElementById(btnId)?.addEventListener('click', function() {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            }
        });
    }
    setupPasswordToggle('togglePasswordBtn', 'password', 'togglePasswordIcon');
    setupPasswordToggle('togglePasswordKonfirmBtn', 'password_konfirmasi', 'togglePasswordKonfirmIcon');

    // Validasi Tanggal Lahir Calon Siswa (13 s.d. 21 Tahun)
    function validateTanggalLahir(showAlert = true) {
        const input = document.getElementById('tanggal_lahir');
        const feedback = document.getElementById('tgl_lahir_feedback');
        if (!input) return true;

        const val = input.value;
        const minD = input.getAttribute('min');
        const maxD = input.getAttribute('max');

        if (!val) {
            if (feedback) {
                feedback.className = 'spmb-input-feedback';
                feedback.innerHTML = '';
            }
            input.classList.remove('is-invalid', 'is-valid');
            return true;
        }

        // Hitung usia calon siswa
        const birthDate = new Date(val);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (minD && val < minD) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.className = 'spmb-input-feedback danger';
                feedback.innerHTML = '<i class="ph-bold ph-warning-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Batas usia terlewati: Usia calon siswa lebih dari 21 tahun (Maksimal 21 tahun per 1 Juli <?php echo $refYear; ?>).</span>';
            }
            if (showAlert && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Usia Melebihi 21 Tahun',
                    text: 'Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia calon peserta didik maksimal adalah 21 tahun per 1 Juli <?php echo $refYear; ?>.',
                    confirmButtonColor: '#0A4D68'
                });
            }
            return false;
        }

        if (maxD && val > maxD) {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.className = 'spmb-input-feedback danger';
                feedback.innerHTML = '<i class="ph-bold ph-warning-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Usia belum mencukupi: Usia calon siswa kurang dari 13 tahun (Minimal 13 tahun).</span>';
            }
            if (showAlert && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Usia Kurang Dari 13 Tahun',
                    text: 'Berdasarkan ketentuan Permendikbud PPDB SMK, batas usia calon peserta didik minimal adalah 13 tahun.',
                    confirmButtonColor: '#0A4D68'
                });
            }
            return false;
        }

        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if (feedback) {
            feedback.className = 'spmb-input-feedback success';
            feedback.innerHTML = '<i class="ph-bold ph-check-circle" style="font-size:1.1rem; flex-shrink:0;"></i><span>Tanggal lahir valid' + (age > 0 ? ' (Usia ' + age + ' tahun)' : '') + '.</span>';
        }
        return true;
    }

    const tglInput = document.getElementById('tanggal_lahir');
    if (tglInput) {
        tglInput.addEventListener('change', function() {
            validateTanggalLahir(true);
        });
        tglInput.addEventListener('input', function() {
            validateTanggalLahir(false);
        });
        if (tglInput.value) {
            validateTanggalLahir(false);
        }
    }

    // Cegah submit form jika usia tidak memenuhi syarat
    const registerForm = document.querySelector('form[action="register.php"]');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateTanggalLahir(true)) {
                e.preventDefault();
                if (tglInput) tglInput.focus();
                return false;
            }
        });
    }
    </script>
</body>
</html>
