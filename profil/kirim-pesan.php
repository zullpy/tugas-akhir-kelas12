<?php
// Handler pengiriman pesan dari form kontak
// Menggunakan PHPMailer + Gmail SMTP (Railway-compatible)
ob_start();
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

// Path vendor PHPMailer
$vendor_dir = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';

if (!file_exists($vendor_dir . 'PHPMailer.php')) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Library PHPMailer tidak ditemukan di server.'
    ]);
    exit;
}

require_once $vendor_dir . 'Exception.php';
require_once $vendor_dir . 'PHPMailer.php';
require_once $vendor_dir . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ─── KONFIGURASI GMAIL SMTP ───────────────────────────────
// Mendukung Environment Variables (SMTP_USER, SMTP_PASSWORD, TO_EMAIL, SMTP_PORT, SMTP_HOST)
$smtp_user     = getenv('SMTP_USER')     ?: 'mzulfahmi008@gmail.com';
$smtp_password = getenv('SMTP_PASSWORD') ?: 'bisviqjcrlbqrnsd';
$to_email      = getenv('TO_EMAIL')      ?: 'mzulfahmi008@gmail.com';
$smtp_host     = getenv('SMTP_HOST')     ?: 'smtp.gmail.com';
$smtp_port     = (int)(getenv('SMTP_PORT') ?: 465);
// ─────────────────────────────────────────────────────────

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

// Sanitasi input
$nama  = trim(strip_tags($_POST['nama']  ?? ''));
$email = trim(strip_tags($_POST['email'] ?? ''));
$pesan = trim(strip_tags($_POST['pesan'] ?? ''));

// Validasi input
if (empty($nama) || empty($email) || empty($pesan)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

// Validasi keberadaan domain server email (MX / A Record)
$domain = substr(strrchr($email, "@"), 1);
if (!empty($domain) && function_exists('checkdnsrr')) {
    $has_mx = @checkdnsrr($domain, 'MX');
    $has_a  = @checkdnsrr($domain, 'A');
    if (!$has_mx && !$has_a) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Domain email tidak valid atau tidak memiliki server email aktif.']);
        exit;
    }
}

if (strlen($nama) > 100 || strlen($pesan) > 3000) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Input terlalu panjang.']);
    exit;
}

try {
    $mail = new PHPMailer(true);

    // Server SMTP
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_user;
    $mail->Password   = $smtp_password;
    
    if ($smtp_port === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->Port       = $smtp_port;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 15;

    // SSL options untuk kompatibilitas server cloud container (Railway/Render/cPanel)
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];

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

    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Pesan berhasil dikirim! Kami akan segera menghubungi Anda.'
    ]);

} catch (\Throwable $e) {
    ob_clean();
    $errMsg = $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengirim pesan: ' . $errMsg
    ]);
}

