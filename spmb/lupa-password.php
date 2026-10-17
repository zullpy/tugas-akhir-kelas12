<?php
// spmb/lupa-password.php
// Reset Password Akun Calon Siswa SPMB dengan Verifikasi OTP via Brevo
$base_url = '../';
$pageTitle = 'spmb';
$subPageTitle = 'lupa-password';

require_once __DIR__ . '/config.php';
$pdo = get_db_connection();
$settings = get_all_settings();

// Jika sudah login, redirect ke daftar.php
if (!empty($_SESSION['spmb_user_id'])) {
    header("Location: daftar.php");
    exit;
}

$error = '';
$success = '';

// Step 1: Kirim OTP, Step 2: Verifikasi OTP & Reset Password
$step = 1;
$emailValue = sanitize_input($_POST['email'] ?? ($_GET['email'] ?? ($_SESSION['reset_email'] ?? '')));

// Jika ada parameter step=2 di query atau session
if (!empty($emailValue) && (isset($_GET['step']) && $_GET['step'] == '2' || isset($_POST['action_reset']))) {
    $step = 2;
}

// ACTION 1: REQUEST OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_request_otp'])) {
    $email = sanitize_input($_POST['email'] ?? '');

    $lastOtpTime = $_SESSION['last_otp_sent_time'] ?? 0;
    if (time() - $lastOtpTime < 60) {
        $sisaDetik = 60 - (time() - $lastOtpTime);
        $error = "Kode OTP baru saja dikirimkan. Harap tunggu {$sisaDetik} detik sebelum meminta kode baru.";
    } elseif (empty($email)) {
        $error = "Silakan masukkan alamat email yang terdaftar.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format alamat email tidak valid.";
    } else {
        $stmt = $pdo->prepare("SELECT id, nama_lengkap, email FROM `spmb_akun` WHERE `email` = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Alamat email tidak ditemukan dalam sistem SPMB. Pastikan Anda telah membuat akun.";
        } else {
            // Generate OTP 6 digit
            $otp = sprintf("%06d", random_int(100000, 999999));
            // Berlaku 15 menit
            $expires = date('Y-m-d H:i:s', time() + 900);

            // Simpan ke DB
            $upd = $pdo->prepare("UPDATE `spmb_akun` SET `otp_code` = ?, `otp_expires` = ? WHERE `id` = ?");
            $upd->execute([$otp, $expires, $user['id']]);

            // Kirim via Brevo API
            $mailResult = kirim_otp_brevo($user['email'], $otp, $user['nama_lengkap']);

            if ($mailResult['success']) {
                $_SESSION['last_otp_sent_time'] = time();
                $_SESSION['reset_email'] = $user['email'];
                $emailValue = $user['email'];
                $step = 2;
                $success = "Kode OTP telah berhasil dikirim ke alamat <strong>" . htmlspecialchars($user['email']) . "</strong>. Silakan periksa kotak masuk atau folder spam email Anda.";
            } else {
                $error = "Gagal mengirimkan kode OTP: " . $mailResult['message'];
            }
        }
    }
}

// ACTION 2: VERIFY OTP & RESET PASSWORD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_reset'])) {
    $email               = sanitize_input($_POST['email'] ?? '');
    $otpInput            = trim($_POST['otp'] ?? '');
    $password            = $_POST['password'] ?? '';
    $password_konfirmasi = $_POST['password_konfirmasi'] ?? '';

    $step = 2; // Tetap di step 2 saat ada kesalahan
    $emailValue = $email;

    if (empty($email) || empty($otpInput) || empty($password) || empty($password_konfirmasi)) {
        $error = "Harap isi seluruh kolom yang wajib.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format alamat email tidak valid.";
    } elseif (strlen($password) < 6) {
        $error = "Password baru minimal terdiri dari 6 karakter.";
    } elseif ($password !== $password_konfirmasi) {
        $error = "Konfirmasi password baru tidak cocok.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `spmb_akun` WHERE `email` = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Akun tidak ditemukan. Silakan ulangi proses dari awal.";
            $step = 1;
        } elseif (empty($user['otp_code'])) {
            $error = "Permintaan OTP tidak valid atau belum diminta. Silakan minta kode OTP baru.";
            $step = 1;
        } elseif ($user['otp_code'] !== $otpInput) {
            $error = "Kode OTP yang Anda masukkan salah. Silakan periksa kembali email Anda.";
        } elseif (empty($user['otp_expires']) || strtotime($user['otp_expires']) < time()) {
            $error = "Kode OTP telah kedaluwarsa (berlaku 15 menit). Silakan kirim ulang kode OTP.";
        } else {
            // Update password baru & bersihkan OTP
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE `spmb_akun` SET `password` = ?, `otp_code` = NULL, `otp_expires` = NULL WHERE `id` = ?");
            $upd->execute([$hashedPassword, $user['id']]);

            unset($_SESSION['reset_email']);

            header("Location: login.php?reset=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password Akun | SPMB SMKS SUKAPURA</title>
    <meta name="description" content="Atur ulang password akun calon siswa SPMB SMKS Sukapura menggunakan kode OTP email.">
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
            max-width: 500px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }
        .auth-card {
            background: #fff;
            border: var(--border-thick);
            border-radius: 18px;
            box-shadow: var(--shadow-xl);
            padding: 36px 32px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .auth-icon-badge {
            width: 64px;
            height: 64px;
            background: var(--nb-yellow);
            border: var(--border-md);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #000;
            margin-bottom: 14px;
        }
        .auth-title {
            font-family: var(--font-heading);
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--nb-navy);
            margin: 0 0 6px;
        }
        .auth-subtitle {
            font-size: 0.92rem;
            color: #64748B;
            margin: 0;
            line-height: 1.45;
        }
        .otp-input-field {
            font-family: monospace;
            font-size: 1.5rem !important;
            letter-spacing: 8px;
            text-align: center;
            font-weight: 800;
        }
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .step-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border: var(--border-thin);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            background: #F1F5F9;
            color: #64748B;
        }
        .step-pill.active {
            background: var(--nb-yellow);
            color: #000;
            box-shadow: var(--shadow-sm);
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
    </style>
</head>
<body class="spmb-body">
    <?php include __DIR__ . '/header_minimal.php'; ?>

    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon-badge">
                    <i class="ph-bold ph-key"></i>
                </div>
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">
                    <?php if ($step === 1): ?>
                        Masukkan email Anda untuk menerima kode OTP verifikasi
                    <?php else: ?>
                        Masukkan kode OTP dari email dan atur password baru Anda
                    <?php endif; ?>
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="spmb-alert spmb-alert-danger" style="margin-bottom: 22px;">
                    <i class="ph-bold ph-warning-circle" style="font-size: 1.4rem; flex-shrink: 0;"></i>
                    <div style="font-size: 0.9rem; line-height: 1.4;"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="spmb-alert spmb-alert-success" style="margin-bottom: 22px; background: #D1FAE5; border-color: #059669; color: #065F46;">
                    <i class="ph-bold ph-check-circle" style="font-size: 1.4rem; flex-shrink: 0;"></i>
                    <div style="font-size: 0.9rem; line-height: 1.4;"><?php echo $success; ?></div>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- STEP 1: FORM REQUEST OTP -->
                <form action="lupa-password.php" method="POST">
                    <input type="hidden" name="action_request_otp" value="1">

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="email">
                            Alamat Email Akun SPMB <span class="spmb-req">*</span>
                        </label>
                        <input type="email" name="email" id="email" class="spmb-input" 
                               placeholder="nama@email.com" 
                               value="<?php echo htmlspecialchars($emailValue); ?>" 
                               required autofocus autocomplete="email">
                        <small style="font-size: 0.78rem; color: #64748B; margin-top: 4px; display: block;">
                            Sistem akan mengirimkan 6 digit kode OTP ke email ini melalui layanan resmi Brevo
                        </small>
                    </div>

                    <button type="submit" class="spmb-btn spmb-btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 8px;">
                        <i class="ph-bold ph-paper-plane-tilt"></i> Kirim Kode OTP
                    </button>
                </form>

            <?php else: ?>
                <!-- STEP 2: FORM VERIFY OTP & RESET PASSWORD -->
                <form action="lupa-password.php" method="POST">
                    <input type="hidden" name="action_reset" value="1">

                    <div class="spmb-form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="spmb-label" for="email" style="margin-bottom: 0;">Alamat Email</label>
                            <a href="lupa-password.php" style="font-size: 0.82rem; font-weight: 700; color: #0284C7; text-decoration: none;">
                                <i class="ph-bold ph-arrows-clockwise"></i> Ganti Email
                            </a>
                        </div>
                        <input type="email" name="email" id="email" class="spmb-input" 
                               value="<?php echo htmlspecialchars($emailValue); ?>" 
                               readonly style="background: #F8FAFC; cursor: not-allowed;">
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="otp">
                            Kode OTP (6 Digit) <span class="spmb-req">*</span>
                        </label>
                        <input type="text" name="otp" id="otp" class="spmb-input otp-input-field" 
                               placeholder="123456" maxlength="6" pattern="[0-9]{6}"
                               required autofocus autocomplete="one-time-code">
                        <small style="font-size: 0.78rem; color: #64748B; margin-top: 4px; display: block;">
                            Masukkan 6 angka kode yang Anda terima di email (berlaku 15 menit)
                        </small>
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="password">
                            Password Baru <span class="spmb-req">*</span>
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
                    </div>

                    <div class="spmb-form-group">
                        <label class="spmb-label" for="password_konfirmasi">
                            Konfirmasi Password Baru <span class="spmb-req">*</span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" name="password_konfirmasi" id="password_konfirmasi" class="spmb-input" 
                                   placeholder="Ulangi password baru" 
                                   required autocomplete="new-password"
                                   style="padding-right: 42px;">
                            <button type="button" class="password-toggle-btn" id="togglePasswordKonfirmBtn" title="Tampilkan/Sembunyikan Password">
                                <i class="ph-bold ph-eye" id="togglePasswordKonfirmIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="spmb-btn spmb-btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 10px;">
                        <i class="ph-bold ph-check"></i> Simpan Password Baru
                    </button>
                </form>

                <div style="text-align: center; margin-top: 18px;">
                    <form action="lupa-password.php" method="POST" style="display: inline;">
                        <input type="hidden" name="action_request_otp" value="1">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($emailValue); ?>">
                        <button type="submit" style="background: none; border: none; color: #0284C7; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Belum terima kode? Kirim ulang OTP
                        </button>
                    </form>
                </div>
            <?php endif; ?>
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

    // Cegah klik ganda / double-submit pada form
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                if (btn.dataset.submitted === 'true') {
                    e.preventDefault();
                    return false;
                }
                btn.dataset.submitted = 'true';
                btn.style.opacity = '0.75';
                btn.style.pointerEvents = 'none';
                btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Sedang memproses...';
            }
        });
    });
    </script>
</body>
</html>
