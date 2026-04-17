<?php
require_once 'koneksi.php';
session_start();

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Logic Filter (Sama dengan laporan_admin.php)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date'])   ? $_GET['end_date']   : '';
$filter_user= isset($_GET['user_id'])    ? $_GET['user_id']    : '';

$where_clauses = [];
if (!empty($start_date)) $where_clauses[] = "l.tanggal >= '$start_date'";
if (!empty($end_date))   $where_clauses[] = "l.tanggal <= '$end_date'";
if (!empty($filter_user))$where_clauses[] = "l.user_id = '$filter_user'";

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Ambil data laporan
$sql = "SELECT l.*, u.nama AS nama_user, u.nip 
        FROM laporan l 
        JOIN users u ON l.user_id = u.id 
        $where_sql
        ORDER BY l.created_at DESC";
$laporan = $koneksi->query($sql);

$filter_info = "Semua Laporan";
if (!empty($start_date) || !empty($end_date)) {
    $filter_info = "Periode: " . ($start_date ?: '...') . " s/d " . ($end_date ?: '...');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kejadian - Satpam UNHAS</title>
    <style>
        body { font-family: 'Inter', sans-serif; color: #334155; margin: 0; padding: 40px; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #1e3a8a; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #64748b; font-size: 14px; }
        
        .meta { margin-bottom: 20px; display: flex; justify-content: space-between; font-size: 13px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; color: #475569; font-weight: 700; text-align: left; padding: 12px; border: 1px solid #e2e8f0; font-size: 12px; text-transform: uppercase; }
        td { padding: 12px; border: 1px solid #e2e8f0; font-size: 13px; vertical-align: top; }
        
        .foto-container { width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        .foto-container img { width: 100%; height: 100%; object-fit: cover; }
        
        .deskripsi { line-height: 1.6; color: #1e293b; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            tr { page-break-inside: avoid; }
        }

        .no-print-btn {
            position: fixed; top: 20px; right: 20px; background: #1e3a8a; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; cursor: pointer; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print-btn no-print">Cetak Ke PDF</button>

    <div class="header">
        <h1>Laporan Kejadian Lapangan</h1>
        <p>Satuan Pengamanan (SATPAM) Universitas Hasanuddin</p>
    </div>

    <div class="meta">
        <div><strong>Status Report:</strong> <?= $filter_info ?></div>
        <div><strong>Tanggal Cetak:</strong> <?= date('d M Y H:i') ?> WITA</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Personel</th>
                <th style="width: 15%;">Waktu</th>
                <th>Deskripsi Kejadian</th>
                <th style="width: 130px;">Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($laporan->num_rows > 0): ?>
                <?php while ($l = $laporan->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($l['nama_user']) ?></strong><br>
                            <span style="color: #64748b; font-size: 11px;">NIP: <?= htmlspecialchars($l['nip']) ?></span>
                        </td>
                        <td>
                            <?= date('d M Y', strtotime($l['tanggal'])) ?><br>
                            <?= $l['jam'] ?> WITA
                        </td>
                        <td class="deskripsi">
                            <?= nl2br(htmlspecialchars($l['deskripsi'])) ?>
                            <?php if($l['latitude'] && $l['longitude']): ?>
                                <div style="margin-top: 8px; font-size: 11px; color: #1e40af;">
                                    Lokasi: <?= $l['latitude'] ?>, <?= $l['longitude'] ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($l['foto'])): ?>
                                <div class="foto-container">
                                    <img src="uploads/<?= $l['foto'] ?>">
                                </div>
                            <?php else: ?>
                                <span style="color: #cbd5e1; font-style: italic;">Tidak ada foto</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: #94a3b8;">Tidak ada data laporan ditemukan untuk filter ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; font-size: 12px; color: #94a3b8;">
        Dokumen ini dibuat secara otomatis melalui Sistem Absensi Satpam UNHAS.
    </div>

    <script>
        // Trigger print dialog automatically after slight delay to ensure images load
        window.onload = function() {
            setTimeout(function() {
                // window.print(); // Uncomment if you want automatic trigger
            }, 1000);
        };
    </script>
</body>
</html>
