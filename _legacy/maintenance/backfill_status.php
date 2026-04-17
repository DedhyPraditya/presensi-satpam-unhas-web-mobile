<?php
/**
 * SCRIPT BACKFILL STATUS ABSENSI
 * ------------------------------
 * Digunakan untuk mengisi kolom 'terlambat' dan 'cepat_pulang' pada data lama
 * agar sinkron antara Dashboard Admin dan User.
 */

require 'koneksi.php';

// Pastikan koneksi berhasil
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

echo "<h2>⚙ Memulai Proses Backfill Status...</h2><hr>";

// 1. Ambil Pengaturan Jam Kerja
$cfg = $koneksi->query("SELECT * FROM pengaturan WHERE id=1")->fetch_assoc();
if (!$cfg) {
    die("Gagal mengambil data pengaturan.");
}

// 2. Ambil Semua Data Absensi
$sql_absensi = "SELECT a.*, u.jenis_kerja 
                FROM absensi a 
                JOIN users u ON a.user_id = u.id";
$res_absensi = $koneksi->query($sql_absensi);

$updated = 0;
$skipped = 0;

while ($row = $res_absensi->fetch_assoc()) {
    $id = $row['id'];
    $jam_masuk = $row['jam_masuk'];
    $jam_pulang = $row['jam_pulang'];
    $jenis_kerja = $row['jenis_kerja'];
    
    $terlambat = null;
    $cepat_pulang = null;

    // --- LOGIKA TERLAMBAT (MASUK) ---
    if (!empty($jam_masuk) && $jam_masuk != '-') {
        $target_masuk = '';
        if ($jenis_kerja == 'non_shift') {
            $target_masuk = $cfg['jam_masuk_non_shift_pagi'];
        } else {
            // Logika Shift (Pagi vs Malam)
            $h = intval(substr($jam_masuk, 0, 2));
            if ($h >= 5 && $h <= 12) {
                $target_masuk = $cfg['jam_masuk_shift_pagi'];
            } else {
                $target_masuk = $cfg['jam_masuk_shift_malam'];
            }
        }
        
        if (!empty($target_masuk)) {
            $terlambat = (strtotime($jam_masuk) > strtotime($target_masuk)) ? 'Ya' : 'Tidak';
        }
    }

    // --- LOGIKA CEPAT PULANG ---
    if (!empty($jam_pulang) && $jam_pulang != '-') {
        $target_pulang = '';
        if ($jenis_kerja == 'non_shift') {
            $target_pulang = $cfg['jam_pulang_non_shift_pagi'];
        } else {
            // Logika Shift (Pagi vs Malam)
            $h = intval(substr($jam_pulang, 0, 2));
            if ($h >= 15 && $h <= 23) {
                $target_pulang = $cfg['jam_pulang_shift_pagi'];
            } else {
                $target_pulang = $cfg['jam_pulang_shift_malam'];
            }
        }

        if (!empty($target_pulang)) {
            $cepat_pulang = (strtotime($jam_pulang) < strtotime($target_pulang)) ? 'Ya' : 'Tidak';
        }
    }

    // 3. Update Database
    if ($terlambat !== null || $cepat_pulang !== null) {
        $update_sql = "UPDATE absensi SET terlambat = ?, cepat_pulang = ? WHERE id = ?";
        $stmt = $koneksi->prepare($update_sql);
        $stmt->bind_param("ssi", $terlambat, $cepat_pulang, $id);
        $stmt->execute();
        $updated++;
    } else {
        $skipped++;
    }
}

echo "<h3>Proses Selesai!</h3>";
echo "<ul>
        <li>Data Diperbarui: <strong>$updated</strong> baris</li>
        <li>Data Dilewati: <strong>$skipped</strong> baris</li>
      </ul>";
echo "<p style='color:red;'>Silakan hapus file ini dari server demi keamanan.</p>";
