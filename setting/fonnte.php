<?php
// setting/fonnte.php
// Pengaturan Integrasi WhatsApp Gateway (Fonnte API) SMKS SUKAPURA

require_once __DIR__ . '/../spmb/config.php';
require_once __DIR__ . '/../spmb/fonnte.php';
check_admin_login(true, '../spmb/admin/login.php');

$pdo      = get_db_connection();
$settings = get_all_settings();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'fonnte') {
        $token  = trim($_POST['fonnte_token'] ?? '');
        $status = isset($_POST['fonnte_status']) ? '1' : '0';

        $ins = $pdo->prepare("INSERT INTO `spmb_pengaturan` (`kunci`, `nilai`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `nilai` = VALUES(`nilai`)");
        $ins->execute(['fonnte_token', $token]);
        $ins->execute(['fonnte_status', $status]);

        $settings = get_all_settings();
        $success = "Pengaturan integrasi WhatsApp Gateway (Fonnte) berhasil disimpan!";

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $success]);
            exit;
        }
    } elseif ($action === 'test_wa') {
        $noTes = trim($_POST['no_test'] ?? '');
        if (empty($noTes)) {
            $error = "Nomor WhatsApp untuk uji coba wajib diisi.";
        } else {
            $cfg = get_fonnte_config();
            $linkContoh = $cfg['base_url'] . '/spmb/cetak.php?no=REG-CONTOH-1234';
            $pesanTes = "🔔 *UJI COBA WHATSAPP GATEWAY (FONNTE)*\n\n"
                      . "Halo Administrator SMKS Sukapura,\n"
                      . "Ini adalah pesan verifikasi koneksi API Fonnte dari Portal SPMB SMKS Sukapura.\n\n"
                      . "✅ *Status: KONEKSI BERHASIL!*\n"
                      . "🌐 *Base URL Otomatis:* {$cfg['base_url']}\n"
                      . "📄 *Contoh Link Cetak:* {$linkContoh}\n"
                      . "⏰ *Waktu Kirim:* " . date('d/m/Y H:i:s') . " WIB\n\n"
                      . "Sistem notifikasi otomatis SPMB siap digunakan.";
            
            $res = kirim_wa_fonnte($noTes, $pesanTes);
            if ($res['success']) {
                $success = "Uji coba kirim pesan WhatsApp ke nomor <strong>" . htmlspecialchars($noTes) . "</strong> BERHASIL!";
            } else {
                $error = "Uji coba WhatsApp GAGAL: " . htmlspecialchars($res['error'] ?? 'Terjadi kesalahan saat menghubungi API Fonnte');
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => !empty($success),
                    'message' => !empty($success) ? $success : $error
                ]);
                exit;
            }
        }
    }
}

$cfg = get_fonnte_config();
$isFonnteAktif = (!empty($settings['fonnte_token']) && ($settings['fonnte_status'] ?? '1') === '1');

$adminPageTitle   = 'Pengaturan WhatsApp Fonnte';
$adminPageHeading = 'Integrasi WhatsApp Gateway (Fonnte)';
require_once __DIR__ . '/../spmb/admin/header.php';
?>

<?php $activeTab = 'fonnte.php'; require_once __DIR__ . '/tabs.php'; ?>

<?php if (!empty($success)): ?>
    <div class="spmb-alert spmb-alert-success">
        <i class="ph-bold ph-check-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $success; ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="spmb-alert spmb-alert-danger">
        <i class="ph-bold ph-warning-circle" style="font-size:1.5rem;"></i>
        <div><?php echo $error; ?></div>
    </div>
<?php endif; ?>

<div style="display:grid; grid-template-columns:1.3fr 1fr; gap:24px;">

    <!-- KOLOM KIRI: KONFIGURASI UTAMA FONNTE -->
    <div>
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-whatsapp-logo" style="color:#22C55E;"></i> Konfigurasi Akun Fonnte API
                </h3>
                <span class="spmb-badge" style="background:<?php echo $isFonnteAktif ? '#DCFCE7' : '#FEE2E2'; ?>; color:<?php echo $isFonnteAktif ? '#166534' : '#991B1B'; ?>; font-weight:800; font-size:0.75rem; border:1px solid currentColor;">
                    <i class="ph-bold <?php echo $isFonnteAktif ? 'ph-check-circle' : 'ph-x-circle'; ?>"></i>
                    <?php echo $isFonnteAktif ? 'TERHUBUNG' : 'BELUM AKTIF'; ?>
                </span>
            </div>
            <div class="adm-card-body">
                <p style="font-size:0.88rem; color:#64748B; margin-bottom:18px; line-height:1.6;">
                    Notifikasi otomatis akan dikirimkan langsung ke nomor WhatsApp calon siswa saat admin memperbarui hasil seleksi / verifikasi (Diterima + link cetak kartu, Cadangan, Perlu Perbaikan berkas/kuota penuh, dan Ditolak + motivasi).
                </p>

                <form id="form-fonnte" action="fonnte.php" method="POST">
                    <input type="hidden" name="action" value="fonnte">

                    <div class="spmb-form-group">
                        <label class="spmb-label">
                            Token API Fonnte <span class="required">*</span>
                        </label>
                        <input type="text" name="fonnte_token" class="spmb-input" placeholder="Masukkan token API akun Fonnte Anda" value="<?php echo htmlspecialchars($settings['fonnte_token'] ?? ''); ?>" required style="font-family:monospace; font-size:0.95rem; font-weight:700;">
                        <span style="font-size:0.75rem; color:#64748B; margin-top:4px; display:block;">
                            Dapatkan token API pada menu Device di dashboard <a href="https://fonnte.com" target="_blank" style="color:#2563EB; font-weight:700; text-decoration:underline;">Fonnte.com</a>.
                        </span>
                    </div>

                    <div class="spmb-form-group">
                        <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:0.9rem; font-weight:700; color:#1E293B;">
                            <input type="checkbox" name="fonnte_status" value="1" <?php echo (($settings['fonnte_status'] ?? '1') === '1') ? 'checked' : ''; ?> style="width:20px; height:20px; accent-color:#16A34A;">
                            <span>Aktifkan Pengiriman WhatsApp Otomatis</span>
                        </label>
                    </div>

                    <!-- BANNER DOMAIN & LINK CETAK OTOMATIS -->
                    <div style="background:#F0FDF4; border:1.5px solid #86EFAC; border-radius:10px; padding:14px; margin-bottom:20px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:0.85rem; font-weight:800; color:#166534; display:flex; align-items:center; gap:6px;">
                                <i class="ph-bold ph-magic-wand" style="font-size:1.1rem;"></i> Base URL &amp; Link Cetak Otomatis
                            </span>
                            <span style="background:#DCFCE7; color:#166534; font-size:0.7rem; font-weight:800; padding:2px 8px; border-radius:999px; border:1px solid #16A34A;">100% OTOMATIS</span>
                        </div>
                        <div style="font-size:0.82rem; color:#14532D; line-height:1.5; margin-bottom:10px;">
                            Anda <strong>tidak perlu mengatur domain secara manual</strong>. Sistem secara otomatis mendeteksi server hosting:
                        </div>
                        <div style="background:#FFFFFF; border:1px solid #BBF7D0; border-radius:6px; padding:8px 12px; font-family:monospace; font-size:0.85rem; color:#0F172A; font-weight:700;">
                            🌐 Domain Terdeteksi: <span style="color:#2563EB;"><?php echo htmlspecialchars($cfg['base_url']); ?></span>
                        </div>
                        <div style="font-size:0.75rem; color:#15803D; margin-top:8px;">
                            ✓ Saat website aktif di hosting (<strong>smksukapura.miw.web.id</strong>), link unduh kartu dan perbaikan berkas di WhatsApp otomatis mengarah ke <code>https://smksukapura.miw.web.id</code>.
                        </div>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-primary" style="width:100%; padding:12px; font-size:0.95rem;">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Pengaturan Fonnte
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: UJI COBA PESAN & PREVIEW TEMPLATE -->
    <div>
        <!-- FORM UJI COBA PESAN -->
        <div class="adm-card" style="margin-bottom:24px;">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-paper-plane-tilt" style="color:#0284C7;"></i> Uji Coba Pengiriman Pesan
                </h3>
            </div>
            <div class="adm-card-body">
                <p style="font-size:0.82rem; color:#64748B; margin-bottom:14px; line-height:1.5;">
                    Kirim pesan tes ke nomor WhatsApp Anda untuk memastikan token API Fonnte dan koneksi server bekerja normal.
                </p>

                <form id="form-test-wa" action="fonnte.php" method="POST">
                    <input type="hidden" name="action" value="test_wa">

                    <div class="spmb-form-group">
                        <label class="spmb-label">Nomor WhatsApp Penerima Tes</label>
                        <input type="text" name="no_test" class="spmb-input" placeholder="08xxxxxxxxxx" required value="<?php echo htmlspecialchars($settings['hotline_wa'] ?? ''); ?>" style="font-weight:700;">
                        <span style="font-size:0.75rem; color:#64748B; margin-top:4px; display:block;">Format: 08xxx atau 628xxx</span>
                    </div>

                    <button type="submit" class="adm-btn adm-btn-success" style="width:100%; padding:11px; font-size:0.9rem;">
                        <i class="ph-bold ph-paper-plane-right"></i> Kirim Pesan Uji Coba
                    </button>
                </form>
            </div>
        </div>

        <!-- INFO TEMPLATE PESAN OTOMATIS -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title">
                    <i class="ph-bold ph-chat-circle-text"></i> Trigger Pesan Otomatis
                </h3>
            </div>
            <div class="adm-card-body" style="font-size:0.83rem; color:#475569; line-height:1.6;">
                <ul style="padding-left:18px; margin:0; display:flex; flex-direction:column; gap:10px;">
                    <li>
                        <strong style="color:#166534;">🎉 Status Diterima:</strong>
                        <div>Mengirim pengumuman kelulusan, jadwal tes, serta lampiran tautan unduh Kartu Peserta resmi.</div>
                    </li>
                    <li>
                        <strong style="color:#B45309;">⚠️ Status Perlu Perbaikan:</strong>
                        <div>Mengirim rincian catatan revisi dari panitia serta link langsung untuk koreksi formulir.</div>
                    </li>
                    <li>
                        <strong style="color:#0284C7;">📋 Status Cadangan:</strong>
                        <div>Pemberitahuan bahwa kuota utama penuh dan calon siswa masuk ke dalam daftar prioritas cadangan.</div>
                    </li>
                    <li>
                        <strong style="color:#DC2626;">❌ Status Ditolak:</strong>
                        <div>Pemberitahuan hasil seleksi disertai ucapan terima kasih dan kalimat motivasi positif.</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>

<script>
// AJAX form submit handler untuk UX yang mulus tanpa reload
const formFonnte = document.getElementById('form-fonnte');
if (formFonnte) {
    formFonnte.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = formFonnte.querySelector('button[type="submit"]');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner" style="animation:spin 1s linear infinite"></i> Menyimpan...'; }

        fetch('fonnte.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(formFonnte)
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            formFonnte.submit();
        });
    });
}

const formTestWa = document.getElementById('form-test-wa');
if (formTestWa) {
    formTestWa.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = formTestWa.querySelector('button[type="submit"]');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner" style="animation:spin 1s linear infinite"></i> Mengirim WA...'; }

        fetch('fonnte.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(formTestWa)
        })
        .then(r => r.json())
        .then(data => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Terkirim!', html: data.message });
            } else {
                Swal.fire({ icon: 'error', title: 'Pengiriman Gagal', html: data.message });
            }
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            formTestWa.submit();
        });
    });
}
</script>

<?php require_once __DIR__ . '/../spmb/admin/footer.php'; ?>
