<?php
/**
 * Auto Generator Ikon PWA
 * 
 * Script ini menggambar ikon PWA (192px dan 512px) 
 * dengan background gradien gelap dan simbol dompet menggunakan PHP GD.
 */

function generateIcon($size, $filename) {
    // Buat canvas
    $im = imagecreatetruecolor($size, $size);
    
    // Aktifkan antialiasing
    imagealphablending($im, true);
    imagesavealpha($im, true);

    // Gambar background gradient melingkar (radial-like) / linear
    for ($i = 0; $i < $size; $i++) {
        // Interpolasi warna dari Indigo (#6366f1) ke Cyan (#06b6d4)
        $ratio = $i / $size;
        $r = (int)(99 * (1 - $ratio) + 6 * $ratio);
        $g = (int)(102 * (1 - $ratio) + 182 * $ratio);
        $b = (int)(241 * (1 - $ratio) + 212 * $ratio);
        
        $color = imagecolorallocate($im, $r, $g, $b);
        imageline($im, 0, $i, $size, $i, $color);
    }

    // Tambahkan ikon dompet sederhana di tengah
    // Kita buat gambar dompet dengan garis putih tebal
    $white = imagecolorallocate($im, 255, 255, 255);
    imagesetthickness($im, max(2, $size / 40));

    // Koordinat dompet
    $margin = $size * 0.28;
    $w = $size - (2 * $margin);
    $h = $size * 0.38;
    $x = $margin;
    $y = ($size - $h) / 2;

    // Gambar badan dompet (kotak melengkung)
    imagerectangle($im, (int)$x, (int)$y, (int)($x + $w), (int)($y + $h), $white);
    
    // Gambar penutup lipatan dompet kecil di sisi kanan
    $flapX = $x + $w - ($size * 0.08);
    $flapY = $y + ($h * 0.25);
    $flapW = $size * 0.14;
    $flapH = $h * 0.5;
    imagefilledrectangle($im, (int)$flapX, (int)$flapY, (int)($flapX + $flapW), (int)($flapY + $flapH), $white);
    
    // Gambar kancing dompet kecil
    $dark = imagecolorallocate($im, 99, 102, 241);
    imagefilledellipse($im, (int)($flapX + ($flapW / 2)), (int)($flapY + ($flapH / 2)), (int)($size * 0.04), (int)($size * 0.04), $dark);

    // Simpan gambar
    imagepng($im, $filename);
    imagedestroy($im);
    echo "Ikon $filename ($size x $size) berhasil dibuat.\n";
}

$cssDir = __DIR__;
generateIcon(192, $cssDir . '/logo-192.png');
generateIcon(512, $cssDir . '/logo-512.png');
