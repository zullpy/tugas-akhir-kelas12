<?php
// spmb/admin/export.php
// Ekspor Data Pendaftar SPMB ke Excel / CSV
require_once __DIR__ . '/../config.php';
check_admin_login();

$pdo = get_db_connection();

$qStatus  = sanitize_input($_GET['status'] ?? '');
$qJurusan = sanitize_input($_GET['jurusan'] ?? '');
$qJalur   = sanitize_input($_GET['jalur'] ?? '');
$qSearch  = sanitize_input($_GET['q'] ?? '');
$format   = strtolower(sanitize_input($_GET['format'] ?? 'excel'));

$sql = "SELECT * FROM `spmb_pendaftar` WHERE 1=1";
$params = [];

if (!empty($qStatus)) {
    $sql .= " AND `status` = ?";
    $params[] = $qStatus;
}
if (!empty($qJurusan)) {
    $sql .= " AND (`jurusan_1` = ? OR `jurusan_2` = ?)";
    $params[] = $qJurusan;
    $params[] = $qJurusan;
}
if (!empty($qJalur)) {
    $sql .= " AND `jalur` = ?";
    $params[] = $qJalur;
}
if (!empty($qSearch)) {
    $sql .= " AND (`no_pendaftaran` LIKE ? OR `nisn` LIKE ? OR `nama_lengkap` LIKE ? OR `asal_sekolah` LIKE ?)";
    $term = "%$qSearch%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY `id` ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$headers = [
    'No',
    'No. Pendaftaran',
    'NISN',
    'NIK',
    'Nama Lengkap',
    'Jenis Kelamin',
    'Tempat Lahir',
    'Tanggal Lahir',
    'No. HP Calon Siswa',
    'Asal SMP/MTs',
    'Alamat Lengkap',
    'Jurusan Pilihan 1',
    'Jurusan Pilihan 2',
    'Jurusan Diterima',
    'Jalur Pendaftaran',
    'Berkas KK',
    'Berkas Akta',
    'Berkas Ijazah/SKL',
    'Berkas KTP Ortu',
    'Berkas KIP',
    'Berkas Sertifikat',
    'Nama Ayah',
    'Nama Ibu',
    'Pekerjaan Orang Tua',
    'No. HP Orang Tua',
    'Penghasilan Orang Tua',
    'Status Seleksi',
    'Jadwal Tes',
    'Ruang Tes',
    'Catatan Panitia',
    'Waktu Mendaftar'
];

// Opsi ekspor ke CSV murni jika format=csv
if ($format === 'csv') {
    $filename = 'SPMB_SMKS_SUKAPURA_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    $output = fopen('php://output', 'w');
    fputcsv($output, $headers, ';');
    $no = 1;
    foreach ($rows as $r) {
        fputcsv($output, [
            $no++,
            $r['no_pendaftaran'],
            "'" . $r['nisn'],
            "'" . $r['nik'],
            $r['nama_lengkap'],
            $r['jenis_kelamin'],
            $r['tempat_lahir'],
            $r['tanggal_lahir'],
            "'" . $r['no_hp'],
            $r['asal_sekolah'],
            str_replace(["\r", "\n"], ' ', $r['alamat']),
            $r['jurusan_1'],
            $r['jurusan_2'],
            $r['jurusan_diterima'] ?: '-',
            $r['jalur'],
            $r['berkas_kk'] ?: '-',
            $r['berkas_akta'] ?: '-',
            $r['berkas_ijazah'] ?: '-',
            $r['berkas_ktp_ortu'] ?: '-',
            $r['berkas_kip'] ?: '-',
            $r['berkas_prestasi'] ?: '-',
            $r['nama_ayah'],
            $r['nama_ibu'],
            $r['pekerjaan_ortu'],
            "'" . $r['no_hp_ortu'],
            $r['penghasilan_ortu'],
            $r['status'],
            $r['jadwal_tes'],
            $r['ruang_tes'],
            str_replace(["\r", "\n"], ' ', $r['catatan_admin']),
            $r['tanggal_daftar']
        ], ';');
    }
    fclose($output);
    exit;
}

// Default: Ekspor ke Excel (.xls) dengan Header Bold & Ukuran Font 13pt
$filename = 'SPMB_SMKS_SUKAPURA_' . date('Y-m-d_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Data Pendaftar SPMB</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
echo '<style>
    body, table {
        font-family: Calibri, Arial, sans-serif;
    }
    th {
        font-family: Calibri, Arial, sans-serif;
        font-size: 13pt !important;
        font-weight: bold !important;
        background-color: #E2E8F0;
        color: #000000;
        border: 1px solid #000000;
        text-align: center;
        vertical-align: middle;
        height: 36px;
        padding: 8px 12px;
        white-space: nowrap;
    }
    td {
        font-family: Calibri, Arial, sans-serif;
        font-size: 11pt;
        border: 1px solid #CBD5E1;
        vertical-align: middle;
        padding: 6px 10px;
    }
    .str {
        mso-number-format: "\@";
    }
    .center {
        text-align: center;
    }
</style>';
echo '</head>';
echo '<body>';
echo '<table border="1">';
echo '<thead>';
echo '<tr style="height: 36px;">';
foreach ($headers as $h) {
    echo '<th style="font-family: Calibri, Arial, sans-serif; font-size: 13pt; font-weight: bold; background-color: #E2E8F0; color: #000000; border: 1px solid #000000; text-align: center; vertical-align: middle; height: 36px; padding: 8px 12px; white-space: nowrap;">' . htmlspecialchars($h) . '</th>';
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$no = 1;
foreach ($rows as $r) {
    echo '<tr>';
    echo '<td class="center" style="text-align: center;">' . $no++ . '</td>';
    echo '<td class="str center" style="mso-number-format:\'\@\'; text-align: center;">' . htmlspecialchars($r['no_pendaftaran']) . '</td>';
    echo '<td class="str center" style="mso-number-format:\'\@\'; text-align: center;">' . htmlspecialchars($r['nisn']) . '</td>';
    echo '<td class="str center" style="mso-number-format:\'\@\'; text-align: center;">' . htmlspecialchars($r['nik']) . '</td>';
    echo '<td>' . htmlspecialchars($r['nama_lengkap']) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['jenis_kelamin']) . '</td>';
    echo '<td>' . htmlspecialchars($r['tempat_lahir']) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['tanggal_lahir']) . '</td>';
    echo '<td class="str center" style="mso-number-format:\'\@\'; text-align: center;">' . htmlspecialchars($r['no_hp']) . '</td>';
    echo '<td>' . htmlspecialchars($r['asal_sekolah']) . '</td>';
    echo '<td>' . htmlspecialchars(str_replace(["\r", "\n"], ' ', $r['alamat'])) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['jurusan_1']) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['jurusan_2']) . '</td>';
    echo '<td class="center" style="text-align: center; font-weight: bold;">' . htmlspecialchars($r['jurusan_diterima'] ?: '-') . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['jalur']) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['berkas_kk'] ?: '-') . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['berkas_akta'] ?: '-') . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['berkas_ijazah'] ?: '-') . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['berkas_ktp_ortu'] ?: '-') . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['berkas_kip'] ?: '-') . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['berkas_prestasi'] ?: '-') . '</td>';
    echo '<td>' . htmlspecialchars($r['nama_ayah']) . '</td>';
    echo '<td>' . htmlspecialchars($r['nama_ibu']) . '</td>';
    echo '<td>' . htmlspecialchars($r['pekerjaan_ortu']) . '</td>';
    echo '<td class="str center" style="mso-number-format:\'\@\'; text-align: center;">' . htmlspecialchars($r['no_hp_ortu']) . '</td>';
    echo '<td>' . htmlspecialchars($r['penghasilan_ortu']) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['status']) . '</td>';
    echo '<td>' . htmlspecialchars($r['jadwal_tes']) . '</td>';
    echo '<td>' . htmlspecialchars($r['ruang_tes']) . '</td>';
    echo '<td>' . htmlspecialchars(str_replace(["\r", "\n"], ' ', $r['catatan_admin'])) . '</td>';
    echo '<td class="center" style="text-align: center;">' . htmlspecialchars($r['tanggal_daftar']) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</body>';
echo '</html>';
exit;
