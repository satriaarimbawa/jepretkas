# Product Requirements Document (PRD)
## Sistem Pencatatan Keuangan Terintegrasi Bukti Foto

**Nama Proyek:** Sistem Pencatatan Keuangan Terintegrasi Bukti Foto  
**Penulis:** I Komang Gede Satria Arimbawa  
**Tanggal:** 11 Juli 2026  
**Status:** Draf / Perencanaan Awal  

---

### 1. Ringkasan Eksekutif
Sistem ini adalah aplikasi berbasis web yang dirancang untuk mencatat arus kas (pengeluaran dan pemasukan) secara akurat. Nilai tambah utama dari sistem ini adalah kewajiban atau opsi untuk melampirkan bukti foto (struk/nota) pada setiap transaksi pengeluaran. Hal ini bertujuan untuk menciptakan transparansi yang tinggi, meminimalisir transaksi yang tidak tercatat, dan mempermudah proses validasi serta audit keuangan di masa mendatang.

### 2. Tujuan dan Metrik Keberhasilan
*   **Digitalisasi Dokumen:** Mengurangi ketergantungan pada struk kertas fisik yang mudah hilang atau pudar.
*   **Transparansi & Akuntabilitas:** Memastikan setiap nominal yang keluar memiliki bukti fisik yang sah.
*   **Metrik Keberhasilan:** 
    *   Pengguna dapat menginput transaksi dan mengunggah foto dalam waktu kurang dari 1 menit.
    *   Sistem mampu memuat laporan dan galeri struk tanpa kendala waktu tunggu (maksimal *load time* 3 detik).

### 3. Kebutuhan Fungsional (Fitur Utama)

| Nama Fitur | Deskripsi Kebutuhan | Prioritas |
| :--- | :--- | :--- |
| **Manajemen Autentikasi** | Sistem pendaftaran, *login*, dan manajemen sesi pengguna. | Tinggi |
| **Dashboard Utama** | Menampilkan total saldo, grafik pengeluaran bulanan, dan ringkasan transaksi terbaru. | Tinggi |
| **Input Transaksi** | Formulir untuk mencatat nominal, tanggal, kategori, dan deskripsi transaksi. | Tinggi |
| **Integrasi Kamera & File** | Kemampuan mengambil foto langsung melalui perangkat atau mengunggah file gambar dari galeri. | Tinggi |
| **Manajemen Kategori** | Pengguna dapat menambah atau mengedit label kategori (contoh: Operasional, Konsumsi). | Menengah |
| **Laporan & Riwayat** | Tabel riwayat transaksi yang dilengkapi dengan fitur *filter* (berdasarkan tanggal/kategori) dan fungsi pratinjau gambar struk. | Tinggi |

### 4. Kebutuhan Non-Fungsional (Teknologi & Tata Kelola)
*   **Antarmuka & Pengalaman Pengguna (UI/UX):** Perancangan visual akan menggunakan **Figma** dengan menerapkan prinsip *Atomic Design* untuk menjaga konsistensi komponen antar halaman. Antarmuka harus responsif di perangkat seluler (*mobile-first*).
*   **Front-end:** Menggunakan HTML, CSS, dan JavaScript murni atau dikembangkan sebagai *Progressive Web App* (PWA) untuk kelancaran akses kamera.
*   **Back-end:** Dibangun menggunakan **PHP** dengan *framework* **Laravel** untuk menangani *routing*, API, dan logika bisnis.
*   **Manajemen Database:** Menggunakan **MySQL** untuk mengelola data relasional (pengguna, transaksi, dan kategori).
*   **Penyimpanan Aset:** File foto struk akan diunggah dan disimpan ke layanan *cloud* seperti **Firebase Storage** untuk menjaga performa *server* utama.
*   **Keamanan & Auditabilitas:** Validasi ketat pada ekstensi file gambar (hanya JPG, PNG) dan pembatasan ukuran file maksimal (misal: 2MB). Semua transaksi akan memiliki *timestamp* untuk mendukung standar tata kelola dan kemudahan proses audit.

### 5. Rencana Rilis (Milestones)
*   **Fase 1: Desain & Prototyping** (Pembuatan *wireflow* dan *high-fidelity design*).
*   **Fase 2: Persiapan Infrastruktur** (Konfigurasi Laravel, skema MySQL, dan integrasi Firebase).
*   **Fase 3: Pengembangan Fitur Inti** (Modul transaksi dan unggah foto).
*   **Fase 4: Pengujian & Penyempurnaan** (Simulasi input data riil, perbaikan antarmuka, dan optimasi *query* laporan).
