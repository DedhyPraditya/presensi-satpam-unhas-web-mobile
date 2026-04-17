<?php
$page_title = "Daftar Laporan Kejadian";
include 'layout/header.php';

$msg = '';

// Hapus laporan
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Ambil info foto dulu untuk dihapus dari storage
    $cek = $koneksi->query("SELECT foto FROM laporan WHERE id=$id")->fetch_assoc();
    if($cek){
        if(!empty($cek['foto']) && file_exists('uploads/'.$cek['foto'])){
            unlink('uploads/'.$cek['foto']);
        }
        $koneksi->query("DELETE FROM laporan WHERE id=$id");
        $msg = "✅ Laporan berhasil dihapus.";
    }
    header("Location: laporan_admin.php?msg=" . urlencode($msg));
    exit;
}

// Ambil data user untuk filter dropdown
$users_list = $koneksi->query("SELECT id, nama FROM users WHERE role='user' ORDER BY nama ASC");

// Logic Filter
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
?>
  <div class="page-header">
    <div class="page-title">
      <h2>Laporan Kejadian Lapangan</h2>
      <p>Pantau laporan dan temuan dari personel satpam di lapangan</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="export_laporan_pdf.php?<?php echo $_SERVER['QUERY_STRING'] ?? ''; ?>" target="_blank" class="btn btn-primary" style="background: #ef4444; border: none;">
            <i data-lucide="file-text" style="width: 18px;"></i> Export PDF
        </a>
        <div class="badge badge-danger" style="padding: 10px 20px; display: flex; align-items: center;">
          <b><?= $laporan->num_rows ?></b> &nbsp; Total Laporan
        </div>
    </div>
  </div>

  <!-- Filter Form -->
  <div class="card" style="margin-bottom: 25px; padding: 20px;">
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
      <div style="flex: 1; min-width: 150px;">
        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 5px;">Mulai Tanggal</label>
        <input type="date" name="start_date" value="<?= $start_date ?>" class="input" style="margin-bottom: 0;">
      </div>
      <div style="flex: 1; min-width: 150px;">
        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 5px;">Sampai Tanggal</label>
        <input type="date" name="end_date" value="<?= $end_date ?>" class="input" style="margin-bottom: 0;">
      </div>
      <div style="flex: 1; min-width: 200px;">
        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 5px;">Pilih Personel</label>
        <select name="user_id" class="input" style="margin-bottom: 0;">
          <option value="">-- Semua Personel --</option>
          <?php while($ul = $users_list->fetch_assoc()): ?>
            <option value="<?= $ul['id'] ?>" <?= ($filter_user == $ul['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ul['nama']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div style="display: flex; gap: 10px;">
        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
          <i data-lucide="filter" style="width: 16px;"></i> Filter
        </button>
        <?php if(!empty($start_date) || !empty($end_date) || !empty($filter_user)): ?>
          <a href="laporan_admin.php" class="btn" style="background: #f1f5f9; color: #475569; padding: 10px 20px;">
            <i data-lucide="rotate-ccw" style="width: 16px;"></i> Reset
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <?php if(isset($_GET['msg'])): ?>
    <div class="card" style="background: #ecfdf5; border-color: #10b981; color: #166534; padding: 15px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
        <i data-lucide="check-circle" style="width: 20px;"></i>
        <?= htmlspecialchars($_GET['msg']) ?>
    </div>
  <?php endif; ?>

  <div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>Pelapor</th>
            <th>Waktu Kejadian</th>
            <th style="width: 30%;">Deskripsi Kejadian</th>
            <th>Dokumentasi</th>
            <th>Lokasi</th>
            <th style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($l = $laporan->fetch_assoc()): ?>
          <tr>
            <td>
                <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($l['nama_user']) ?></div>
                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($l['nip']) ?></div>
            </td>
            <td>
                <div style="font-weight: 600;"><?= date('d M Y', strtotime($l['tanggal'])) ?></div>
                <div style="font-size: 12px; color: var(--text-muted);"><?= $l['jam'] ?> WITA</div>
            </td>
            <td>
                <p style="font-size: 14px; color: #475569; line-height: 1.5;"><?= nl2br(htmlspecialchars($l['deskripsi'])) ?></p>
            </td>
            <td>
                <?php if(!empty($l['foto'])): ?>
                    <img class="thumb" src="uploads/<?= $l['foto'] ?>" onclick="viewImage(this.src)" style="width: 80px; height: 80px; border-radius: 12px;" title="Klik untuk perbesar">
                <?php else: ?>
                    <span class="text-muted">No Photo</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($l['latitude'] && $l['longitude']): ?>
                    <a href="https://www.google.com/maps?q=<?= $l['latitude'] ?>,<?= $l['longitude'] ?>" target="_blank" class="badge badge-info" style="text-decoration: none;">
                        <i data-lucide="map-pin" style="width: 12px;"></i> Lihat Peta
                    </a>
                <?php else: ?>
                    <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            <td style="text-align: right;">
                <a href="?hapus=<?= $l['id'] ?>" class="btn" style="background: #fee2e2; color: #dc2626; padding: 8px; border-radius: 10px;" onclick="return confirm('Hapus laporan ini?')">
                  <i data-lucide="trash-2" style="width: 18px;"></i>
                </a>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if($laporan->num_rows == 0): ?>
            <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted);">
              <i data-lucide="clipboard-x" style="width: 40px; height: 40px; margin-bottom: 15px; opacity: 0.2;"></i>
              <p>Belum ada laporan masuk dari lapangan.</p>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php include 'layout/footer.php'; ?>
