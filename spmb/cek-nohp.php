<?php
// spmb/cek-nohp.php
// Endpoint AJAX untuk validasi ketersediaan dan status pendaftaran Nomor HP & NISN calon siswa
// Mendukung pengecekan akun WhatsApp aktif via Fonnte Gateway dan validasi struktur Kemendikbud

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/fonnte.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = get_db_connection();

$no_hp = sanitize_input($_GET['no_hp'] ?? ($_POST['no_hp'] ?? ''));
$nisn = sanitize_input($_GET['nisn'] ?? ($_POST['nisn'] ?? ''));
$tanggal_lahir = sanitize_input($_GET['tanggal_lahir'] ?? ($_POST['tanggal_lahir'] ?? ''));
$excludeId = isset($_REQUEST['exclude_id']) && is_numeric($_REQUEST['exclude_id']) ? (int)$_REQUEST['exclude_id'] : null;

// Mode 1: Pengecekan Nomor HP / WhatsApp Calon Siswa
if (!empty($no_hp) || isset($_REQUEST['no_hp'])) {
    if (empty(trim($no_hp))) {
        echo json_encode([
            'status'     => 'error',
            'valid'      => false,
            'registered' => false,
            'message'    => 'Nomor WhatsApp calon siswa wajib diisi.'
        ]);
        exit;
    }

    if (!is_valid_indonesian_phone($no_hp)) {
        echo json_encode([
            'status'     => 'invalid',
            'valid'      => false,
            'registered' => false,
            'message'    => 'Format nomor HP tidak valid. Masukkan nomor seluler Indonesia yang valid diawali 08/628 (10-14 digit angka).'
        ]);
        exit;
    }



    // 2. Cek apakah nomor benar-benar aktif di WhatsApp via Fonnte Gateway
    $waCheck = cek_nomor_wa_fonnte($no_hp);
    if ($waCheck['checked'] && !$waCheck['is_wa']) {
        echo json_encode([
            'status'     => 'not_wa',
            'valid'      => false,
            'registered' => false,
            'is_wa'      => false,
            'message'    => 'Nomor ini <strong>TIDAK terdaftar di aplikasi WhatsApp</strong>. Harap gunakan nomor WhatsApp aktif agar dapat menerima notifikasi dan kartu peserta.',
            'normalized' => normalize_phone_number($no_hp)
        ]);
        exit;
    }

    echo json_encode([
        'status'     => 'available',
        'valid'      => true,
        'registered' => false,
        'is_wa'      => true,
        'message'    => '✓ Nomor WhatsApp aktif dan dapat digunakan.',
        'normalized' => normalize_phone_number($no_hp)
    ]);
    exit;
}

// Mode 2: Pengecekan NISN (Sesuai aturan Kemendikbud & Database SPMB)
if (!empty($nisn) || isset($_REQUEST['nisn'])) {
    $nisn = trim($nisn);
    if (!preg_match('/^[0-9]{10}$/', $nisn)) {
        echo json_encode([
            'status'     => 'invalid',
            'valid'      => false,
            'registered' => false,
            'message'    => 'Format NISN tidak valid. NISN Kemendikbud harus terdiri dari tepat 10 digit angka.'
        ]);
        exit;
    }

    // 1. Cek duplikasi di database pendaftar sekolah
    $sql = "SELECT `id`, `no_pendaftaran`, `nama_lengkap`, `nisn`, `status` FROM `spmb_pendaftar` WHERE `nisn` = ?";
    $params = [$nisn];
    if ($excludeId !== null) {
        $sql .= " AND `id` != ?";
        $params[] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode([
            'status'     => 'registered',
            'valid'      => false,
            'registered' => true,
            'message'    => 'NISN ' . htmlspecialchars($nisn) . ' sudah pernah didaftarkan atas nama <strong>' . htmlspecialchars($existing['nama_lengkap']) . '</strong> (No. Reg: <strong>' . htmlspecialchars($existing['no_pendaftaran']) . '</strong>).',
            'data'       => [
                'no_pendaftaran' => $existing['no_pendaftaran'],
                'nama_lengkap'   => $existing['nama_lengkap'],
                'status'         => $existing['status']
            ]
        ]);
        exit;
    }

    // 2. Validasi struktur tahun kelahiran Kemendikbud (Digit 1-2 NISN adalah 2 digit tahun lahir)
    $catatanKemdikbud = '';
    if (!empty($tanggal_lahir) && strtotime($tanggal_lahir)) {
        $thnLahir2Digit = date('y', strtotime($tanggal_lahir));
        $prefixNisn = substr($nisn, 0, 2);
        if ($prefixNisn !== $thnLahir2Digit) {
            $catatanKemdikbud = "Catatan: Menurut standar Dapodik/Kemendikbud, 2 digit awal NISN ($prefixNisn) umumnya sama dengan 2 digit tahun kelahiran ($thnLahir2Digit). Pastikan sesuai ijazah/rapor.";
        }
    }

    echo json_encode([
        'status'         => 'available',
        'valid'          => true,
        'registered'     => false,
        'message'        => '✓ Format NISN valid (10 digit) dan belum terdaftar di sistem.',
        'note'           => $catatanKemdikbud,
        'kemdikbud_link' => 'https://nisn.data.kemendikdasmen.go.id/'
    ]);
    exit;
}

echo json_encode([
    'status'  => 'error',
    'message' => 'Parameter no_hp atau nisn diperlukan.'
]);
