<?php
session_start();
require 'koneksi.php';

$msg = '';
$is_success = false;

// Ambil daftar POS dari tabel pos_lokasi
$pos_query = $koneksi->query("SELECT id, nama_pos FROM pos_lokasi ORDER BY id ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = trim($_POST['nama']);
  $nip = trim($_POST['nip']);
  $pass = $_POST['password'];
  $cpass = $_POST['cpassword'];
  $jenis = $_POST['jenis_kerja'];
  $id_pos = intval($_POST['id_pos']);

  // Validasi
  if ($pass !== $cpass) {
    $msg = "Password & konfirmasi tidak sama.";
  } elseif (!preg_match('/^\d{4,20}$/', $nip)) {
    $msg = "NIP harus berupa angka 4-20 digit.";
  } elseif (!in_array($jenis, ['non_shift', 'shift'])) {
    $msg = "Jenis kerja tidak valid.";
  } elseif ($id_pos < 1) {
    $msg = "Silakan pilih lokasi POS kerja.";
  } else {
    // Cek NIP sudah terdaftar
    $stmt = $koneksi->prepare("SELECT id FROM users WHERE nip=?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $msg = "NIP sudah terdaftar.";
    } else {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $status = 'pending';
      $role = 'user';

      // Insert user
      $stmt2 = $koneksi->prepare("
                INSERT INTO users (nama,nip,password,role,jenis_kerja,status,id_pos)
                VALUES (?,?,?,?,?,?,?)
            ");
      $stmt2->bind_param("ssssssi", $nama, $nip, $hash, $role, $jenis, $status, $id_pos);

      if ($stmt2->execute()) {
        $msg = "Pendaftaran sukses! Silakan hubungi admin untuk verifikasi akun anda.";
        $is_success = true;
      } else {
        $msg = "Terjadi kesalahan: " . $stmt2->error;
      }
    }
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Akun - Presensi Satpam UNHAS</title>
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .reg-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 40px 20px;
    }

    .reg-card {
      width: 100%;
      max-width: 550px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    @media (max-width: 600px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div class="reg-container">
    <div class="card reg-card" style="padding: 40px;">
      <div style="text-align: center; margin-bottom: 35px;">
        <div
          style="background: #e0f2fe; width: 60px; height: 60px; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; color: var(--primary); margin-bottom: 20px;">
          <i data-lucide="user-plus" style="width: 30px; height: 30px;"></i>
        </div>
        <h2 style="font-size: 28px; color: var(--primary-dark); margin-bottom: 8px;">Daftar Akun Baru</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Lengkapi data diri anda untuk mendaftar sistem presensi
        </p>
      </div>

      <?php if ($msg): ?>
        <div class="card"
          style="background: <?= $is_success ? '#ecfdf5' : '#fff1f2' ?>; border-color: <?= $is_success ? '#10b981' : '#fda4af' ?>; color: <?= $is_success ? '#166534' : '#9f1239' ?>; padding: 15px; display: flex; align-items: center; gap: 12px; margin-bottom: 25px; border-radius: 14px;">
          <i data-lucide="<?= $is_success ? 'check-circle' : 'alert-circle' ?>" style="width: 20px;"></i>
          <span style="font-size: 14px; font-weight: 500;"><?= htmlspecialchars($msg) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!$is_success): ?>
        <form method="post">
          <div style="margin-bottom: 15px;">
            <label
              style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 6px;">NAMA
              LENGKAP</label>
            <div style="position: relative;">
              <i data-lucide="user"
                style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
              <input class="input" name="nama" placeholder="Contoh: Andi Satriawan" required
                style="padding-left: 42px; margin-bottom: 0;">
            </div>
          </div>

          <div style="margin-bottom: 15px;">
            <label
              style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 6px;">NIP
              / NOMOR INDUK PEGAWAI</label>
            <div style="position: relative;">
              <i data-lucide="id-card"
                style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 16px; color: var(--text-muted);"></i>
              <input class="input" name="nip" placeholder="Masukkan NIP anda" required
                style="padding-left: 42px; margin-bottom: 0;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom: 15px;">
            <div>
              <label
                style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 6px;">PASSWORD</label>
              <input class="input" type="password" name="password" placeholder="Min 6 karakter" required
                style="margin-bottom: 0;">
            </div>
            <div>
              <label
                style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 6px;">KONFIRMASI</label>
              <input class="input" type="password" name="cpassword" placeholder="Ulangi password" required
                style="margin-bottom: 0;">
            </div>
          </div>

          <div class="form-grid" style="margin-bottom: 30px;">
            <div>
              <label
                style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 6px;">JENIS
                KERJA</label>
              <select class="input" name="jenis_kerja" required style="margin-bottom: 0;">
                <option value="non_shift">Non Shift</option>
                <option value="shift">Shift</option>
              </select>
            </div>
            <div>
              <label
                style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 6px;">LOKASI
                POS</label>
              <select class="input" name="id_pos" required style="margin-bottom: 0;">
                <option value="">Pilih POS...</option>
                <?php while ($p = $pos_query->fetch_assoc()): ?>
                  <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_pos']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>

          <button class="btn btn-primary" type="submit" style="width: 100%; padding: 16px; font-size: 16px;">
            Daftar Akun Sekarang <i data-lucide="arrow-right" style="width: 18px;"></i>
          </button>
        </form>
      <?php else: ?>
        <div style="text-align: center; margin-top: 20px;">
          <a href="login.php" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 16px;">
            Kembali ke Login <i data-lucide="log-in" style="width: 18px;"></i>
          </a>
        </div>
      <?php endif; ?>

      <div style="margin-top:30px; text-align: center; font-size: 14px; color: var(--text-muted);">
        Sudah punya akun? <a href="login.php"
          style="color: var(--primary); font-weight: 700; text-decoration: none;">Login Sekarang</a>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>