<?php
/**
 * Local FTP Deployment Script
 * 
 * Script ini digunakan untuk mengunggah proyek secara rekursif ke server
 * FTP hosting (seperti InfinityFree) berdasarkan konfigurasi pada deploy.config.json.
 */

$configFile = __DIR__ . '/deploy.config.json';

if (!file_exists($configFile)) {
    echo "========================================================\n";
    echo "ERROR: Berkas 'deploy.config.json' tidak ditemukan!\n";
    echo "========================================================\n";
    echo "Silakan salin 'deploy.config.json.example' menjadi\n";
    echo "'deploy.config.json', kemudian isi kredensial FTP Anda.\n";
    echo "========================================================\n";
    exit(1);
}

$config = json_decode(file_get_contents($configFile), true);

if (!$config || empty($config['host']) || empty($config['username']) || empty($config['password'])) {
    echo "ERROR: Konfigurasi deploy.config.json tidak valid atau tidak lengkap.\n";
    exit(1);
}

$host = $config['host'];
$port = isset($config['port']) ? (int)$config['port'] : 21;
$username = $config['username'];
$password = $config['password'];
$remoteDir = isset($config['remote_dir']) ? rtrim($config['remote_dir'], '/') : '/htdocs';

echo "Menghubungkan ke server FTP $host:$port...\n";
$conn = ftp_connect($host, $port, 90);

if (!$conn) {
    echo "ERROR: Gagal terhubung ke server FTP $host.\n";
    exit(1);
}

echo "Mencoba masuk sebagai $username...\n";
if (!@ftp_login($conn, $username, $password)) {
    echo "ERROR: Login FTP gagal! Periksa username/password Anda.\n";
    ftp_close($conn);
    exit(1);
}

// Aktifkan mode pasif (sangat disarankan untuk shared hosting / cloud)
echo "Mengaktifkan mode pasif (passive mode)...\n";
ftp_pasv($conn, true);

// Pindah ke folder remote tujuan
echo "Pindah ke direktori tujuan: $remoteDir...\n";
if (!@ftp_chdir($conn, $remoteDir)) {
    echo "ERROR: Tidak dapat membuka folder remote: $remoteDir.\n";
    ftp_close($conn);
    exit(1);
}

// Daftar berkas/folder yang tidak boleh diunggah
$excludeList = [
    '.git',
    '.gitignore',
    '.github',
    'deploy.config.json',
    'deploy.config.json.example',
    'deploy.php',
    'database/keuangan.sqlite',
    '.system_generated',
    '.gemini'
];

/**
 * Fungsi rekursif untuk mengunggah file
 */
function uploadFolder($conn, $localPath, $remotePath, $excludes) {
    $dir = opendir($localPath);
    if (!$dir) return;

    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $localFilePath = $localPath . '/' . $file;
        $remoteFilePath = empty($remotePath) ? $file : $remotePath . '/' . $file;

        // Cek apakah ada dalam daftar pengecualian
        $isExcluded = false;
        foreach ($excludes as $exc) {
            // Cek kecocokan path relatif
            if ($exc === $file || strpos($remoteFilePath, $exc) === 0) {
                $isExcluded = true;
                break;
            }
        }

        if ($isExcluded) {
            echo "   [DIABAIKAN] $remoteFilePath\n";
            continue;
        }

        if (is_dir($localFilePath)) {
            // Buat folder di server jika belum ada
            if (!@ftp_chdir($conn, $remoteFilePath)) {
                if (@ftp_mkdir($conn, $remoteFilePath)) {
                    echo "📁 Membuat folder: $remoteFilePath...\n";
                } else {
                    echo "⚠️ Gagal membuat folder: $remoteFilePath\n";
                    continue;
                }
            }
            // Kembalikan ke folder sebelumnya
            ftp_chdir($conn, '..');
            
            // Rekursi untuk folder anak
            uploadFolder($conn, $localFilePath, $remoteFilePath, $excludes);
        } else {
            // Unggah berkas
            echo "📤 Mengunggah: $remoteFilePath...";
            $upload = ftp_put($conn, $remoteFilePath, $localFilePath, FTP_BINARY);
            if ($upload) {
                echo " [SUKSES]\n";
            } else {
                echo " [GAGAL]\n";
            }
        }
    }
    closedir($dir);
}

echo "\n--- MEMULAI PROSES UNGGAH ---\n";
uploadFolder($conn, __DIR__, '', $excludeList);
echo "--- PROSES UNGGAH SELESAI ---\n";

ftp_close($conn);
echo "\nDeployment sukses! Proyek Anda telah diunggah ke server InfinityFree.\n";
