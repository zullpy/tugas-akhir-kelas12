#!/bin/bash

# 1. Jalankan server lokal Anda di port 8000 secara background
# Lepas tanda pagar (#) pada SALAH SATU perintah di bawah sesuai project Anda:

# Jika menggunakan PHP Native / XAMPP:
php -S localhost:8000 &

# Jika menggunakan Laravel:
# php artisan serve --port=8000 &

# Jika menggunakan Python:
# python -m http.server 8000 &


# 2. Tunggu 2 detik agar server utama siap berjalan
sleep 2

# 3. Jalankan Browsersync sebagai Proxy
browser-sync start --proxy "localhost:8000" --files "**/*"
