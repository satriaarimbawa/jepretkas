# 💳 Sistem Pencatatan Keuangan Terintegrasi Bukti Foto

Aplikasi web manajemen keuangan mandiri yang dibuat dengan **PHP Native** menggunakan arsitektur **MVC (Model-View-Controller)** murni tanpa bantuan Composer. Dilengkapi dengan tampilan gelap premium (dark mode), antarmuka glassmorphism yang modern, fungsionalitas drag-and-drop unggah struk belanja, integrasi kamera (mobile-first), grafik visualisasi, dan ekspor laporan ke format CSV.

---

## 🚀 Fitur Utama
1. **Manajemen Pengguna (Autentikasi):** Register, Login, & Logout aman dengan hashing `password_hash()` bawaan PHP.
2. **Dashboard Dinamis:** Statistik total saldo, ringkasan arus kas masuk (pemasukan) dan keluar (pengeluaran), serta grafik visual interaktif bulanan menggunakan Chart.js.
3. **Pencatatan Transaksi:** Penginputan nominal (terformat Rupiah otomatis), tanggal, kategori, deskripsi, serta kewajiban/opsi mengunggah foto struk/nota belanja.
4. **Manajemen Kategori:** Kustomisasi kategori transaksi lengkap dengan warna representasi grafik.
5. **Laporan & Ekspor:** Riwayat transaksi dengan filter detail (tanggal, kategori, tipe) yang bisa diekspor langsung ke format `.csv` (Excel-compatible).
6. **Desain Premium:** Tampilan gelap modern dengan efek blur kaca (glassmorphism), ikon dari Google Material Icons, dan sepenuhnya responsif di perangkat mobile.

---

## 🛠️ Persyaratan Sistem
- **PHP** versi 7.4 ke atas (direkomendasikan PHP 8.x) dengan ekstensi **PDO** aktif.
- **MySQL / MariaDB**.
- Web Server (**Apache** dengan modul `mod_rewrite` aktif untuk mendukung URL yang bersih).

---

## 📦 Cara Instalasi & Menjalankan Aplikasi

### Langkah 1: Kloning / Letakkan Folder Project
Pastikan folder project `new-project` diletakkan di dalam direktori web server Anda (misalnya di dalam `htdocs` jika Anda menggunakan XAMPP, atau `/var/www/html` jika menggunakan Linux).

### Langkah 2: Setup Database MySQL
1. Buka phpMyAdmin atau MySQL client pilihan Anda.
2. Buat database baru dengan nama `keuangan_db`:
   ```sql
   CREATE DATABASE keuangan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Impor skema tabel yang ada pada file `database/schema.sql` ke dalam database `keuangan_db` tersebut.

### Langkah 3: Konfigurasi Database (Jika Diperlukan)
Jika database Anda menggunakan username, password, host, atau port yang berbeda dari default (Host: `localhost`, User: `root`, Password: `[kosong]`), silakan sesuaikan pengaturannya pada berkas:
👉 **[`config/database.php`](file:///D:/CHACE/percobaan/new-project/config/database.php)**

### Langkah 4: Jalankan Server
- Jika menggunakan **XAMPP / Laragon / WampServer**:
  - Aktifkan modul Apache dan MySQL.
  - Buka browser Anda dan akses alamat `http://localhost/new-project` (atau sesuai dengan path folder Anda).
- Jika menggunakan **Built-in PHP Server** (untuk testing cepat):
  - Jalankan perintah berikut di terminal/PowerShell pada direktori root project:
    ```bash
    php -S localhost:8000 -t public
    ```
  - Buka browser Anda dan akses alamat `http://localhost:8000`

---

## 📂 Struktur Direktori Utama
- `app/` - Logika utama aplikasi (Controllers, Models, Views)
- `config/` - File konfigurasi database dan konstanta global
- `core/` - Kernel/Framework MVC buatan sendiri (App Router, DB wrapper, Base Controller, Base Model)
- `database/` - File SQL skema database
- `public/` - Dokumen web root yang menampung index.php (front-controller), CSS, JS, dan berkas foto struk belanja terunggah.

---

*Dikembangkan untuk memberikan solusi transparansi finansial dan digitalisasi struk fisik.*
