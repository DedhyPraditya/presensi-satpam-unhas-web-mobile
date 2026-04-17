<?php
/**
 * SCRIPT PERBAIKAN DATABASE (ONE-TIME USE)
 * ---------------------------------------
 * Script ini digunakan untuk menambahkan kolom yang kurang pada database aaPanel
 * tanpa menghapus data yang sudah ada.
 */

// Sertakan file koneksi yang sudah ada
require 'koneksi.php';

// Pastikan koneksi berhasil
if ($koneksi->connect_error) {
    die("<div style='color:red; font-family:sans-serif;'>
            <h3>❌ Koneksi Gagal!</h3>
            <p>Pastikan file <b>koneksi.php</b> sudah diatur untuk database aaPanel.</p>
         </div>");
}

echo "<div style='font-family:sans-serif; padding:20px; border:1px solid #ccc; border-radius:10px; max-width:800px; margin:20px auto; background:#f9f9f9;'>";
echo "<h2 style='color:#1e3a8a;'>🛠 Perbaikan Database Satpam UNHAS</h2>";
echo "<p>Menjalankan query pembaruan struktur...</p><hr>";

$queries = [
    // 1. Tambah kolom di tabel absensi
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS jenis_kerja ENUM('non_shift_pagi','shift_pagi','shift_malam') NOT NULL DEFAULT 'non_shift_pagi' AFTER ceklog_pulang",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS terlambat VARCHAR(10) DEFAULT NULL AFTER jenis_kerja",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS cepat_pulang VARCHAR(10) DEFAULT NULL AFTER terlambat",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS latitude DOUBLE DEFAULT NULL AFTER cepat_pulang",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS longitude DOUBLE DEFAULT NULL AFTER latitude",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS foto_masuk VARCHAR(255) DEFAULT NULL AFTER longitude",
    "ALTER TABLE absensi ADD COLUMN IF NOT EXISTS foto_pulang VARCHAR(255) DEFAULT NULL AFTER foto_masuk",
    
    // 2. Buat tabel laporan jika belum ada
    "CREATE TABLE IF NOT EXISTS laporan (
      id int(11) NOT NULL AUTO_INCREMENT,
      user_id int(11) NOT NULL,
      tanggal date NOT NULL,
      jam time NOT NULL,
      deskripsi text NOT NULL,
      foto varchar(255) DEFAULT NULL,
      latitude double DEFAULT NULL,
      longitude double DEFAULT NULL,
      created_at timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (id),
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    // 3. Pastikan kolom tambahan di laporan juga ada (antisipasi versi lama)
    "ALTER TABLE laporan ADD COLUMN IF NOT EXISTS foto VARCHAR(255) DEFAULT NULL AFTER deskripsi",
    "ALTER TABLE laporan ADD COLUMN IF NOT EXISTS latitude DOUBLE DEFAULT NULL AFTER foto",
    "ALTER TABLE laporan ADD COLUMN IF NOT EXISTS longitude DOUBLE DEFAULT NULL AFTER latitude"
];

$success_count = 0;
$error_count = 0;

foreach ($queries as $index => $q) {
    echo "<div style='margin-bottom:10px; padding:10px; background:#fff; border-left:4px solid #3b82f6;'>";
    echo "<small style='color:#666;'>Query #" . ($index + 1) . ":</small><br><code style='display:block; background:#f1f5f9; padding:5px; margin:5px 0; word-break:break-all;'>$q</code>";
    
    // Gunakan try-catch atau cek manual agar tidak fatal error
    try {
        if ($koneksi->query($q)) {
            echo "<span style='color:green; font-weight:bold;'>✅ Berhasil / Sudah Ada</span>";
            $success_count++;
        } else {
            echo "<span style='color:orange; font-weight:bold;'>⚠ Dilewati: " . $koneksi->error . "</span>";
        }
    } catch (Exception $e) {
        echo "<span style='color:red; font-weight:bold;'>❌ Gagal: " . $e->getMessage() . "</span>";
        $error_count++;
    }
    echo "</div>";
}

echo "<hr>";
echo "<h3>Ringkasan:</h3>";
echo "<p>Berhasil: <span style='color:green; font-weight:bold;'>$success_count</span></p>";
echo "<p>Gagal: <span style='color:red; font-weight:bold;'>$error_count</span></p>";

if ($error_count == 0) {
    echo "<div style='background:#dcfce7; color:#166534; padding:15px; border-radius:8px;'>
            <strong>🎉 Sukses!</strong> Database Anda sekarang sudah sinkron dengan versi terbaru.
          </div>";
} else {
    echo "<div style='background:#fee2e2; color:#991b1b; padding:15px; border-radius:8px;'>
            <strong>⚠ Perhatian!</strong> Ada beberapa query yang gagal. Pastikan tabel yang dituju sudah ada.
          </div>";
}

echo "<p style='margin-top:20px; color:#ef4444; font-weight:bold; font-size:14px;'>
        🛡 KEAMANAN: Segera hapus file 'perbaikan_db.php' ini dari server aaPanel Anda setelah selesai digunakan!
      </p>";
echo "</div>";
