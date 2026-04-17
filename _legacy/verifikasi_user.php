<?php
$page_title = "Verifikasi User";
include 'layout/header.php';

$msg = '';

// verifikasi user
if (isset($_GET['verif'])) {
    $id = intval($_GET['verif']);
    $stmt = $koneksi->prepare("UPDATE users SET status='verified' WHERE id=? AND role='user'");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        $msg = "✅ User berhasil diverifikasi.";
    } else {
        $msg = "⚠ Gagal memverifikasi user.";
    }
    header("Location: verifikasi_user.php?msg=" . urlencode($msg));
    exit;
}

// hapus user
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $cek = $koneksi->query("SELECT role FROM users WHERE id=$id")->fetch_assoc();
    if ($cek && $cek['role'] !== 'admin') {
        $stmt = $koneksi->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        if($stmt->execute()){
            $msg = "✅ User berhasil dihapus.";
        } else {
            $msg = "⚠ Gagal menghapus user.";
        }
    }
    header("Location: verifikasi_user.php?msg=" . urlencode($msg));
    exit;
}

// ambil data user
$users = $koneksi->query("SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
?>
  <div class="page-header">
    <div class="page-title">
      <h2>Verifikasi Pengguna</h2>
      <p>Kelola pendaftaran akun satpam baru</p>
    </div>
    <div class="badge badge-info" style="padding: 10px 20px;">
      <b><?= $users->num_rows ?></b> &nbsp; Total User
    </div>
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
            <th>Informasi Satpam</th>
            <th>Tipe Kerja</th>
            <th>Status Akun</th>
            <th style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($u = $users->fetch_assoc()): ?>
          <tr>
            <td>
                <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($u['nama']) ?></div>
                <div style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($u['nip']) ?></div>
            </td>
            <td>
              <span class="badge badge-info" style="text-transform: capitalize; background: #f1f5f9; color: #475569;">
                <i data-lucide="briefcase" style="width: 12px;"></i>
                <?= str_replace('_', ' ', $u['jenis_kerja']) ?>
              </span>
            </td>
            <td>
                <?php if($u['status'] == 'pending'): ?>
                  <span class="badge badge-warning">
                    <i data-lucide="clock" style="width: 12px;"></i> Menunggu
                  </span>
                <?php else: ?>
                  <span class="badge badge-success">
                    <i data-lucide="user-check" style="width: 12px;"></i> Terverifikasi
                  </span>
                <?php endif; ?>
            </td>
            <td style="text-align: right;">
              <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <?php if ($u['status'] == 'pending'): ?>
                  <a href="?verif=<?= $u['id'] ?>" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">
                    <i data-lucide="check" style="width: 14px;"></i> Verifikasi
                  </a>
                <?php endif; ?>
                <a href="?hapus=<?= $u['id'] ?>" class="btn" style="background: #fee2e2; color: #dc2626; padding: 8px 16px; font-size: 13px;" onclick="return confirm('Hapus user ini permanent?')">
                  <i data-lucide="trash-2" style="width: 14px;"></i> Hapus
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if($users->num_rows == 0): ?>
            <tr><td colspan="4" style="text-align: center; padding: 60px; color: var(--text-muted);">
              <i data-lucide="user-plus" style="width: 40px; height: 40px; margin-bottom: 15px; opacity: 0.2;"></i>
              <p>Belum ada data user terdaftar.</p>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php include 'layout/footer.php'; ?>
