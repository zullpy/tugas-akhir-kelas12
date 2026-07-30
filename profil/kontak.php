<?php
$base_url = '../';
$pageTitle = 'profil';
$subPageTitle = 'kontak';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak | SMKS SUKAPURA</title>
    <meta name="description" content="Kontak SMKS SUKAPURA Kab. Tasikmalaya - Informasi kontak, lokasi, dan formulir pesan.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/fill/style.css"/>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="<?php echo $base_url; ?>assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <header class="profile-hero">
        <img src="../assets/bawah.webp" alt="Banner Kontak">
        <div class="background-overlay"></div>
        <div class="hero-title-overlay">
            <div class="hero-badge"><i class="ph-bold ph-phone"></i> PROFIL SEKOLAH</div>
            <h1 class="hero-heading">Kontak Kami</h1>
            <p class="hero-sub">SMKS Sukapura Kab. Tasikmalaya</p>
        </div>
    </header>

    <main class="kontak-main">

        <!-- ── SECTION HEADER ── -->
        <div class="kontak-section-header reveal">
            <div class="kontak-badge"><i class="ph-bold ph-address-book"></i> INFORMASI KONTAK</div>
            <h2 class="kontak-section-title">Hubungi Kami</h2>
            <p class="kontak-section-sub">Jangan ragu untuk menghubungi kami melalui salah satu saluran di bawah ini.</p>
        </div>

        <!-- ── INFO CARDS ── -->
        <div class="kontak-cards-grid">

            <div class="kontak-info-card card-yellow reveal reveal-delay-1">
                <div class="kontak-info-icon"><i class="ph-bold ph-map-pin-area"></i></div>
                <div class="kontak-info-body">
                    <span class="kontak-info-label">Alamat</span>
                    <p class="kontak-info-value">Jl. Dalem Wirawangsa Km. 03 Cikalapa,<br>Kec. Tanjungjaya, Kab. Tasikmalaya</p>
                </div>
            </div>

            <div class="kontak-info-card card-blue reveal reveal-delay-2">
                <div class="kontak-info-icon"><i class="ph-bold ph-envelope-simple"></i></div>
                <div class="kontak-info-body">
                    <span class="kontak-info-label">Email</span>
                    <a href="mailto:smksukapurakabtasikmalaya@gmail.com" class="kontak-info-value kontak-link">
                        smksukapurakabtasikmalaya@gmail.com
                    </a>
                </div>
            </div>

            <div class="kontak-info-card card-green reveal reveal-delay-3">
                <div class="kontak-info-icon"><i class="ph-bold ph-phone"></i></div>
                <div class="kontak-info-body">
                    <span class="kontak-info-label">Telepon / WhatsApp</span>
                    <a href="https://wa.me/6282119277417" target="_blank" class="kontak-info-value kontak-link">
                        0821-1927-7417
                    </a>
                </div>
            </div>

            <div class="kontak-info-card card-pink reveal reveal-delay-4">
                <div class="kontak-info-icon"><i class="ph-bold ph-clock"></i></div>
                <div class="kontak-info-body">
                    <span class="kontak-info-label">Jam Operasional</span>
                    <div class="kontak-info-value">
                        <div class="jam-row"><span>Senin – Kamis</span><span>06.30 – 15.00</span></div>
                        <div class="jam-row"><span>Jum'at</span><span>06.30 – 11.00</span></div>
                        <div class="jam-row jam-libur"><span>Sabtu – Minggu</span><span>Libur</span></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── SOCIAL MEDIA ── -->
        <div class="kontak-sosmed-wrap reveal">
            <span class="kontak-sosmed-label">Temukan kami di media sosial:</span>
            <div class="kontak-sosmed-btns">
                <a href="https://www.youtube.com/@smksukapurakab.tasikmalaya" target="_blank" aria-label="YouTube" class="sosmed-btn sosmed-yt">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    YouTube
                </a>
                <a href="https://www.instagram.com/smksukapurakab.tasikmalaya/" target="_blank" aria-label="Instagram" class="sosmed-btn sosmed-ig">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    Instagram
                </a>
                <a href="https://www.facebook.com/people/Smks-Sukapura-Kab-Tasikmalaya/61555166803090/" target="_blank" aria-label="Facebook" class="sosmed-btn sosmed-fb">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </a>
                <a href="https://www.tiktok.com/@smksukapurakabtas" target="_blank" aria-label="TikTok" class="sosmed-btn sosmed-tt">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.82.57-1.32 1.55-1.32 2.55 0 1.25.72 2.41 1.83 2.97.98.49 2.15.53 3.16.14 1.05-.4 1.84-1.29 2.12-2.37.15-.56.17-1.14.17-1.71V0z"/></svg>
                    TikTok
                </a>
            </div>
        </div>

        <!-- ── MAPS SECTION ── -->
        <div class="kontak-maps-section reveal">
            <div class="kontak-badge"><i class="ph-bold ph-map-trifold"></i> LOKASI KAMI</div>
            <h2 class="kontak-section-title">Temukan Kami di Peta</h2>
            <p class="kontak-section-sub">SMKS Sukapura — Jl. Dalem Wirawangsa Km. 03, Tanjungjaya, Kab. Tasikmalaya</p>
            <div class="kontak-maps-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5131.429384377086!2d108.11760227603685!3d-7.380232392629273!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f55c08833af51%3A0x3ddfa99678b1d4a4!2sSMK%20Sukapura!5e1!3m2!1sen!2sid!4v1785290545610!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
        </div>

        <!-- ── FORM PESAN ── -->
        <div class="kontak-form-section reveal">
            <div class="kontak-badge"><i class="ph-bold ph-paper-plane-tilt"></i> KIRIM PESAN</div>
            <h2 class="kontak-section-title">Kirimkan Pesan Anda</h2>
            <p class="kontak-section-sub">Kesan dan masukan Anda membantu kami terus meningkatkan kualitas pelayanan sekolah.</p>

            <!-- Notifikasi status pengiriman -->
            <div id="form-notif" class="form-notif" style="display:none;" role="alert"></div>

            <form class="kontak-form" id="kontak-form" action="kirim-pesan.php" method="POST" novalidate>
                <div class="kontak-form-row">
                    <div class="kontak-form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input id="nama" type="text" name="nama" placeholder="Masukkan nama lengkap Anda" required>
                    </div>
                    <div class="kontak-form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" placeholder="contoh@email.com" required>
                    </div>
                </div>
                <div class="kontak-form-group">
                    <label for="pesan">Pesan</label>
                    <textarea id="pesan" name="pesan" rows="5" placeholder="Tuliskan pesan, pertanyaan, atau masukan Anda di sini..." required></textarea>
                </div>
                <button type="submit" class="kontak-submit-btn" id="submit-btn">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Kirim Pesan
                </button>
            </form>
        </div>

    </main>

    <?php include '../components/footer.php'; ?>
    <script src="script.js"></script>
    <script>
    (function () {
        const form    = document.getElementById('kontak-form');
        const btn     = document.getElementById('submit-btn');
        const notif   = document.getElementById('form-notif');

        function showNotif(success, msg) {
            notif.textContent = msg;
            notif.className   = 'form-notif ' + (success ? 'notif-sukses' : 'notif-gagal');
            notif.style.display = 'block';
            notif.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            setTimeout(() => { notif.style.display = 'none'; }, 6000);
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Loading state
            btn.disabled   = true;
            btn.innerHTML  = '<i class="ph-bold ph-circle-notch" style="animation:spin 0.8s linear infinite"></i> Mengirim...';

            try {
                const res = await fetch('kirim-pesan.php', {
                    method: 'POST',
                    body:   new FormData(form)
                });

                let data;
                try {
                    data = await res.json();
                } catch (jsonErr) {
                    const rawText = await res.text().catch(() => '');
                    console.error('Server non-JSON response:', rawText);
                    data = {
                        success: false,
                        message: `Server mengembalikan respon tidak valid (Status ${res.status}).`
                    };
                }

                showNotif(data.success, data.message || (data.success ? 'Pesan berhasil dikirim!' : 'Gagal mengirim pesan.'));
                if (data.success) form.reset();
            } catch (err) {
                console.error('Fetch error:', err);
                showNotif(false, 'Gagal terhubung ke server. Silakan periksa koneksi Anda dan coba beberapa saat lagi.');
            } finally {
                btn.disabled  = false;
                btn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> Kirim Pesan';
            }
        });
    })();
    </script>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .form-notif {
            padding: 14px 18px;
            border: 2.5px solid #000;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 18px;
            box-shadow: 4px 4px 0 #000;
        }
        .notif-sukses { background-color: #DCFCE7; color: #166534; }
        .notif-gagal  { background-color: #FEE2E2; color: #991B1B; }
    </style>
</body>
</html>