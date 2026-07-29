<?php
// Handler pengiriman pesan dari form kontak
// Menggunakan PHPMailer + Gmail SMTP (Railway-compatible)
header('Content-Type: application/json');

// Load PHPMailer manual (tanpa Composer)
require __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ─── KONFIGURASI GMAIL SMTP ───────────────────────────────
// Isi dengan akun Gmail + App Password (bukan password biasa!)
// Cara buat App Password: myaccount.google.com → Security → 2-Step Verification → App Passwords
$smtp_user     = 'mzulfahmi008@gmail.com';   // Akun Gmail pengirim
$smtp_password = 'bisviqjcrlbqrnsd';    // App Password 16 karakter dari Google
$to_email      = 'mzulfahmi008@gmail.com';   // Email tujuan penerima
// ─────────────────────────────────────────────────────────

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

// Sanitasi input
$nama  = trim(strip_tags($_POST['nama']  ?? ''));
$email = trim(strip_tags($_POST['email'] ?? ''));
$pesan = trim(strip_tags($_POST['pesan'] ?? ''));

// Validasi input
if (empty($nama) || empty($email) || empty($pesan)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

if (strlen($nama) > 100 || strlen($pesan) > 3000) {
    echo json_encode(['success' => false, 'message' => 'Input terlalu panjang.']);
    exit;
}

// Kirim email via PHPMailer
$mail = new PHPMailer(true);

try {
    // Server SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_user;
    $mail->Password   = $smtp_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Pengirim & penerima
    $mail->setFrom($smtp_user, 'Website SMKS Sukapura');
    $mail->addAddress($to_email, 'Admin SMKS Sukapura');
    $mail->addReplyTo($email, $nama);

    // Isi email (HTML)
    $mail->isHTML(true);
    $mail->Subject = "Pesan Baru dari Website SMKS Sukapura — {$nama}";
    $mail->Body    = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;border:2px solid #000;border-radius:12px;overflow:hidden'>
      <div style='background:#FFE600;padding:20px 24px;border-bottom:2px solid #000'>
        <h2 style='margin:0;font-size:1.2rem;color:#000'>📬 Pesan Baru — Website SMKS Sukapura</h2>
      </div>
      <div style='padding:24px;background:#fff'>
        <table style='width:100%;border-collapse:collapse;font-size:0.95rem'>
          <tr style='border-bottom:1px solid #e2e8f0'>
            <td style='padding:10px 8px;font-weight:bold;width:120px;color:#475569'>Nama</td>
            <td style='padding:10px 8px;color:#0f172a'>{$nama}</td>
          </tr>
          <tr style='border-bottom:1px solid #e2e8f0'>
            <td style='padding:10px 8px;font-weight:bold;color:#475569'>Email</td>
            <td style='padding:10px 8px;color:#0f172a'><a href='mailto:{$email}' style='color:#1d4ed8'>{$email}</a></td>
          </tr>
          <tr style='border-bottom:1px solid #e2e8f0'>
            <td style='padding:10px 8px;font-weight:bold;color:#475569'>Tanggal</td>
            <td style='padding:10px 8px;color:#0f172a'>" . date('d F Y, H:i') . " WIB</td>
          </tr>
        </table>
        <div style='margin-top:20px;padding:18px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:8px'>
          <p style='margin:0 0 8px;font-weight:bold;color:#475569;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.5px'>Isi Pesan</p>
          <p style='margin:0;color:#0f172a;line-height:1.7'>" . nl2br(htmlspecialchars($pesan)) . "</p>
        </div>
      </div>
      <div style='padding:14px 24px;background:#f1f5f9;border-top:2px solid #000;font-size:0.8rem;color:#64748b'>
        Pesan ini dikirim melalui form kontak website SMKS Sukapura · Balas langsung ke: {$email}
      </div>
    </div>
    ";

    // Versi plain text (fallback)
    $mail->AltBody = "Pesan baru dari {$nama} ({$email}):\n\n{$pesan}\n\nDikirim: " . date('d F Y, H:i') . " WIB";

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Pesan berhasil dikirim! Kami akan segera menghubungi Anda.'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengirim pesan. Silakan coba lagi atau hubungi kami langsung.'
        // Debug: 'debug' => $mail->ErrorInfo  // aktifkan saat development
    ]);
}
