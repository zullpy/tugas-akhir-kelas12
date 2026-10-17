<?php
// spmb/fonnte.php
// Modul Integrasi WhatsApp Gateway (Fonnte API) & Otomatisasi SPMB SMKS SUKAPURA

require_once __DIR__ . '/config.php';

/**
 * Ambil konfigurasi Fonnte dari database & deteksi Base URL hosting secara 100% otomatis
 * Saat di hosting (smksukapura.miw.web.id), otomatis menggunakan domain resmi tanpa perlu diubah manual!
 */
function get_fonnte_config() {
    $settings = get_all_settings();

    // 1. Deteksi host server saat ini secara dinamis
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $hostOnly = strtolower(explode(':', $httpHost)[0]);
    $isLocalHost = in_array($hostOnly, ['localhost', '127.0.0.1', '::1', '']);

    // 2. Jika website sedang berjalan di hosting / live domain (bukan localhost)
    if (!$isLocalHost && !empty($httpHost)) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                  (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') 
                  ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $httpHost;
    } 
    // 3. Jika di environment production / CLI
    elseif (defined('APP_ENV') && APP_ENV === 'production') {
        $baseUrl = 'https://smksukapura.miw.web.id';
    } 
    // 4. Default di local development
    else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $httpHost ?: 'localhost:8000';
        $baseUrl = $scheme . '://' . $host;
    }

    $baseUrl = rtrim($baseUrl, '/');

    return [
        'token'    => trim($settings['fonnte_token'] ?? ''),
        'status'   => ($settings['fonnte_status'] ?? '1') === '1',
        'base_url' => $baseUrl
    ];
}

/**
 * Format nomor telepon ke standar internasional Indonesia (62xxx)
 */
function format_nomor_wa($nomor) {
    $clean = preg_replace('/[^0-9]/', '', (string)$nomor);
    if (empty($clean)) return '';

    // Jika diawali 08, ganti 0 dengan 62
    if (substr($clean, 0, 1) === '0') {
        $clean = '62' . substr($clean, 1);
    } elseif (substr($clean, 0, 2) !== '62') {
        $clean = '62' . $clean;
    }
    return $clean;
}

/**
 * Kirim pesan WhatsApp menggunakan API Fonnte
 * Mendukung pesan teks dan attachment file (PDF, gambar, dll)
 *
 * @param string $target Nomor tujuan
 * @param string $message Teks pesan
 * @param string|null $filePath Path file lokal yang akan dilampirkan
 * @param string|null $fileName Nama file saat diterima (opsional)
 * @return array ['success' => bool, 'response' => array|string, 'error' => string]
 */
function kirim_wa_fonnte($target, $message, $filePath = null, $fileName = null) {
    $cfg = get_fonnte_config();

    if (empty($cfg['token'])) {
        return [
            'success' => false,
            'skipped' => true,
            'error'   => 'Token API Fonnte belum dikonfigurasi pada menu Pengaturan SPMB.'
        ];
    }

    if (!$cfg['status']) {
        return [
            'success' => false,
            'skipped' => true,
            'error'   => 'Layanan integrasi WhatsApp Fonnte sedang dinonaktifkan di pengaturan.'
        ];
    }

    $nomorTujuan = format_nomor_wa($target);
    if (empty($nomorTujuan) || strlen($nomorTujuan) < 8) {
        return [
            'success' => false,
            'skipped' => false,
            'error'   => 'Nomor telepon calon siswa tidak valid (' . htmlspecialchars($target) . ').'
        ];
    }

    $postData = [
        'target'      => $nomorTujuan,
        'message'     => $message,
        'countryCode' => '62'
    ];

    // Jika ada file fisik yang ingin dikirimkan sebagai attachment (misal PDF Kartu Peserta)
    if (!empty($filePath) && file_exists($filePath)) {
        $postData['file'] = new CURLFile(
            $filePath, 
            'application/pdf', 
            $fileName ?: basename($filePath)
        );
        $postData['filename'] = $fileName ?: basename($filePath);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . $cfg['token']
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($curl);
    $curlErr  = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (PHP_VERSION_ID < 80500) {
        @curl_close($curl);
    }

    if ($curlErr) {
        return [
            'success'  => false,
            'httpCode' => $httpCode,
            'error'    => 'Koneksi cURL Gagal: ' . $curlErr
        ];
    }

    $json = json_decode($response, true);

    // Fonnte biasanya mengembalikan {"status": true, ...}
    if (is_array($json) && (!empty($json['status']) || ($json['status'] ?? false) === true)) {
        return [
            'success'  => true,
            'httpCode' => $httpCode,
            'response' => $json
        ];
    } else {
        $reason = $json['reason'] ?? ($json['detail'] ?? ($json['message'] ?? $response));
        return [
            'success'  => false,
            'httpCode' => $httpCode,
            'error'    => 'Fonnte Error: ' . (is_string($reason) ? $reason : json_encode($reason)),
            'response' => $json
        ];
    }
}

/**
 * Periksa apakah sebuah nomor HP benar-benar terdaftar aktif di aplikasi WhatsApp
 * Menggunakan endpoint resmi Fonnte /validate yang langsung mengecek ke jaringan WhatsApp
 * 
 * @param string $nomor
 * @return array ['checked' => bool, 'is_wa' => bool, 'message' => string]
 */
function cek_nomor_wa_fonnte($nomor) {
    $cfg = get_fonnte_config();
    if (empty($cfg['token']) || !$cfg['status']) {
        return [
            'checked' => false,
            'is_wa'   => true, // Fallback pass jika gateway tidak aktif agar tidak memblokir pendaftar
            'message' => 'Layanan Fonnte belum aktif / tidak dikonfigurasi.'
        ];
    }

    $clean = preg_replace('/[^0-9]/', '', (string)$nomor);
    if (empty($clean) || strlen($clean) < 9) {
        return [
            'checked' => true,
            'is_wa'   => false,
            'message' => 'Nomor telepon tidak valid.'
        ];
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => 'https://api.fonnte.com/validate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'target'      => $clean,
            'countryCode' => '62'
        ],
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . $cfg['token']
        ],
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $curlErr  = curl_error($curl);

    if ($curlErr || empty($response)) {
        return [
            'checked' => false,
            'is_wa'   => true,
            'message' => 'Gagal menghubungi server validasi WhatsApp.'
        ];
    }

    $json = json_decode($response, true);
    if (!is_array($json) || empty($json['status'])) {
        return [
            'checked' => false,
            'is_wa'   => true,
            'message' => $json['reason'] ?? 'Validasi gateway tidak merespon.'
        ];
    }

    $registered = $json['registered'] ?? [];
    $notRegistered = $json['not_registered'] ?? [];

    if (!empty($registered)) {
        return [
            'checked' => true,
            'is_wa'   => true,
            'message' => 'Nomor aktif dan terdaftar di WhatsApp.'
        ];
    }

    if (!empty($notRegistered)) {
        return [
            'checked' => true,
            'is_wa'   => false,
            'message' => 'Nomor HP ini TIDAK terdaftar di aplikasi WhatsApp.'
        ];
    }

    return [
        'checked' => false,
        'is_wa'   => true,
        'message' => 'Status nomor tidak dapat dipastikan.'
    ];
}

/**
 * Generate PDF Kartu Peserta resmi menggunakan Google Chrome headless
 * 
 * @param PDO $pdo
 * @param string $no_pendaftaran
 * @return string|false Path file PDF jika sukses, atau false jika gagal
 */
function generate_kartu_peserta_pdf($pdo, $no_pendaftaran) {
    // 1. Cek apakah fungsi eksekusi shell tersedia dan tidak dinonaktifkan di server hosting
    if (!function_exists('shell_exec') || !function_exists('exec')) {
        return false;
    }

    $disabledFuncs = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    if (in_array('shell_exec', $disabledFuncs) || in_array('exec', $disabledFuncs)) {
        return false;
    }

    try {
        $tempDir = __DIR__ . '/uploads/temp_pdf';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $safeNo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $no_pendaftaran);
        $pdfPath = $tempDir . '/Kartu_Peserta_' . $safeNo . '.pdf';

        // Cari executable google-chrome / chromium di sistem
        $chromeBin = '';
        if (file_exists('/usr/bin/google-chrome-stable')) {
            $chromeBin = '/usr/bin/google-chrome-stable';
        } else {
            $foundChrome = trim(@shell_exec('which google-chrome-stable 2>/dev/null') ?: '');
            if (!empty($foundChrome) && file_exists($foundChrome)) {
                $chromeBin = $foundChrome;
            } else {
                $foundChromium = trim(@shell_exec('which chromium 2>/dev/null') ?: '');
                if (!empty($foundChromium) && file_exists($foundChromium)) {
                    $chromeBin = $foundChromium;
                }
            }
        }

        if (empty($chromeBin) || !file_exists($chromeBin)) {
            return false;
        }

        // URL halaman cetak (dinamis sesuai konfigurasi base_url atau APP_URL)
        $cfg = get_fonnte_config();
        $baseUrl = !empty($cfg['base_url']) ? $cfg['base_url'] : (defined('APP_URL') ? APP_URL : 'http://localhost:8000');
        $urlCetak = rtrim($baseUrl, '/') . '/spmb/cetak.php?no=' . urlencode($no_pendaftaran);

        $cmd = 'timeout 10s ' . escapeshellcmd($chromeBin) . 
               ' --headless --disable-gpu --no-sandbox --disable-dev-shm-usage' . 
               ' --print-to-pdf=' . escapeshellarg($pdfPath) . 
               ' ' . escapeshellarg($urlCetak) . ' 2>&1';

        @exec($cmd, $output, $returnCode);

        if (file_exists($pdfPath) && filesize($pdfPath) > 5000) {
            return $pdfPath;
        }
    } catch (\Throwable $e) {
        // Jika terjadi exception shell atau permission di shared hosting, lewati pembuatan PDF
        return false;
    }

    return false;
}

/**
 * Kirim Notifikasi WhatsApp Otomatis ke Calon Siswa berdasarkan status SPMB
 * 
 * @param PDO $pdo
 * @param int $pendaftar_id ID pendaftar
 * @param string $statusBaru Status baru (Diterima, Cadangan, Perlu Perbaikan, Ditolak)
 * @param array $opsi Opsi tambahan seperti catatan_admin, is_kuota_penuh, tenggat_perbaikan
 * @return array Status pengiriman
 */
function kirim_notifikasi_status_spmb($pdo, $pendaftar_id, $statusBaru, $opsi = []) {
    global $DAFTAR_JURUSAN;
    sync_daftar_jurusan();

    $stmt = $pdo->prepare("SELECT * FROM `spmb_pendaftar` WHERE `id` = ?");
    $stmt->execute([$pendaftar_id]);
    $p = $stmt->fetch();

    if (!$p) {
        return ['success' => false, 'error' => 'Data pendaftar tidak ditemukan.'];
    }

    $cfg = get_fonnte_config();
    $baseUrl = $cfg['base_url'];

    $nama = strtoupper($p['nama_lengkap']);
    $noPendaftaran = $p['no_pendaftaran'];
    $catatanAdmin = trim($opsi['catatan_admin'] ?? ($p['catatan_admin'] ?? ''));
    $isKuotaPenuh = !empty($opsi['is_kuota_penuh']);
    $tenggatPerbaikan = $opsi['tenggat_perbaikan'] ?? ($p['tenggat_perbaikan'] ?? null);

    $linkCetak  = $baseUrl . '/spmb/cetak.php?no=' . urlencode($noPendaftaran);
    $linkEdit   = $baseUrl . '/spmb/edit.php?no=' . urlencode($noPendaftaran);
    $linkStatus = $baseUrl . '/spmb/cek-status.php?no=' . urlencode($noPendaftaran);

    $message = '';
    $pdfAttachment = null;
    $attachmentName = null;

    if ($statusBaru === 'Diterima') {
        $kodeJurusan = !empty($p['jurusan_diterima']) ? $p['jurusan_diterima'] : $p['jurusan_1'];
        $namaJurusan = isset($DAFTAR_JURUSAN[$kodeJurusan]) ? ($kodeJurusan . ' - ' . $DAFTAR_JURUSAN[$kodeJurusan]['nama']) : $kodeJurusan;

        $infoTes = '';
        if (!empty($p['jadwal_tes'])) {
            $infoTes .= "\n *Jadwal Tes:* " . $p['jadwal_tes'];
        }
        if (!empty($p['ruang_tes'])) {
            $infoTes .= "\n *Ruang Tes:* " . $p['ruang_tes'];
        }

        // Generate Kartu Peserta PDF asli jika lingkungan hosting mendukung
        $generatedPdf = false;
        try {
            $generatedPdf = generate_kartu_peserta_pdf($pdo, $noPendaftaran);
        } catch (\Throwable $e) {
            $generatedPdf = false;
        }

        if ($generatedPdf && file_exists($generatedPdf)) {
            $pdfAttachment = $generatedPdf;
            $attachmentName = 'Kartu_Peserta_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $noPendaftaran) . '.pdf';
            $infoKartuPeserta = "Dokumen resmi kartu peserta Anda terlampir dalam bentuk file PDF bersama pesan ini. Anda juga dapat mengunduh atau mencetaknya secara langsung kapan saja melalui tautan:\n{$linkCetak}";
        } else {
            $infoKartuPeserta = "Dokumen resmi kartu peserta Anda dapat diunduh atau dicetak secara langsung melalui tautan resmi berikut:\n{$linkCetak}";
        }

        $message = "📢 *PENGUMUMAN HASIL SELEKSI SPMB SMKS SUKAPURA*\n\n"
                 . "Halo Sdr/i *{$nama}*,\n"
                 . "Nomor Pendaftaran: *{$noPendaftaran}*\n\n"
                 . "*SELAMAT! ANDA DINYATAKAN DITERIMA* di SMKS SUKAPURA pada Kompetensi Keahlian:\n"
                 . "*{$namaJurusan}*\n"
                 . $infoTes . "\n\n"
                 . "*Kartu Tanda Peserta / Bukti Kelulusan:*\n"
                 . $infoKartuPeserta . "\n\n"
                 . "*Petunjuk Daftar Ulang:*\n"
                 . "1. Buka tautan di atas dan cetak Kartu Peserta dalam ukuran kertas A4/F4.\n"
                 . "2. Hadir ke sekretariat SPMB SMKS Sukapura sesuai jadwal dengan membawa kartu peserta dan kelengkapan berkas fisik persyaratan.\n\n"
                 . "📄 *Persyaratan Berkas Dokumen (Dibawa Saat Daftar Ulang):*\n"
                 . "1. Fotokopi Ijazah / Surat Keterangan Lulus (SKL) dari SMP/MTs yang telah dilegalisir (2 lembar).\n"
                 . "2. Fotokopi Kartu Keluarga (KK) dan Akta Kelahiran calon peserta didik (2 lembar).\n"
                 . "3. Nomor Induk Siswa Nasional (NISN) aktif yang terdaftar di Kemendikbudristek.\n"
                 . "4. Pas Foto berwarna terbaru ukuran 3×4 (3 lembar) dengan latar belakang warna merah/biru.\n"
                 . "5. Sertifikat piagam kejuaraan asli & fotokopi (khusus pendaftar Jalur Prestasi).\n\n"
                 . "*Keterangan Pelaksanaan:*\n"
                 . "Seluruh berkas fisik di atas *wajib dibawa dan diserahkan saat proses Daftar Ulang* ke sekretariat panitia SPMB di kampus SMKS Sukapura setelah dinyatakan lulus seleksi.\n\n"
                 . "Selamat bergabung dengan keluarga besar SMKS Sukapura!\n\n"
                 . "Salam hangat,\n*Panitia SPMB SMKS SUKAPURA*";

    } elseif ($statusBaru === 'Perlu Perbaikan' && $isKuotaPenuh) {
        // Format Khusus Sesuai Permintaan User Saat Kuota Pilihan 1 & 2 Penuh
        $tenggatTeks = !empty($tenggatPerbaikan) 
            ? date('d F Y', strtotime($tenggatPerbaikan)) . ', pukul ' . date('H:i', strtotime($tenggatPerbaikan)) . ' WIB'
            : date('d F Y', strtotime('+7 days')) . ', pukul 23:59 WIB';

        $message = "⚠️ *PEMBERITAHUAN KUOTA JURUSAN SPMB SMKS SUKAPURA*\n\n"
                 . "Halo Sdr/i *{$nama}*,\n"
                 . "Nomor Pendaftaran: *{$noPendaftaran}*\n\n"
                 . "Mohon maaf, kuota penerimaan untuk jurusan pilihan 1 dan pilihan 2 Anda telah penuh. Silakan login ke menu Perbaiki Formulir dan ganti pilihan jurusan Anda ke kompetensi keahlian yang masih tersedia kuota.\n\n"
                 . "*Batas Waktu Penggantian Jurusan:* \n"
                 . "Maksimal 1 Minggu (Sebelum: *{$tenggatTeks}*)\n"
                 . "_PENTING: Jika sampai batas waktu 1 minggu tersebut Anda tidak melakukan konfirmasi ganti jurusan, pendaftaran Anda akan OTOMATIS DITOLAK oleh sistem._\n\n"
                 . "Silakan klik link berikut untuk login dan memilih jurusan lain yang masih memiliki sisa kuota:\n"
                 . "{$linkEdit}\n\n"
                 . "Terima kasih atas perhatian dan kerja samanya.\n\n"
                 . "*Panitia SPMB SMKS SUKAPURA*";

    } elseif ($statusBaru === 'Perlu Perbaikan') {
        // Format Umum Perlu Perbaikan (Misal berkas buram / NISN salah)
        $message = "⚠️ *PEMBERITAHUAN PERBAIKAN BERKAS SPMB SMKS SUKAPURA*\n\n"
                 . "Halo Sdr/i *{$nama}*,\n"
                 . "Nomor Pendaftaran: *{$noPendaftaran}*\n\n"
                 . "Berdasarkan hasil verifikasi panitia, terdapat berkas atau data pendaftaran Anda yang *memerlukan perbaikan/koreksi*.\n\n"
                 . "*Catatan Panitia:*\n"
                 . "\"" . ($catatanAdmin ?: 'Mohon periksa dan lengkapi kembali data atau berkas yang belum sesuai.') . "\"\n\n"
                 . "Silakan lakukan perbaikan secara mandiri melalui tautan formulir koreksi berikut:\n"
                 . "{$linkEdit}\n\n"
                 . "Mohon segera diperbaiki agar berkas Anda dapat diverifikasi kembali oleh panitia.\n\n"
                 . "Terima kasih,\n*Panitia SPMB SMKS SUKAPURA*";

    } elseif ($statusBaru === 'Cadangan') {
        $message = "📢 *INFORMASI STATUS PENDAFTARAN SPMB SMKS SUKAPURA*\n\n"
                 . "Halo Sdr/i *{$nama}*,\n"
                 . "Nomor Pendaftaran: *{$noPendaftaran}*\n\n"
                 . "Terima kasih telah mendaftar di SMKS SUKAPURA. Berdasarkan proses verifikasi berkas dan kapasitas kuota saat ini, status pendaftaran Anda ditetapkan sebagai:\n"
                 . "*CADANGAN*\n\n"
                 . "Calon siswa berstatus Cadangan akan diprioritaskan untuk diterima apabila terdapat kuota yang terbuka dari calon peserta didik utama yang mengundurkan diri atau tidak melakukan daftar ulang.\n\n"
                 . "Anda dapat memantau perkembangan status kelulusan Anda secara berkala di:\n"
                 . "{$linkStatus}\n\n"
                 . "Terima kasih,\n*Panitia SPMB SMKS SUKAPURA*";

    } elseif ($statusBaru === 'Ditolak') {
        $message = "📢 *PENGUMUMAN HASIL SELEKSI SPMB SMKS SUKAPURA*\n\n"
                 . "Halo Sdr/i *{$nama}*,\n"
                 . "Nomor Pendaftaran: *{$noPendaftaran}*\n\n"
                 . "Terima kasih banyak atas antusiasme dan partisipasi Anda dalam mengikuti seleksi SPMB SMKS SUKAPURA Tahun Ajaran ini.\n\n"
                 . "Setelah melalui proses evaluasi berkas dan ketersediaan daya tampung yang sangat terbatas, kami dengan berat hati menginformasikan bahwa pendaftaran Anda *belum dapat diterima* di SMKS SUKAPURA pada periode ini.\n\n"
                 . "\"Kegagalan hari ini bukanlah akhir dari perjalananmu, melainkan awal dari kesempatan baru yang menantimu di depan. Percayalah bahwa setiap orang memiliki jalan dan waktu terbaiknya masing-masing untuk sukses. Tetaplah bersemangat, jangan pernah berhenti belajar, dan teruslah berkarya mengejar impianmu!\"\n\n"
                 . "Kami mendoakan kesuksesan yang luar biasa untuk perjalanan pendidikan dan masa depan Anda berikutnya.\n\n"
                 . "Salam hangat dan rasa bangga,\n*Keluarga Besar SMKS SUKAPURA*";
    }

    if (empty($message)) {
        return ['success' => false, 'skipped' => true, 'error' => 'Tidak ada template pesan untuk status ini.'];
    }

    // Kirim pesan ke nomor WhatsApp calon siswa
    $res = kirim_wa_fonnte($p['no_hp'], $message, $pdfAttachment, $attachmentName);

    // Hapus file temporary PDF jika ada setelah dikirim
    if ($pdfAttachment && file_exists($pdfAttachment)) {
        @unlink($pdfAttachment);
    }

    return $res;
}

/**
 * Otomatisasi pemeriksaan tenggat waktu 1 minggu bagi calon siswa dengan kuota penuh
 * Mengubah status menjadi 'Ditolak' jika melewati batas waktu tanpa konfirmasi ganti jurusan
 * 
 * @param PDO $pdo
 * @return int Jumlah calon siswa yang otomatis ditolak
 */
function cek_auto_tolak_tenggat_perbaikan($pdo) {
    // Cari siswa yang statusnya 'Perlu Perbaikan' dan memiliki tenggat_perbaikan yang sudah lewat
    $stmt = $pdo->prepare("SELECT `id`, `no_pendaftaran`, `nama_lengkap`, `no_hp`, `catatan_admin` 
                           FROM `spmb_pendaftar` 
                           WHERE `status` = 'Perlu Perbaikan' 
                             AND `tenggat_perbaikan` IS NOT NULL 
                             AND `tenggat_perbaikan` <= NOW()");
    $stmt->execute();
    $expiredList = $stmt->fetchAll();

    $count = 0;
    if (!empty($expiredList)) {
        $upd = $pdo->prepare("UPDATE `spmb_pendaftar` SET 
                                `status` = 'Ditolak', 
                                `catatan_admin` = ?, 
                                `tenggat_perbaikan` = NULL 
                              WHERE `id` = ?");

        foreach ($expiredList as $item) {
            $catatanBaru = trim($item['catatan_admin'] ?? '');
            $waktuSekarang = date('d/m/Y H:i');
            $catatanBaru .= "\n[Sistem: Otomatis Ditolak pada $waktuSekarang WIB karena melewati batas tenggat waktu 1 minggu untuk memilih jurusan baru yang masih tersedia kuota]";

            $upd->execute([$catatanBaru, $item['id']]);
            $count++;

            // Kirim notifikasi WA pemberitahuan ditolak + motivasi
            kirim_notifikasi_status_spmb($pdo, $item['id'], 'Ditolak');
        }
    }

    return $count;
}
