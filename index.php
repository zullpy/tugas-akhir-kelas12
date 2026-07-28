<?php
$pageTitle = "beranda";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | SMKS SUKAPURA</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>

<?php include 'components/navbar.php'; ?>

<div class="carousel">
    <div class="carousel-inner">
        <div class="carousel-slide active">
            <img src="assets/depan.webp" alt="SMKS Sukapura">
            <div class="carousel-caption">
                <h2>Selamat Datang di SMKS Sukapura</h2>
                <p>Mencetak generasi unggul, kompeten, dan siap kerja di era global.</p>
            </div>
        </div>
        <div class="carousel-slide">
            <img src="assets/bawah.webp" alt="Fasilitas SMKS Sukapura">
            <div class="carousel-caption">
                <h2>Fasilitas Praktik Modern</h2>
                <p>Mendukung pembelajaran interaktif berbasis standar industri terbaru.</p>
            </div>
        </div>
        <div class="carousel-slide">
            <img src="assets/welcome.webp" alt="Kemitraan SMKS Sukapura">
            <div class="carousel-caption">
                <h2>Kemitraan Industri Luas</h2>
                <p>Menjamin peluang magang dan penyerapan kerja lulusan terbaik.</p>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <button class="carousel-control prev" aria-label="Slide Sebelumnya">&#10094;</button>
    <button class="carousel-control next" aria-label="Slide Berikutnya">&#10095;</button>

    <!-- Indicators -->
    <div class="carousel-indicators">
        <span class="indicator active" data-slide="0"></span>
        <span class="indicator" data-slide="1"></span>
        <span class="indicator" data-slide="2"></span>
    </div>
</div>

<main style="width: 92%; max-width: 1200px; margin: 40px auto; min-height: 400px;">
        <div class="rangkuman">
            <p></p>
        </div>

    <section>
        <h1>Sambutan Kepala Sekolah</h1>
        <div class="text-center">
            <img src="assets/kepsek.png" alt="" style="width: auto; height: 300px;">
            <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Veritatis quas error dolores corrupti animi quia suscipit earum architecto, eligendi temporibus! Quia sequi, iure eos laudantium laborum pariatur. Nostrum, aut eius?</p>
        </div>
    </section>

    <div class="mitra">
        <h1>Mitra Kerja Sama</h1>
        <div>
            <img src="assets/mitra/abl.webp" alt="">
            <img src="assets/mitra/bni.webp" alt="">
            <img src="assets/mitra/btn.webp" alt="">
            <img src="assets/mitra/byu.webp" alt="">
            <img src="assets/mitra/pnm.webp" alt="">
            <img src="assets/mitra/prilude.webp" alt="">
            <img src="assets/mitra/rabbani.webp" alt="">
            <img src="assets/mitra/suzuki.webp" alt="">
            <img src="assets/mitra/telkom.webp" alt="">
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>