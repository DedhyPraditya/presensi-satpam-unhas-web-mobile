<?php
session_start();
require 'koneksi.php';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nip = $_POST['nip'];
  $password = $_POST['password'];

  $stmt = $koneksi->prepare("SELECT * FROM users WHERE nip=? LIMIT 1");
  $stmt->bind_param("s", $nip);
  $stmt->execute();
  $q = $stmt->get_result();

  if ($q && $q->num_rows === 1) {
    $u = $q->fetch_assoc();

    if (!password_verify($password, $u['password'])) {
      $err = 'Password salah.';
    } elseif ($u['status'] !== 'verified') {
      $err = 'Akun belum diverifikasi admin.';
    } else {
      $_SESSION['user_id'] = $u['id'];
      $_SESSION['nama'] = $u['nama'];
      $_SESSION['role'] = $u['role'];
      $_SESSION['id_pos'] = $u['id_pos'];

      header('Location: ' . ($u['role'] == 'admin' ? 'dashboard_admin.php' : 'dashboard_user.php'));
      exit;
    }
  } else {
    $err = 'NIP tidak ditemukan.';
  }
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Presensi Satpam UNHAS</title>
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .login-split {
      display: flex;
      min-height: 100vh;
      width: 100%;
      overflow: hidden;
    }

    .login-banner {
      flex: 1.2;
      background-color: #1e3a8a;
      background-image: linear-gradient(135deg, var(--primary-dark), var(--primary));
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px;
      color: #fff;
      position: relative;
    }

    .login-banner img {
      width: 180px;
      filter: drop-shadow(0 0 20px rgba(0, 0, 0, 0.2));
      margin-bottom: 30px;
      z-index: 10;
    }

    .login-panel {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      padding: 40px;
    }

    .form-container {
      width: 100%;
      max-width: 400px;
    }

    .login-banner::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
      opacity: 0.1;
    }

    @media (max-width: 900px) {
      .login-split {
        flex-direction: column;
      }

      .login-banner {
        flex: 0.5;
        padding: 40px 20px;
      }

      .login-banner img {
        width: 100px;
        margin-bottom: 15px;
      }

      .login-banner h1 {
        font-size: 24px;
      }

      .login-panel {
        padding: 40px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="login-split">
    <div class="login-banner">
      <img src="assets/logo_unhas.png" alt="Logo UNHAS">
      <h1 style="font-size: 32px; letter-spacing: -1px; margin-bottom: 5px;">SISTEM PRESENSI</h1>
      <p style="opacity: 0.8; font-weight: 500; text-align: center;">Satuan Pengamanan Universitas Hasanuddin</p>
    </div>
    <div class="login-panel">
      <div class="form-container">
        <div style="margin-bottom: 35px;">
          <h2 style="color: var(--primary-dark); font-size: 28px; margin-bottom: 8px;">Selamat Datang</h2>
          <p style="color: var(--text-muted); font-size: 14px;">Silakan login untuk memulai sesi anda</p>
        </div>

        <?php if ($err): ?>
          <div class="card"
            style="background: #fff1f2; border-color: #fda4af; color: #9f1239; padding: 12px 15px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px; border-radius: 14px;">
            <i data-lucide="alert-circle" style="width: 20px;"></i>
            <span style="font-size: 14px; font-weight: 500;"><?= htmlspecialchars($err) ?></span>
          </div>
        <?php endif; ?>

        <form method="post" id="loginForm">
          <div style="margin-bottom: 20px;">
            <label
              style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">NIP
              / USERNAME</label>
            <div style="position: relative;">
              <i data-lucide="user"
                style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);"></i>
              <input class="input" name="nip" placeholder="Masukkan NIP" required
                style="padding-left: 45px; margin-bottom: 0;">
            </div>
          </div>

          <div style="margin-bottom: 30px;">
            <label
              style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">PASSWORD</label>
            <div style="position: relative;">
              <i data-lucide="lock"
                style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); width: 18px; color: var(--text-muted);"></i>
              <input class="input" type="password" name="password" placeholder="Masukkan Password" required
                style="padding-left: 45px; margin-bottom: 0;">
            </div>
          </div>

          <button class="btn btn-primary" type="submit"
            style="width: 100%; padding: 16px; font-size: 16px; box-shadow: 0 10px 20px -5px rgba(11, 37, 140, 0.4);">
            Masuk Sekarang <i data-lucide="chevron-right" style="width: 18px;"></i>
          </button>
        </form>

        <div style="margin-top:40px; text-align: center; font-size: 14px; color: var(--text-muted);">
          Belum punya akun? <a href="register.php"
            style="color: var(--primary); font-weight: 700; text-decoration: none;">Daftar Akun Baru</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>

</html>