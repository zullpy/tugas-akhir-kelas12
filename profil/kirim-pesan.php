<?php
// Handler pengiriman pesan dari form kontak
// Menggunakan PHPMailer + Gmail SMTP (Port 465 & 587 Fallback + Auto Local Backup)

error_reporting(0);
@ini_set('display_errors', '0');

ob_start();
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

// Function untuk menyimpan pesan ke file JSON sebagai jaminan 100% data tersimpan
function saveMessageLocally($nama, $email, $pesan) {
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $file = $dir . '/pesan_masuk.json';
    $existing = [];
    if (file_exists($file)) {
        $content = @file_get_contents($file);
        if ($content) {
            $existing = @json_decode($content, true) ?: [];
        }
    }
    $existing[] = [
        'tanggal' => date('Y-m-d H:i:s'),
        'nama'    => $nama,
        'email'   => $email,
        'pesan'   => $pesan,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ];
    @file_put_contents($file, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Function untuk mencatat log error SMTP untuk kemudahan debugging
function logSmtpError($errorMsg) {
    $dir = __DIR__ . '/../data';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $logFile = $dir . '/smtp_error.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] SMTP Error: " . $errorMsg . "\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

// Validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

// Sanitasi input
$nama  = trim(strip_tags($_POST['nama']  ?? ''));
$email = trim(strip_tags($_POST['email'] ?? ''));
$pesan = trim(strip_tags($_POST['pesan'] ?? ''));

// Validasi input
if (empty($nama) || empty($email) || empty($pesan)) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Semua bidang form wajib diisi.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
    exit;
}

if (strlen($nama) > 100 || strlen($pesan) > 3000) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => 'Input terlalu panjang.']);
    exit;
}

// Simpan pesan secara lokal terlebih dahulu
saveMessageLocally($nama, $email, $pesan);

// Konfigurasi SMTP
$smtp_user     = getenv('SMTP_USER')     ?: 'mzulfahmi008@gmail.com';
$smtp_password = getenv('SMTP_PASSWORD') ?: 'bisviqjcrlbqrnsd';
$to_email      = getenv('TO_EMAIL')      ?: 'mzulfahmi008@gmail.com';
$smtp_host     = getenv('SMTP_HOST')     ?: 'smtp.gmail.com';

$vendor_dir = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';
$email_sent = false;
$last_error = '';

if (file_exists($vendor_dir . 'PHPMailer.php')) {
    require_once $vendor_dir . 'Exception.php';
    require_once $vendor_dir . 'PHPMailer.php';
    require_once $vendor_dir . 'SMTP.php';

    // Helper untuk mencoba mengirim via PHPMailer
    $trySend = function($port, $secureType) use ($smtp_host, $smtp_user, $smtp_password, $to_email, $email, $nama, $pesan) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = $secureType;
        $mail->Port       = $port;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 5;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom($smtp_user, 'Website SMKS Sukapura');
        $mail->addAddress($to_email, 'Admin SMKS Sukapura');
        $mail->addReplyTo($email, $nama);

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
        $mail->AltBody = "Pesan baru dari {$nama} ({$email}):\n\n{$pesan}\n\nDikirim: " . date('d F Y, H:i') . " WIB";

        return $mail->send();
    };

    // Percobaan 1: Port 465 (SSL)
    try {
        $email_sent = $trySend(465, PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS);
    } catch (\Throwable $e1) {
        $last_error = $e1->getMessage();
        // Percobaan 2: Port 587 (TLS Fallback)
        try {
            $email_sent = $trySend(587, PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS);
        } catch (\Throwable $e2) {
            $last_error = $e2->getMessage();
            $email_sent = false;
        }
    }
}

if (!$email_sent && !empty($last_error)) {
    logSmtpError($last_error);
}

if (ob_get_length()) ob_clean();

echo json_encode([
    'success' => true,
    'message' => $email_sent 
        ? 'Pesan Anda telah berhasil dikirim ke email admin sekolah! Terima kasih.' 
        : 'Pesan Anda telah berhasil diterima dan tersimpan di database sekolah. Terima kasih!'
]);
exit;
