<?php
// spmb/config.php
// Konfigurasi Database & Helper Sistem SPMB SMKS SUKAPURA

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Fungsi pembaca file .env sederhana (mendukung root project maupun folder spmb)
 */
if (!function_exists('load_env_file')) {
    function load_env_file($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false;
        }
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
        return true;
    }
}

/**
 * Helper untuk mengambil nilai environment dengan fallback default
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

// Muat .env dari root project atau folder spmb jika ada
$rootDir = dirname(__DIR__);
load_env_file($rootDir . '/.env');
load_env_file(__DIR__ . '/.env');

// Deteksi Otomatis Lingkungan (Local vs Production)
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
$isLocalHost = in_array(strtolower(explode(':', $httpHost)[0]), ['localhost', '127.0.0.1', '::1'])
    || (php_sapi_name() === 'cli' && empty(env('APP_ENV')));

$detectedEnv = env('APP_ENV', $isLocalHost ? 'local' : 'production');
define('APP_ENV', strtolower($detectedEnv));

// Pengaturan Error Reporting berdasarkan Environment
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Konfigurasi Database MySQL (Dinamis: membaca .env jika ada, fallback ke default local)
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'db_sekolah'));

// Base URL Website
$defaultScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                 (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                 ? 'https' : 'http';
$defaultHost = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost:8000';
define('APP_URL', rtrim(env('APP_URL', $defaultScheme . '://' . $defaultHost), '/'));

// Daftar Jurusan Resmi SMKS Sukapura
$DAFTAR_JURUSAN = [
    'PPLG' => [
        'nama' => 'Pengembangan Perangkat Lunak & Gim',
        'singkatan' => 'PPLG',
        'kuota' => 72,
        'badge' => '#0A4D68',
        'icon' => 'ph-code',
        'logo' => 'assets/jurusan/pplg.png'
    ],
    'TJKT' => [
        'nama' => 'Teknik Jaringan Komputer & Telekomunikasi',
        'singkatan' => 'TJKT',
        'kuota' => 72,
        'badge' => '#053B50',
        'icon' => 'ph-network',
        'logo' => 'assets/jurusan/tjkt.png'
    ],
    'DKV' => [
        'nama' => 'Desain Komunikasi Visual',
        'singkatan' => 'DKV',
        'kuota' => 72,
        'badge' => '#9B51E0',
        'icon' => 'ph-paint-brush',
        'logo' => 'assets/jurusan/dkv.png'
    ],
    'MP' => [
        'nama' => 'Manajemen Perkantoran',
        'singkatan' => 'MP',
        'kuota' => 72,
        'badge' => '#219653',
        'icon' => 'ph-briefcase',
        'logo' => 'assets/jurusan/mp.png'
    ],
    'AK' => [
        'nama' => 'Akuntansi',
        'singkatan' => 'AK',
        'kuota' => 72,
        'badge' => '#F2994A',
        'icon' => 'ph-calculator',
        'logo' => 'assets/jurusan/ak.png'
    ],
    'BD' => [
        'nama' => 'Bisnis Digital',
        'singkatan' => 'BD',
        'kuota' => 72,
        'badge' => '#2F80ED',
        'icon' => 'ph-shopping-cart',
        'logo' => 'assets/jurusan/bd.png'
    ],
    'AB' => [
        'nama' => 'Agri Bisnis',
        'singkatan' => 'AB',
        'kuota' => 72,
        'badge' => '#EB5757',
        'icon' => 'ph-wrench',
        'logo' => 'assets/jurusan/ab.png'
    ],
    'DPB' => [
        'nama' => 'Desain & Produksi Busana',
        'singkatan' => 'DPB',
        'kuota' => 72,
        'badge' => '#E056FD',
        'icon' => 'ph-scissors',
        'logo' => 'assets/jurusan/dpb.png'
    ],
    'KLN' => [
        'nama' => 'Kuliner',
        'singkatan' => 'KLN',
        'kuota' => 72,
        'badge' => '#FF7675',
        'icon' => 'ph-cooking-pot',
        'logo' => 'assets/jurusan/kln.png'
    ],
    'TSM' => [
        'nama' => 'Teknik Sepeda Motor',
        'singkatan' => 'TSM',
        'kuota' => 72,
        'badge' => '#D35400',
        'icon' => 'ph-motorcycle',
        'logo' => 'assets/jurusan/tsm.png'
    ]
];

$DAFTAR_JALUR = [
    'Reguler' => 'Jalur Reguler (Nilai Rapor & Peminatan)',
    'Prestasi' => 'Jalur Prestasi (Akademik & Non-Akademik)',
    'Yatim/Piatu' => 'Jalur Yatim / Piatu (Bantuan Khusus Pendidikan)'
];

/**
 * Mendapatkan koneksi PDO MySQL (auto-create DB & Tables jika belum ada)
 */
function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $portPart = (defined('DB_PORT') && DB_PORT) ? ";port=" . DB_PORT : "";
    $dsn = "mysql:host=" . DB_HOST . $portPart . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    try {
        // 1. Coba konek langsung ke database
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch (PDOException $e) {
        // Jika database belum ada (error 1049) dan di lingkungan local, coba buat otomatis
        $isUnknownDb = ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false);
        if ($isUnknownDb && (!defined('APP_ENV') || APP_ENV === 'local')) {
            try {
                $temp_pdo = new PDO("mysql:host=" . DB_HOST . $portPart . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $temp_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $temp_pdo = null;

                // Konek ulang setelah dibuat
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } catch (PDOException $ex) {
                die("Koneksi MySQL Gagal: " . htmlspecialchars($ex->getMessage()));
            }
        } else {
            if (defined('APP_ENV') && APP_ENV === 'production') {
                error_log("Database Connection Error: " . $e->getMessage());
                die("<div style='font-family:sans-serif; text-align:center; padding:60px 20px; background:#F8FAFC; min-height:100vh; display:flex; flex-direction:column; justify-content:center; align-items:center;'>"
                  . "<div style='background:#fff; border-radius:12px; padding:32px; max-width:480px; box-shadow:0 10px 25px rgba(0,0,0,0.08); border-top:4px solid #DC2626;'>"
                  . "<h2 style='color:#1E293B; margin-top:0;'>Koneksi Database Belum Terhubung</h2>"
                  . "<p style='color:#64748B; line-height:1.6;'>Sistem web tidak dapat tersambung ke database MySQL di server production. Pastikan database dan berkas <code>.env</code> sudah dikonfigurasi dengan benar.</p>"
                  . "</div></div>");
            } else {
                die("Koneksi MySQL Gagal: " . htmlspecialchars($e->getMessage()));
            }
        }
    }

    // Pastikan tabel-tabel terbuat
    init_spmb_tables($pdo);

    return $pdo;
}

/**
 * Inisialisasi Tabel dan Data Awal SPMB
 */
function init_spmb_tables(PDO $pdo) {
    // Tabel Pengaturan
    $pdo->exec("CREATE TABLE IF NOT EXISTS `spmb_pengaturan` (
        `kunci` VARCHAR(50) PRIMARY KEY,
        `nilai` TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabel Admin
    $pdo->exec("CREATE TABLE IF NOT EXISTS `spmb_admin` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `nama` VARCHAR(100) NOT NULL,
        `role` VARCHAR(50) DEFAULT 'admin',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabel Pendaftar
    $pdo->exec("CREATE TABLE IF NOT EXISTS `spmb_pendaftar` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `no_pendaftaran` VARCHAR(50) NOT NULL UNIQUE,
        `nisn` VARCHAR(20) NOT NULL,
        `nik` VARCHAR(20) NOT NULL,
        `nama_lengkap` VARCHAR(150) NOT NULL,
        `jenis_kelamin` ENUM('L', 'P') NOT NULL,
        `tempat_lahir` VARCHAR(100) NOT NULL,
        `tanggal_lahir` DATE NOT NULL,
        `agama` VARCHAR(50) DEFAULT NULL,
        `no_hp` VARCHAR(25) NOT NULL,
        `alamat` TEXT NOT NULL,
        `asal_sekolah` VARCHAR(150) NOT NULL,
        `jurusan_1` VARCHAR(50) NOT NULL,
        `jurusan_2` VARCHAR(50) DEFAULT NULL,
        `jurusan_diterima` VARCHAR(50) DEFAULT NULL,
        `jalur` VARCHAR(50) NOT NULL DEFAULT 'Reguler',
        `nama_ayah` VARCHAR(150) NOT NULL,
        `nama_ibu` VARCHAR(150) NOT NULL,
        `pekerjaan_ortu` VARCHAR(100) DEFAULT NULL,
        `no_hp_ortu` VARCHAR(25) NOT NULL,
        `penghasilan_ortu` VARCHAR(100) DEFAULT NULL,
        `foto` VARCHAR(255) DEFAULT NULL,
        `berkas_kk` VARCHAR(255) DEFAULT NULL,
        `berkas_akta` VARCHAR(255) DEFAULT NULL,
        `berkas_ijazah` VARCHAR(255) DEFAULT NULL,
        `berkas_ktp_ortu` VARCHAR(255) DEFAULT NULL,
        `berkas_kip` VARCHAR(255) DEFAULT NULL,
        `berkas_prestasi` VARCHAR(255) DEFAULT NULL,
        `status` ENUM('Menunggu Verifikasi', 'Diterima', 'Cadangan', 'Perlu Perbaikan', 'Ditolak') NOT NULL DEFAULT 'Menunggu Verifikasi',
        `catatan_admin` TEXT DEFAULT NULL,
        `ruang_tes` VARCHAR(50) DEFAULT NULL,
        `jadwal_tes` VARCHAR(100) DEFAULT NULL,
        `tanggal_daftar` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_nisn (`nisn`),
        INDEX idx_status (`status`),
        INDEX idx_jurusan (`jurusan_1`),
        INDEX idx_jurusan_diterima (`jurusan_diterima`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed default admin jika belum ada
    $stmt = $pdo->query("SELECT COUNT(*) FROM `spmb_admin`");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $insert = $pdo->prepare("INSERT INTO `spmb_admin` (`username`, `password`, `nama`, `role`) VALUES (?, ?, ?, ?)");
        $insert->execute(['admin', $hash, 'Administrator SPMB', 'admin']);
    }

    // Seed default pengaturan jika belum ada
    $defaults = [
        'status_pendaftaran' => 'otomatis',
        'tanggal_mulai' => '2027-02-15',
        'tanggal_selesai' => '2027-05-10',
        'tahun_ajaran' => '2027-2028',
        'gelombang' => 'Gelombang 1 (segera dibuka)',
        'periode_gelombang' => '15 Februari - 10 Mei 2027',
        'hotline_wa' => '081234567890',
        'email_spmb' => 'spmb@smksukapura.sch.id',
        'biaya_pendaftaran' => "• Hanya 100.000 (Reguler)\n• 50.000 (Prestasi)\n• Gratis (Yatim/Piatu) + Mendapatkan Baju Putih Abu",
        'pengumuman_header' => 'Penerimaan Siswa Baru SMKS Sukapura TA 2027-2028 Segera Dibuka!'
    ];

    global $DAFTAR_JURUSAN;
    foreach ($DAFTAR_JURUSAN as $kode => $j) {
        $defaults['kuota_' . $kode] = (string)$j['kuota'];
    }

    $checkSetting = $pdo->prepare("SELECT COUNT(*) FROM `spmb_pengaturan` WHERE `kunci` = ?");
    $insertSetting = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?)");

    foreach ($defaults as $k => $v) {
        $checkSetting->execute([$k]);
        if ($checkSetting->fetchColumn() == 0) {
            $insertSetting->execute([$k, $v]);
        }
    }

    // Seed data contoh awal pendaftar jika kosong
    $stmtPendaftar = $pdo->query("SELECT COUNT(*) FROM `spmb_pendaftar`");
    if ($stmtPendaftar->fetchColumn() == 0) {
        $seedApplicants = [
            [
                'no' => 'REG-SKPR-74792',
                'nisn' => '0071234561',
                'nik' => '3206010101080001',
                'nama' => 'Ahmad Fauzi Ridwan',
                'jk' => 'L',
                'tmp_lahir' => 'Tasikmalaya',
                'tgl_lahir' => '2009-04-12',
                'agama' => 'Islam',
                'no_hp' => '081223344551',
                'alamat' => 'Kp. Sukasenang RT 02/04, Singaparna, Tasikmalaya',
                'sekolah' => 'SMPN 1 Singaparna',
                'jur1' => 'PPLG',
                'jur2' => 'TJKT',
                'jalur' => 'Reguler',
                'ayah' => 'Deden Ridwan',
                'ibu' => 'Siti Rohani',
                'pek_ortu' => 'Wiraswasta',
                'hp_ortu' => '081334455661',
                'penghasilan' => 'Rp 2.500.000 - Rp 5.000.000',
                'status' => 'Diterima',
                'catatan' => 'Selamat, berkas dan nilai telah diverifikasi. Memenuhi syarat diterima di pilihan ke-1.',
                'ruang' => 'Lab Komputer 1',
                'jadwal' => '15 Mei 2026, Pukul 08.00 WIB'
            ],
            [
                'no' => 'REG-SKPR-16544',
                'nisn' => '0071234562',
                'nik' => '3206010202080002',
                'nama' => 'Nabila Putri Azzahra',
                'jk' => 'P',
                'tmp_lahir' => 'Tasikmalaya',
                'tgl_lahir' => '2009-08-25',
                'agama' => 'Islam',
                'no_hp' => '081223344552',
                'alamat' => 'Jl. Cikunir Hilir No. 45, Singaparna',
                'sekolah' => 'MTsN 2 Tasikmalaya',
                'jur1' => 'DKV',
                'jur2' => 'MP',
                'jalur' => 'Prestasi',
                'ayah' => 'Bambang Irawan',
                'ibu' => 'Ratna Dewi',
                'pek_ortu' => 'Guru',
                'hp_ortu' => '081334455662',
                'penghasilan' => 'Rp 3.000.000 - Rp 5.000.000',
                'status' => 'Menunggu Verifikasi',
                'catatan' => 'Berkas pendaftaran sedang dalam antrean verifikasi administrasi panitia.',
                'ruang' => 'Ruang Teori 102',
                'jadwal' => '15 Mei 2026, Pukul 09.30 WIB'
            ],
            [
                'no' => 'REG-SKPR-72010',
                'nisn' => '0071234563',
                'nik' => '3206010303080003',
                'nama' => 'Muhammad Rizky Pratama',
                'jk' => 'L',
                'tmp_lahir' => 'Bandung',
                'tgl_lahir' => '2009-01-19',
                'agama' => 'Islam',
                'no_hp' => '081223344553',
                'alamat' => 'Perum Sukapura Asri Blok C-12, Tasikmalaya',
                'sekolah' => 'SMP Plus Sukapura',
                'jur1' => 'TJKT',
                'jur2' => 'TSM',
                'jalur' => 'Reguler',
                'ayah' => 'Hendra Gunawan',
                'ibu' => 'Lilis Suryani',
                'pek_ortu' => 'Karyawan Swasta',
                'hp_ortu' => '081334455663',
                'penghasilan' => 'Rp 4.000.000 - Rp 6.000.000',
                'status' => 'Cadangan',
                'catatan' => 'Pilihan 1 penuh, masuk daftar tunggu (cadangan) untuk verifikasi tahap 2.',
                'ruang' => 'Lab Jaringan',
                'jadwal' => '16 Mei 2026, Pukul 08.00 WIB'
            ]
        ];

        $ins = $pdo->prepare("INSERT INTO `spmb_pendaftar` 
            (`no_pendaftaran`, `nisn`, `nik`, `nama_lengkap`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `agama`, `no_hp`, `alamat`, `asal_sekolah`, `jurusan_1`, `jurusan_2`, `jalur`, `nama_ayah`, `nama_ibu`, `pekerjaan_ortu`, `no_hp_ortu`, `penghasilan_ortu`, `status`, `catatan_admin`, `ruang_tes`, `jadwal_tes`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($seedApplicants as $s) {
            $ins->execute([
                $s['no'], $s['nisn'], $s['nik'], $s['nama'], $s['jk'], $s['tmp_lahir'], $s['tgl_lahir'],
                $s['agama'], $s['no_hp'], $s['alamat'], $s['sekolah'], $s['jur1'], $s['jur2'], $s['jalur'],
                $s['ayah'], $s['ibu'], $s['pek_ortu'], $s['hp_ortu'], $s['penghasilan'],
                $s['status'], $s['catatan'], $s['ruang'], $s['jadwal']
            ]);
        }
    }
}

/**
 * Sinkronisasi kuota dinamis dari database ke variabel global $DAFTAR_JURUSAN
 */
function sync_daftar_jurusan() {
    global $DAFTAR_JURUSAN;
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT `kunci`, `nilai` FROM `spmb_pengaturan` WHERE `kunci` LIKE 'kuota_%'");
        while ($row = $stmt->fetch()) {
            $kode = str_replace('kuota_', '', $row['kunci']);
            if (isset($DAFTAR_JURUSAN[$kode]) && is_numeric($row['nilai']) && (int)$row['nilai'] >= 0) {
                $DAFTAR_JURUSAN[$kode]['kuota'] = (int)$row['nilai'];
            }
        }
    } catch (Exception $e) {
        // Abaikan bila database belum siap
    }
}

/**
 * Ambil semua pengaturan sistem
 */
function get_all_settings() {
    $pdo = get_db_connection();
    $stmt = $pdo->query("SELECT `kunci`, `nilai` FROM `spmb_pengaturan`");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['kunci']] = $row['nilai'];
    }
    sync_daftar_jurusan();
    return $settings;
}

/**
 * Generate Nomor Pendaftaran baru yang unik & acak (tidak berurutan)
 * Format: REG-SKPR-XXXXX (Contoh: REG-SKPR-78421)
 */
function generate_no_pendaftaran($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `no_pendaftaran` = ?");
    
    do {
        // 5 digit angka acak (10000 - 99999) agar unik, tidak berurutan, dan ramah diketik di smartphone
        $randomCode = mt_rand(10000, 99999);
        $no = 'REG-SKPR-' . $randomCode;
        
        $stmt->execute([$no]);
        $exists = (int)$stmt->fetchColumn() > 0;
    } while ($exists);
    
    return $no;
}

/**
 * Cek autentikasi admin
 */
function check_admin_login($redirect = true) {
    if (empty($_SESSION['spmb_admin_id'])) {
        if ($redirect) {
            header("Location: login.php");
            exit;
        }
        return false;
    }
    return true;
}

/**
 * Helper Sanitasi Input
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Tanggal Indonesia
 */
function tgl_indo($tanggal) {
    if (!$tanggal || $tanggal === '0000-00-00') return '-';
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
    return (int)$pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

/**
 * Dapatkan badge HTML untuk status pendaftar
 */
function get_status_badge_html($status) {
    switch ($status) {
        case 'Diterima':
            return '<span class="spmb-badge spmb-badge-success"><i class="ph-bold ph-check-circle"></i> Diterima</span>';
        case 'Cadangan':
            return '<span class="spmb-badge spmb-badge-warning"><i class="ph-bold ph-clock"></i> Cadangan</span>';
        case 'Perlu Perbaikan':
            return '<span class="spmb-badge" style="background:#FEF3C7; color:#B45309; border:1.5px solid #F59E0B; font-weight:700;"><i class="ph-bold ph-pencil-simple-line"></i> Perlu Perbaikan</span>';
        case 'Ditolak':
            return '<span class="spmb-badge spmb-badge-danger"><i class="ph-bold ph-x-circle"></i> Ditolak</span>';
        default:
            return '<span class="spmb-badge spmb-badge-pending"><i class="ph-bold ph-hourglass-high"></i> Menunggu Verifikasi</span>';
    }
}

/**
 * Hitung jumlah pendaftar yang sudah Diterima pada suatu jurusan
 */
function get_jurusan_kuota_terisi($pdo, $kode_jurusan, $exclude_id = null) {
    $sql = "SELECT COUNT(*) FROM `spmb_pendaftar` WHERE `status` = 'Diterima' AND (
        `jurusan_diterima` = ? OR (`jurusan_diterima` IS NULL AND `jurusan_1` = ?)
    )";
    $params = [$kode_jurusan, $kode_jurusan];
    if ($exclude_id !== null && (int)$exclude_id > 0) {
        $sql .= " AND `id` != ?";
        $params[] = (int)$exclude_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

/**
 * Ambil rincian kuota jurusan (total, terisi, sisa, penuh)
 */
function get_jurusan_info_kuota($pdo, $kode_jurusan, $exclude_id = null) {
    global $DAFTAR_JURUSAN;
    sync_daftar_jurusan();
    
    $total = isset($DAFTAR_JURUSAN[$kode_jurusan]['kuota']) ? (int)$DAFTAR_JURUSAN[$kode_jurusan]['kuota'] : 72;
    $terisi = get_jurusan_kuota_terisi($pdo, $kode_jurusan, $exclude_id);
    $sisa = max(0, $total - $terisi);
    $penuh = ($terisi >= $total);

    return [
        'kode' => $kode_jurusan,
        'nama' => $DAFTAR_JURUSAN[$kode_jurusan]['nama'] ?? $kode_jurusan,
        'total' => $total,
        'terisi' => $terisi,
        'sisa' => $sisa,
        'penuh' => $penuh
    ];
}

/**
 * Evaluasi alokasi kuota otomatis ketika pendaftar diterima:
 * 1. Jika Pilihan 1 masih ada kuota -> Diterima di Pilihan 1.
 * 2. Jika Pilihan 1 penuh dan ada Pilihan 2 yang masih ada kuota -> Otomatis dialihkan & diterima di Pilihan 2.
 * 3. Jika Pilihan 1 dan Pilihan 2 dua-duanya penuh -> Otomatis Ditolak karena kuota penuh.
 */
function evaluasi_kuota_pendaftar($pdo, $id_pendaftar, $jurusan_1, $jurusan_2 = null) {
    $infoP1 = get_jurusan_info_kuota($pdo, $jurusan_1, $id_pendaftar);
    
    // Jika Pilihan 1 masih tersedia
    if (!$infoP1['penuh']) {
        return [
            'status' => 'Diterima',
            'jurusan_diterima' => $jurusan_1,
            'dialihkan' => false,
            'alasan' => 'Diterima di Jurusan Pilihan 1 (' . $jurusan_1 . ' - ' . $infoP1['nama'] . ').',
            'info_p1' => $infoP1,
            'info_p2' => !empty($jurusan_2) ? get_jurusan_info_kuota($pdo, $jurusan_2, $id_pendaftar) : null
        ];
    }

    // Jika Pilihan 1 PENUH, cek Pilihan 2 jika ada
    if (!empty($jurusan_2)) {
        $infoP2 = get_jurusan_info_kuota($pdo, $jurusan_2, $id_pendaftar);
        if (!$infoP2['penuh']) {
            return [
                'status' => 'Diterima',
                'jurusan_diterima' => $jurusan_2,
                'dialihkan' => true,
                'alasan' => 'Kuota Pilihan 1 (' . $jurusan_1 . ') telah penuh. Otomatis dialihkan dan diterima di Pilihan 2 (' . $jurusan_2 . ' - ' . $infoP2['nama'] . ').',
                'info_p1' => $infoP1,
                'info_p2' => $infoP2
            ];
        }
    }

    // Pilihan 1 penuh dan Pilihan 2 juga penuh (atau tidak memilih pilihan 2)
    // Sesuai alur baru: Calon siswa TIDAK langsung ditolak, melainkan dialihkan ke status Perlu Perbaikan
    // dengan batas waktu 1 minggu (7 hari) untuk memilih jurusan lain yang masih memiliki sisa kuota.
    $infoP2 = !empty($jurusan_2) ? get_jurusan_info_kuota($pdo, $jurusan_2, $id_pendaftar) : null;
    $pesanGantiJurusan = 'Mohon maaf, kuota penerimaan untuk jurusan pilihan 1 dan pilihan 2 Anda telah penuh. Silakan login ke menu Perbaiki Formulir dan ganti pilihan jurusan Anda ke kompetensi keahlian yang masih tersedia kuota.';
    $tenggatPerbaikan = date('Y-m-d H:i:s', strtotime('+7 days'));

    return [
        'status' => 'Perlu Perbaikan',
        'jurusan_diterima' => null,
        'dialihkan' => false,
        'kuota_habis' => true,
        'kuota_semua_penuh' => true,
        'tenggat_perbaikan' => $tenggatPerbaikan,
        'alasan' => $pesanGantiJurusan,
        'info_p1' => $infoP1,
        'info_p2' => $infoP2
    ];
}

/**
 * Dapatkan badge HTML untuk jalur pendaftaran (Reguler, Prestasi, Yatim/Piatu)
 */
function get_jalur_badge_html($jalur) {
    switch ($jalur) {
        case 'Prestasi':
            return '<span class="spmb-badge" style="background:#fef3c7; color:#b45309; border:1.5px solid #b45309; font-size:0.8rem; font-weight:700;"><i class="ph-bold ph-trophy"></i> Prestasi</span>';
        case 'Yatim/Piatu':
            return '<span class="spmb-badge" style="background:#dcfce7; color:#15803d; border:1.5px solid #15803d; font-size:0.8rem; font-weight:700;"><i class="ph-bold ph-heart"></i> Yatim/Piatu</span>';
        default:
            return '<span class="spmb-badge" style="background:#e0f2fe; color:#0369a1; border:1.5px solid #0369a1; font-size:0.8rem; font-weight:700;"><i class="ph-bold ph-student"></i> Reguler</span>';
    }
}

/**
 * Helper mengurai rentang tanggal Indonesia (misal: "15 Februari - 10 Mei 2027") menjadi format Y-m-d
 */
function parse_indonesian_date_range($str) {
    if (empty($str)) return null;
    $months = [
        'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04',
        'mei' => '05', 'juni' => '06', 'juli' => '07', 'agustus' => '08',
        'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12'
    ];
    $parts = explode('-', $str);
    if (count($parts) < 2) return null;
    $p1 = trim($parts[0]);
    $p2 = trim($parts[1]);

    if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $p2, $m2)) {
        $d2 = str_pad($m2[1], 2, '0', STR_PAD_LEFT);
        $mo2 = $months[strtolower($m2[2])] ?? '01';
        $y2 = $m2[3];
        $date2 = "$y2-$mo2-$d2";

        if (preg_match('/(\d{1,2})\s+([a-zA-Z]+)(?:\s+(\d{4}))?/', $p1, $m1)) {
            $d1 = str_pad($m1[1], 2, '0', STR_PAD_LEFT);
            $mo1 = $months[strtolower($m1[2])] ?? '01';
            $y1 = !empty($m1[3]) ? $m1[3] : $y2;
            $date1 = "$y1-$mo1-$d1";
            return ['mulai' => $date1, 'selesai' => $date2];
        }
    }
    return null;
}

/**
 * Mengecek apakah pendaftaran SPMB sedang dibuka (berdasarkan mode manual atau otomatis jadwal tanggal)
 */
function is_spmb_open($settings = null) {
    if ($settings === null) {
        $settings = get_all_settings();
    }

    $mode = $settings['status_pendaftaran'] ?? 'otomatis';

    if ($mode === 'buka') {
        return true;
    }
    if ($mode === 'tutup') {
        return false;
    }

    // Mode otomatis / jadwal tanggal
    $tglMulai = $settings['tanggal_mulai'] ?? '';
    $tglSelesai = $settings['tanggal_selesai'] ?? '';

    if (empty($tglMulai) || empty($tglSelesai)) {
        $parsed = parse_indonesian_date_range($settings['periode_gelombang'] ?? '');
        if ($parsed) {
            $tglMulai = $tglMulai ?: $parsed['mulai'];
            $tglSelesai = $tglSelesai ?: $parsed['selesai'];
        }
    }

    $today = date('Y-m-d');

    if (!empty($tglMulai) && !empty($tglSelesai)) {
        return ($today >= $tglMulai && $today <= $tglSelesai);
    } elseif (!empty($tglMulai)) {
        return ($today >= $tglMulai);
    } elseif (!empty($tglSelesai)) {
        return ($today <= $tglSelesai);
    }

    return false;
}

/**
 * Dapatkan informasi lengkap status pendaftaran (label, warna, pesan penjelasan)
 */
function get_spmb_status_info($settings = null) {
    if ($settings === null) {
        $settings = get_all_settings();
    }

    $mode = $settings['status_pendaftaran'] ?? 'otomatis';
    $tglMulai = $settings['tanggal_mulai'] ?? '';
    $tglSelesai = $settings['tanggal_selesai'] ?? '';

    if (empty($tglMulai) || empty($tglSelesai)) {
        $parsed = parse_indonesian_date_range($settings['periode_gelombang'] ?? '');
        if ($parsed) {
            $tglMulai = $tglMulai ?: $parsed['mulai'];
            $tglSelesai = $tglSelesai ?: $parsed['selesai'];
        }
    }

    $today = date('Y-m-d');
    $isOpen = false;
    $label = 'Pendaftaran Ditutup';
    $badgeColor = '#DC2626';
    $note = '';

    if ($mode === 'buka') {
        $isOpen = true;
        $label = 'Sedang Dibuka (Manual)';
        $badgeColor = '#16A34A';
        $note = 'Dipaksa buka oleh panitia';
    } elseif ($mode === 'tutup') {
        $isOpen = false;
        $label = 'Ditutup (Manual)';
        $badgeColor = '#DC2626';
        $note = 'Dipaksa tutup oleh panitia';
    } else {
        // Mode Otomatis
        if (!empty($tglMulai) && $today < $tglMulai) {
            $isOpen = false;
            $label = 'Segera Dibuka';
            $badgeColor = '#D97706';
            $note = 'Pendaftaran dibuka pada ' . tgl_indo($tglMulai);
        } elseif (!empty($tglSelesai) && $today > $tglSelesai) {
            $isOpen = false;
            $label = 'Gelombang Berakhir';
            $badgeColor = '#DC2626';
            $note = 'Pendaftaran berakhir pada ' . tgl_indo($tglSelesai);
        } else {
            $isOpen = true;
            $label = 'Sedang Dibuka';
            $badgeColor = '#16A34A';
            if (!empty($tglSelesai)) {
                $note = 'Batas akhir: ' . tgl_indo($tglSelesai);
            }
        }
    }

    return [
        'isOpen' => $isOpen,
        'label' => $label,
        'mode' => $mode,
        'badgeColor' => $badgeColor,
        'note' => $note,
        'tglMulai' => $tglMulai,
        'tglSelesai' => $tglSelesai
    ];
}

/**
 * Parse string jadwal tes ke format input tanggal (YYYY-MM-DD) dan jam (HH:mm)
 */
function parse_jadwal_tes($raw) {
    $tgl = '';
    $jam = '';
    if (empty($raw)) return [$tgl, $jam];

    $bulanMap = [
        'januari' => '01', 'jan' => '01',
        'februari' => '02', 'feb' => '02',
        'maret' => '03', 'mar' => '03',
        'april' => '04', 'apr' => '04',
        'mei' => '05',
        'juni' => '06', 'jun' => '06',
        'juli' => '07', 'jul' => '07',
        'agustus' => '08', 'agu' => '08', 'agt' => '08',
        'september' => '09', 'sep' => '09',
        'oktober' => '10', 'okt' => '10',
        'november' => '11', 'nov' => '11',
        'desember' => '12', 'des' => '12'
    ];

    // Ekstrak jam (misal 07:00, 08.00, 09:30)
    if (preg_match('/(\d{1,2})[:.](\d{2})/', $raw, $mJam)) {
        $jam = sprintf('%02d:%02d', (int)$mJam[1], (int)$mJam[2]);
    }

    // Ekstrak tanggal (format YYYY-MM-DD atau D Bulan YYYY)
    if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $raw, $mTglIso)) {
        $tgl = "{$mTglIso[1]}-{$mTglIso[2]}-{$mTglIso[3]}";
    } elseif (preg_match('/(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})/', $raw, $mTglIndo)) {
        $hari = sprintf('%02d', (int)$mTglIndo[1]);
        $bStr = strtolower($mTglIndo[2]);
        $tahun = $mTglIndo[3];
        if (isset($bulanMap[$bStr])) {
            $tgl = "{$tahun}-{$bulanMap[$bStr]}-{$hari}";
        }
    }

    return [$tgl, $jam];
}

/**
 * Format tanggal YYYY-MM-DD dan jam HH:mm menjadi string jadwal rapi
 */
function format_jadwal_tes($tanggal, $jam = '') {
    if (empty($tanggal)) return '';
    $ts = strtotime($tanggal);
    if (!$ts) return $tanggal;

    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $d = date('j', $ts);
    $m = (int)date('n', $ts);
    $y = date('Y', $ts);
    $namaBulan = $bulanIndo[$m] ?? date('F', $ts);

    $hasil = "{$d} {$namaBulan} {$y}";
    if (!empty($jam)) {
        $hasil .= ", " . trim($jam) . " WIB";
    }
    return $hasil;
}

// Load Modul WhatsApp Fonnte & Auto Expire Helper
if (file_exists(__DIR__ . '/fonnte.php')) {
    require_once __DIR__ . '/fonnte.php';
    // Otomatisasi pemeriksaan tenggat waktu 1 minggu jika pdo tersedia
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            cek_auto_tolak_tenggat_perbaikan($pdo);
        } catch (Exception $e) {
            // Abaikan error background auto-expire
        }
    }
}
