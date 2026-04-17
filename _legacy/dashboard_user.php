<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user')
  header('Location: login.php');

$uid = intval($_SESSION['user_id']);

// Ambil data user + POS kerja
$sql = "SELECT u.*, p.nama_pos, p.latitude AS lat_pos, p.longitude AS lng_pos, p.radius 
        FROM users u 
        LEFT JOIN pos_lokasi p ON u.id_pos = p.id 
        WHERE u.id=?";
$stmt_user = $koneksi->prepare($sql);
$stmt_user->bind_param("i", $uid);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

if (!$user['id_pos']) {
  die("<div style='padding:50px; text-align:center;'><h3>⚠ POS kerja belum ditentukan admin. Silakan hubungi admin.</h3><a href='logout.php'>Logout</a></div>");
}

// Hari Ini
$stmt_today = $koneksi->prepare("SELECT * FROM absensi WHERE user_id=? AND tanggal=CURDATE() LIMIT 1");
$stmt_today->bind_param("i", $uid);
$stmt_today->execute();
$hari_ini = $stmt_today->get_result()->fetch_assoc();

// Riwayat Absensi 7 Hari Kebelakang
$stmt_riwayat = $koneksi->prepare("SELECT * FROM absensi WHERE user_id=? AND tanggal < CURDATE() ORDER BY tanggal DESC LIMIT 7");
$stmt_riwayat->bind_param("i", $uid);
$stmt_riwayat->execute();
$riwayat = $stmt_riwayat->get_result();

// Riwayat Laporan 1 Minggu Terakhir
$stmt_lap = $koneksi->prepare("SELECT * FROM laporan WHERE user_id=? AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY created_at DESC");
$stmt_lap->bind_param("i", $uid);
$stmt_lap->execute();
$riwayat_laporan = $stmt_lap->get_result();

function tgl_indo($tanggal)
{
  $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  $hari = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
  $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
  $nama_hari = $hari[date('l', strtotime($tanggal))];
  return $nama_hari . ', ' . $pecahkan[2] . ' ' . $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];
}
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
  <title>Presensi Satpam - UNHAS</title>
  <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- PWA META TAGS -->
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#1e3a8a">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Absensi UNHAS">
  <link rel="apple-touch-icon" href="assets/logo_unhas.png">
  <style>
    /* Dashboard-specific tweaks */
    #map {
      height: 220px;
      border-radius: 16px;
      margin: 12px 0;
      border: 1px solid #e2e8f0;
      z-index: 1;
    }

    #jam {
      font-size: 42px;
      font-weight: 800;
      color: var(--primary);
      letter-spacing: -2px;
      font-family: 'Outfit', sans-serif;
      line-height: 1;
      margin-bottom: 5px;
    }

    @keyframes spin {
      from {
        transform: translateY(-50%) rotate(0deg);
      }

      to {
        transform: translateY(-50%) rotate(360deg);
      }
    }

    .spin {
      animation: spin-simple 1s linear infinite;
    }

    @keyframes spin-simple {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .absen-btn-container {
      display: flex;
      gap: 12px;
      margin-top: 25px;
    }

    .absen-btn {
      flex: 1;
      padding: 18px;
      font-size: 16px;
      border-radius: 16px;
      flex-direction: column;
      gap: 8px;
    }

    .absen-btn i {
      width: 24px;
      height: 24px;
    }

    #camera-container {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: #000;
      z-index: 2000;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    #video {
      width: 100%;
      max-height: 80vh;
      object-fit: cover;
    }

    .camera-controls {
      width: 100%;
      padding: 40px;
      display: flex;
      justify-content: center;
      position: absolute;
      bottom: 0;
      background: linear-gradient(transparent, rgba(0, 0, 0, 0.5));
    }

    .capture-btn {
      width: 75px;
      height: 75px;
      border-radius: 50%;
      border: 6px solid rgba(255, 255, 255, 0.4);
      background: #fff;
      cursor: pointer;
      transition: transform 0.2s;
    }

    .capture-btn:active {
      transform: scale(0.9);
    }

    .close-camera {
      position: absolute;
      top: 30px;
      right: 20px;
      color: white;
      cursor: pointer;
      background: rgba(0, 0, 0, 0.4);
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(5px);
    }

    .history-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 1px solid #f1f5f9;
    }

    .history-item:last-child {
      border-bottom: none;
    }

    /* Inlined to guarantee no overlap despite server cache */
    .user-header {
      background: linear-gradient(135deg, var(--primary-dark, #1e3a8a), var(--primary, #3b82f6));
      color: #fff;
      padding: 40px 24px 80px 24px;
      border-radius: 0 0 40px 40px;
      position: relative;
    }

    .user-content {
      margin-top: -50px;
      padding: 0 20px 100px 20px;
      /* More bottom padding for nav */
    }

    /* Bottom Navigation */
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      max-width: 500px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      display: flex;
      justify-content: space-around;
      padding: 12px 0 25px 0;
      border-top: 1px solid #e2e8f0;
      z-index: 1000;
      box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
    }

    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      color: #94a3b8;
      text-decoration: none;
      font-size: 11px;
      font-weight: 600;
      transition: all 0.2s;
      flex: 1;
    }

    .nav-item.active {
      color: var(--primary);
    }

    .nav-item i {
      width: 22px;
      height: 22px;
    }

    .view-content {
      display: none;
      animation: fadeIn 0.3s ease;
    }

    .view-content.active {
      display: block;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .card,
    .profile-card {
      position: relative;
      z-index: 10;
    }

    .profile-card {
      background: #fff;
      border-radius: 20px;
      padding: 24px;
      margin-bottom: 15px;
      border: 1px solid #e2e8f0;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f1f5f9;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: #64748b;
      font-size: 13px;
    }

    .info-value {
      color: #1e293b;
      font-weight: 700;
      font-size: 13px;
    }
  </style>
</head>

<body>

  <!-- MOBILE APP WRAPPER FOR DESKTOP -->
  <div
    style="max-width: 500px; margin: 0 auto; min-height: 100vh; background: #f8fafc; box-shadow: 0 0 50px rgba(0,0,0,0.08); position: relative;">
    <div class="user-header">
      <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
          <p style="opacity: 0.8; font-size: 14px; font-weight: 500; margin-bottom: 2px;">Selamat Bekerja,</p>
          <h2 style="font-size: 24px;"><?= htmlspecialchars($user['nama']) ?></h2>
          <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; opacity: 0.8; margin-top: 4px;">
            <img src="../assets/logo_unhas.png" onerror="this.src='assets/logo_unhas.png'" alt="UNHAS"
              style="width: 20px; height: 20px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px;">
            <span>Personnel Security UNHAS</span>
          </div>
        </div>
        <a href="logout.php"
          style="background: rgba(255,255,255,0.2); width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;">
          <i data-lucide="log-out"></i>
        </a>
      </div>
    </div>

    <div class="user-content">
      <!-- VIEW: HOME (ABSENSI) -->
      <div id="view-home" class="view-content active">
        <!-- CLOCK & ACTION -->
        <div class="card" style="text-align: center; margin-top: -30px;">
          <div id="jam">00:00:00</div>
          <p id="tgl_sekarang" style="font-weight: 600; color: var(--text-muted); font-size: 14px;"></p>

          <div class="absen-btn-container">
            <button id="btnMasuk" class="btn btn-primary absen-btn" disabled>
              <i data-lucide="user-check"></i>
              <span>Masuk</span>
            </button>
            <button id="btnPulang" class="btn absen-btn" style="background: #334155; color: #fff;" disabled>
              <i data-lucide="user-minus"></i>
              <span>Pulang</span>
            </button>
          </div>
          <div id="status-box"
            style="margin-top: 15px; padding: 12px; border-radius: 12px; background: #fff5f5; border: 1px solid #fee2e2; position: relative;">
            <p id="status-text"
              style="font-size: 13px; color: var(--accent); font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 8px; margin-right: 30px;">
              <i data-lucide="map-pin-off" style="width:16px; height:16px;"></i>
              Mencari lokasi...
            </p>
            <button onclick="detect()" id="btn-refresh-gps"
              style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; display: flex; align-items: center;">
              <i data-lucide="refresh-cw" style="width:16px; height:16px;"></i>
            </button>
          </div>
        </div>

        <!-- LOCATION INFO -->
        <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h4 style="color: var(--primary); display: flex; align-items: center; gap: 8px;">
              <i data-lucide="navigation" style="width:18px;"></i> Tik POS Penugasan
            </h4>
            <span class="badge badge-info"><?= htmlspecialchars($user['nama_pos']) ?></span>
          </div>
          <div id="map"></div>
          <p
            style="font-size: 12px; color: var(--text-muted); line-height: 1.5; background: #f8fafc; padding: 10px; border-radius: 10px; border: 1px dashed #e2e8f0;">
            Anda hanya dapat melakukan presensi jika berada dalam radius <b><?= $user['radius'] ?> meter</b> dari titik
            pusat POS.
          </p>
        </div>

        <!-- HARI INI -->
        <?php if ($hari_ini): ?>
          <div class="card" style="padding: 24px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-around; align-items: center; text-align: center;">
              <!-- Masuk -->
              <div style="flex: 1;">
                <?php if (!empty($hari_ini['foto_masuk'])): ?>
                  <img src="uploads/<?= $hari_ini['foto_masuk'] ?>"
                    style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #e2e8f0;">
                <?php else: ?>
                  <div
                    style="width: 70px; height: 70px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                    <i data-lucide="user"></i>
                  </div>
                <?php endif; ?>
                <div
                  style="font-size: 14px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 6px;">
                  <div style="width: 12px; height: 12px; background: #22c55e; border-radius: 50%;"></div> Waktu Masuk
                </div>
                <div style="font-weight: 700; margin-top: 5px; color: var(--text-main); font-size: 13px;">
                  <i data-lucide="clock" style="width:14px; display:inline-block; margin-right:2px; opacity:0.6;"></i>
                  <?= $hari_ini['jam_masuk'] ?> WIB
                </div>
                <div style="margin-top:4px;">
                  <?php if ($hari_ini['terlambat'] == 'Ya'): ?>
                    <span class="badge badge-danger" style="font-size:9px;">Trlmbt</span>
                  <?php elseif ($hari_ini['terlambat'] == 'Tidak'): ?>
                    <span class="badge badge-success" style="font-size:9px;">On Time</span>
                  <?php else: ?>
                    <span class="badge badge-info" style="font-size:9px;">-</span>
                  <?php endif; ?>
                </div>
              </div>

              <div style="width: 1px; background: #cbd5e1; height: 100px;"></div>

              <!-- Pulang -->
              <div style="flex: 1;">
                <?php if (!empty($hari_ini['foto_pulang'])): ?>
                  <img src="uploads/<?= $hari_ini['foto_pulang'] ?>"
                    style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #e2e8f0;">
                <?php else: ?>
                  <div
                    style="width: 70px; height: 70px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px auto;">
                    <i data-lucide="camera-off" style="color:#94a3b8;"></i>
                  </div>
                <?php endif; ?>

                <div
                  style="font-size: 14px; color: var(--text-muted); display: flex; align-items: center; justify-content: center; gap: 6px;">
                  <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%;"></div> Waktu Keluar
                </div>
                <div style="font-weight: 700; margin-top: 5px; color: var(--text-main); font-size: 13px;">
                  <i data-lucide="clock" style="width:14px; display:inline-block; margin-right:2px; opacity:0.6;"></i>
                  <?= $hari_ini['jam_pulang'] ?? '--:--:--' ?> WIB
                </div>
                <div style="margin-top:4px;">
                  <?php if (!empty($hari_ini['jam_pulang'])): ?>
                    <?php if ($hari_ini['cepat_pulang'] == 'Ya'): ?>
                      <span class="badge badge-danger" style="font-size:9px;">Awal</span>
                    <?php else: ?>
                      <span class="badge badge-success" style="font-size:9px;">On Time</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- HISTORY -->
        <div style="padding: 10px 0;">
          <h3 style="color: var(--text-main); font-size: 16px; margin-bottom: 15px; font-weight: 800;">History</h3>
          <div class="history-list" style="display:flex; flex-direction: column; gap: 12px;">
            <?php if ($riwayat->num_rows > 0): ?>
              <?php while ($r = $riwayat->fetch_assoc()): ?>
                <div
                  style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; display: flex; gap: 15px; background: #fff; align-items: center;">
                  <div style="display: flex; gap: 5px;">
                    <?php if (!empty($r['foto_masuk'])): ?>
                      <img src="uploads/<?= $r['foto_masuk'] ?>" onclick="viewImage(this.src)"
                        style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; cursor: pointer;">
                    <?php else: ?>
                      <div style="width: 50px; height: 50px; border-radius: 8px; background: #f1f5f9;"></div>
                    <?php endif; ?>
                    <?php if (!empty($r['foto_pulang'])): ?>
                      <img src="uploads/<?= $r['foto_pulang'] ?>" onclick="viewImage(this.src)"
                        style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; cursor: pointer;">
                    <?php else: ?>
                      <div style="width: 50px; height: 50px; border-radius: 8px; background: #f1f5f9;"></div>
                    <?php endif; ?>
                  </div>

                  <div style="width: 1px; background: #e2e8f0; height: 40px;"></div>

                  <div style="flex: 1;">
                    <div style="color: var(--text-main); font-size: 13px; margin-bottom: 6px; font-weight: 600;">
                      <?= tgl_indo($r['tanggal']) ?>
                    </div>
                    <div style="color: var(--text-muted); font-size: 12px; margin-bottom: 2px;">Waktu Masuk :
                      <?= $r['jam_masuk'] ?? '--:--:--' ?> WIB
                    </div>
                    <div style="color: var(--text-muted); font-size: 12px;">Waktu Keluar :
                      <?= $r['jam_pulang'] ?? '--:--:--' ?> WIB
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 20px;">Belum ada riwayat
                dalam 7 hari terakhir.</p>
            <?php endif; ?>
          </div>
        </div>
      </div> <!-- END VIEW HOME -->

      <!-- VIEW: LAPORAN (REPORT) -->
      <div id="view-laporan" class="view-content">
        <div class="card" style="padding: 24px; margin-top: -30px;">
          <h3
            style="color: var(--text-main); font-size: 18px; margin-bottom: 20px; font-weight: 800; display:flex; align-items:center; gap:10px;">
            <i data-lucide="alert-circle" style="color:var(--accent);"></i> Laporan Kejadian
          </h3>
          <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">Laporkan kejadian atau temuan mencurigakan di
            area penugasan Anda.</p>

          <div style="margin-bottom: 15px;">
            <label
              style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 8px;">Deksripsi
              Kejadian</label>
            <textarea id="lap-deskripsi" class="form-control" rows="4" placeholder="Jelaskan kronologi kejadian..."
              style="width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; font-size: 14px; outline: none; transition: border-color 0.2s;"></textarea>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 700; color: #334155; display: block; margin-bottom: 8px;">Foto
              Bukti</label>
            <div id="lap-foto-preview" style="display:none; margin-bottom:10px; position:relative;">
              <img id="img-preview" src=""
                style="width:100%; height:200px; object-fit:cover; border-radius:12px; border:1px solid #e2e8f0;">
              <button onclick="resetReportCam()"
                style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.5); border:none; color:white; width:35px; height:35px; border-radius:50%; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(5px);"><i
                  data-lucide="x" style="width:18px;"></i></button>
            </div>

            <button id="btnOpenReportCam" class="btn"
              style="width: 100%; background: #f1f5f9; color: #475569; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 25px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
              <i data-lucide="camera" style="width:30px; height:30px; color:#94a3b8;"></i>
              <span style="font-size: 13px; font-weight: 600;">Ambil Foto Bukti</span>
            </button>

            <div style="margin-top: 10px; display: flex; gap: 10px;">
              <button id="btnGallery" class="btn" onclick="document.getElementById('lap-file-input').click()"
                style="flex: 1; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px; font-size: 12px; display: flex; align-items: center; justify-content: center; gap: 5px;">
                <i data-lucide="image" style="width:16px;"></i> Pilih dari Galeri
              </button>
              <input type="file" id="lap-file-input" accept="image/*" style="display: none;"
                onchange="handleFileSelect(this)">
            </div>
          </div>

          <button id="btnKirimLaporan" class="btn btn-primary"
            style="width: 100%; padding: 16px; border-radius: 12px; font-weight: 700; display:flex; align-items:center; justify-content:center; gap:10px;">
            <i data-lucide="send" style="width:18px;"></i> Kirim Laporan
          </button>

          <!-- RIWAYAT LAPORAN (Satu Minggu Terakhir) -->
          <div style="margin-top: 30px; border-top: 2px dashed #f1f5f9; padding-top: 25px;">
            <h4 style="font-size: 14px; font-weight: 800; color: var(--text-main); margin-bottom: 15px;">Riwayat Laporan
              (1 Minggu)</h4>
            <div style="display: flex; flex-direction: column; gap: 10px;">
              <?php if ($riwayat_laporan->num_rows > 0): ?>
                <?php while ($rl = $riwayat_laporan->fetch_assoc()): ?>
                  <div
                    style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; display: flex; gap: 12px;">
                    <?php if (!empty($rl['foto'])): ?>
                      <img src="uploads/<?= $rl['foto'] ?>"
                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer;"
                        onclick="viewImage(this.src)">
                    <?php else: ?>
                      <div
                        style="width: 60px; height: 60px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="image" style="color: #94a3b8;"></i>
                      </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                      <div style="font-weight: 700; font-size: 13px; color: var(--text-main);">
                        <?= tgl_indo($rl['tanggal']) ?>
                      </div>
                      <p style="font-size: 12px; color: #64748b; margin: 4px 0; line-height: 1.4;">
                        <?= htmlspecialchars($rl['deskripsi']) ?>
                      </p>
                      <small style="color: #94a3b8; font-size: 10px;"><?= $rl['jam'] ?> WIB</small>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php else: ?>
                <div style="text-align: center; padding: 20px; color: #94a3b8; font-size: 12px; font-style: italic;">Belum
                  ada laporan dalam 7 hari terakhir.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div> <!-- END VIEW LAPORAN -->

      <!-- VIEW: PROFILE -->
      <div id="view-profile" class="view-content">
        <div class="card" style="text-align: center; margin-top: -30px;">
          <h3 style="color: var(--text-main); font-size: 18px; margin-bottom: 20px; font-weight: 800;">Profil Saya</h3>
          <div
            style="width: 100px; height: 100px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; border: 4px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
            <img src="../assets/logo_unhas.png" onerror="this.src='assets/logo_unhas.png'" alt="UNHAS"
              style="width: 100px; height: 100px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px;">
          </div>
          <h2 style="font-size: 20px; color: #1e293b; margin-bottom: 4px;"><?= htmlspecialchars($user['nama']) ?></h2>
          <span class="badge badge-info"><?= htmlspecialchars($user['nama_pos']) ?></span>

          <div style="margin-top: 25px; text-align: left;">
            <div class="info-row">
              <span class="info-label">NIP</span>
              <span class="info-value"><?= htmlspecialchars($user['nip']) ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Unit Kerja</span>
              <span class="info-value">Personnel Security UNHAS</span>
            </div>
            <div class="info-row">
              <span class="info-label">Jenis Kerja</span>
              <span class="info-value"><?= ucfirst($user['jenis_kerja']) ?></span>
            </div>
            <div class="info-row">
              <span class="info-label">Status Akun</span>
              <span class="info-value" style="color:#22c55e;">Terverifikasi</span>
            </div>
          </div>

          <a href="logout.php" class="btn"
            style="width: 100%; margin-top: 30px; background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; border-radius: 12px; padding: 14px; font-weight: 700;">
            Keluar Aplikasi
          </a>
        </div>

        <div style="text-align: center; color: #94a3b8; font-size: 11px;">
          App Version 2.1.1
        </div>
      </div> <!-- END VIEW PROFILE -->

    </div>

    <!-- BOTTOM NAVIGATION -->
    <nav class="bottom-nav">
      <a href="javascript:void(0)" class="nav-item active" onclick="switchView('home', this)">
        <i data-lucide="home"></i>
        <span>Home</span>
      </a>
      <a href="javascript:void(0)" class="nav-item" onclick="switchView('laporan', this)">
        <i data-lucide="file-text"></i>
        <span>Laporan</span>
      </a>
      <a href="javascript:void(0)" class="nav-item" onclick="switchView('profile', this)">
        <i data-lucide="user"></i>
        <span>Profile</span>
      </a>
    </nav>

  </div> <!-- END MOBILE APP WRAPPER -->

  <!-- CAMERA OVERLAY -->
  <div id="camera-container">
    <div class="close-camera" id="btnCloseCamera">
      <i data-lucide="x"></i>
    </div>
    <div onclick="switchCamera()"
      style="position: absolute; top: 30px; left: 20px; color: white; cursor: pointer; background: rgba(0, 0, 0, 0.4); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); z-index: 2001;">
      <i data-lucide="refresh-cw"></i>
    </div>
    <video id="video" autoplay muted playsinline></video>
    <div class="camera-controls">
      <div class="capture-btn" id="btnCapture"></div>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    lucide.createIcons();

    // CLOCK
    function updateClock() {
      const now = new Date();
      document.getElementById('jam').innerText = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      document.getElementById('tgl_sekarang').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
    setInterval(updateClock, 1000); updateClock();

    // CONFIG
    const latPos = <?= $user['lat_pos'] ?>;
    const lngPos = <?= $user['lng_pos'] ?>;
    const radius = <?= $user['radius'] ?>;
    const map = L.map('map', { zoomControl: false }).setView([latPos, lngPos], 17);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

    const markerPos = L.marker([latPos, lngPos]).addTo(map).bindPopup("Titik POS");
    const markerUser = L.marker([latPos, lngPos], {
      icon: L.divIcon({
        className: 'user-marker',
        html: '<div style="background:var(--primary); width:15px; height:15px; border-radius:50%; border:3px solid #fff; box-shadow:0 0 10px rgba(0,0,0,0.3);"></div>'
      })
    }).addTo(map);

    L.circle([latPos, lngPos], {
      radius: radius,
      color: 'var(--primary)',
      weight: 1,
      fillOpacity: 0.1
    }).addTo(map);

    // DISTANCE CALC
    function getDistance(lat1, lon1, lat2, lon2) {
      const R = 6371000;
      const dLat = (lat2 - lat1) * Math.PI / 180;
      const dLon = (lon2 - lon1) * Math.PI / 180;
      const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
      return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    // LOCATION DETECTION
    const statusBox = document.getElementById('status-box');
    const statusText = document.getElementById('status-text');
    let watchId = null;

    function detect() {
      // Clear existing watch if any
      if (watchId !== null) navigator.geolocation.clearWatch(watchId);

      const refreshBtn = document.getElementById('btn-refresh-gps');
      if (refreshBtn) refreshBtn.style.animation = "spin 1s linear infinite";

      if (!navigator.geolocation) {
        statusText.innerHTML = '<i data-lucide="alert-triangle"></i> Browser tidak mendukung GPS.';
        if (location.protocol !== 'https:') {
          statusText.innerHTML += '<br><small>Wajib menggunakan HTTPS (SSL) untuk akses lokasi.</small>';
        }
        return;
      }

      statusText.innerHTML = '<i data-lucide="loader" class="spin"></i> Update lokasi...';
      lucide.createIcons();

      watchId = navigator.geolocation.watchPosition(pos => {
        if (refreshBtn) refreshBtn.style.animation = "none";
        const lat = pos.coords.latitude, lng = pos.coords.longitude;
        markerUser.setLatLng([lat, lng]);
        const d = getDistance(lat, lng, latPos, lngPos);
        const isOk = d <= radius;

        if (isOk) {
          statusBox.style.background = "#eff6ff";
          statusBox.style.borderColor = "#bfdbfe";
          statusText.style.color = "#1d4ed8";
          statusText.innerHTML = '<i data-lucide="map-pin" style="width:16px;"></i> Dalam Radius POS';
          document.getElementById('btnMasuk').disabled = false;
          document.getElementById('btnPulang').disabled = false;
        } else {
          statusBox.style.background = "#fff5f5";
          statusBox.style.borderColor = "#fee2e2";
          statusText.style.color = "#ef4444";
          statusText.innerHTML = '<i data-lucide="map-pin-off" style="width:16px;"></i> Diluar Radius (' + Math.round(d - radius) + 'm)';
          document.getElementById('btnMasuk').disabled = true;
          document.getElementById('btnPulang').disabled = true;
        }
        lucide.createIcons();
      }, err => {
        if (refreshBtn) refreshBtn.style.animation = "none";
        console.error("GPS Error Code: " + err.code + " | Message: " + err.message);

        let msg = "Gagal lokasi.";
        if (err.code === 1) msg = "Izin GPS ditolak. Harap aktifkan di pengaturan.";
        else if (err.code === 2) msg = "Sinyal GPS tidak ditemukan. Pastikan GPS aktif.";
        else if (err.code === 3) msg = "Waktu pencarian habis. Coba lagi.";

        // Special check for Insecure Context
        if (location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
          msg = "Wajib HTTPS/SSL untuk fitur GPS.";
        }

        statusBox.style.background = "#fffbfa";
        statusBox.style.borderColor = "#fee2e2";
        statusText.style.color = "#ef4444";
        statusText.innerHTML = '<i data-lucide="alert-circle" style="width:16px;"></i> ' + msg;
        lucide.createIcons();
      }, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      });
    }
    detect();

    // CAMERA HANDLING
    let currentType = 'masuk';
    let stream = null;
    let currentFacingMode = 'user';
    const camCont = document.getElementById('camera-container');
    const video = document.getElementById('video');

    async function openCam(type) {
      currentType = type;
      // Default ke environment (belakang) jika laporan, user (depan) jika absensi
      currentFacingMode = (type === 'laporan') ? 'environment' : 'user';
      startCamera();
    }

    async function startCamera() {
      if (stream) {
        stream.getTracks().forEach(t => t.stop());
      }
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: currentFacingMode }
        });
        video.srcObject = stream;
        camCont.style.display = 'flex';
      } catch (e) {
        // Fallback jika mode spesifik ditolak (terutama di desktop)
        try {
          stream = await navigator.mediaDevices.getUserMedia({ video: true });
          video.srcObject = stream;
          camCont.style.display = 'flex';
        } catch (e2) {
          alert("Izin kamera ditolak atau tidak tersedia.");
        }
      }
    }

    function switchCamera() {
      currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
      startCamera();
    }

    document.getElementById('btnMasuk').onclick = () => openCam('masuk');
    document.getElementById('btnPulang').onclick = () => openCam('pulang');
    document.getElementById('btnOpenReportCam').onclick = () => openCam('laporan');

    document.getElementById('btnCloseCamera').onclick = () => {
      camCont.style.display = 'none';
      if (stream) stream.getTracks().forEach(t => t.stop());
    };

    // FILE HANDLING FOR GALLERY
    function handleFileSelect(input) {
      if (!input.files || !input.files[0]) return;

      const file = input.files[0];
      const reader = new FileReader();
      reader.onload = function (e) {
        const img = new Image();
        img.onload = function () {
          // Resize image logic
          const MAX_WIDTH = 800;
          let width = img.width;
          let height = img.height;

          if (width > MAX_WIDTH) {
            height = Math.round(height * (MAX_WIDTH / width));
            width = MAX_WIDTH;
          }

          const canvas = document.createElement('canvas');
          canvas.width = width;
          canvas.height = height;
          canvas.getContext('2d').drawImage(img, 0, 0, width, height);

          let fotoData = canvas.toDataURL('image/jpeg', 0.8);
          reportFotoData = fotoData.split(',')[1];

          document.getElementById('img-preview').src = 'data:image/jpeg;base64,' + reportFotoData;
          document.getElementById('lap-foto-preview').style.display = 'block';
          document.getElementById('btnOpenReportCam').style.display = 'none';
        };
        img.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    // VIEW SWITCHER
    function switchView(viewId, el) {
      document.querySelectorAll('.view-content').forEach(v => v.classList.remove('active'));
      document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

      document.getElementById('view-' + viewId).classList.add('active');
      el.classList.add('active');
    }

    let reportFotoData = null;
    function resetReportCam() {
      reportFotoData = null;
      document.getElementById('lap-foto-preview').style.display = 'none';
      document.getElementById('btnOpenReportCam').style.display = 'flex';
    }

    // Capture logic update
    document.getElementById('btnCapture').onclick = () => {
      // Scale down image to avoid huge base64 strings crashing the server
      const MAX_WIDTH = 640;
      let width = video.videoWidth;
      let height = video.videoHeight;

      if (width > MAX_WIDTH) {
        height = Math.round(height * (MAX_WIDTH / width));
        width = MAX_WIDTH;
      }

      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      canvas.getContext('2d').drawImage(video, 0, 0, width, height);
      // Use JPEG for huge size optimization ~90% smaller than PNG
      let fotoData = canvas.toDataURL('image/jpeg', 0.8);
      // Strip prefix to prevent WAF / ModSecurity from dropping the request
      fotoData = fotoData.split(',')[1];

      // Disable capture btn to prevent double click
      document.getElementById('btnCapture').style.pointerEvents = 'none';
      document.getElementById('btnCapture').style.opacity = '0.5';

      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
          // If reporting, just store the data and close camera
          if (currentType === 'laporan') {
            reportFotoData = fotoData;
            document.getElementById('img-preview').src = 'data:image/jpeg;base64,' + fotoData;
            document.getElementById('lap-foto-preview').style.display = 'block';
            document.getElementById('btnOpenReportCam').style.display = 'none';

            // Close camera
            camCont.style.display = 'none';
            if (stream) stream.getTracks().forEach(t => t.stop());

            // Reset capture button
            document.getElementById('btnCapture').style.pointerEvents = 'auto';
            document.getElementById('btnCapture').style.opacity = '1';
            return;
          }

          // IF ABSENSI - PROCEED AS USUAL
          fetch("proses_absen.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              type: currentType,
              latitude: pos.coords.latitude,
              longitude: pos.coords.longitude,
              foto: fotoData
            })
          }).then(async r => {
            const textOutput = await r.text();
            try {
              const res = JSON.parse(textOutput);
              alert(res.message);
              if (res.success) location.reload();
              else {
                document.getElementById('btnCapture').style.pointerEvents = 'auto';
                document.getElementById('btnCapture').style.opacity = '1';
              }
            } catch (e) {
              alert("Server Error (Bukan JSON): " + textOutput.substring(0, 100));
              console.log(textOutput);
              document.getElementById('btnCapture').style.pointerEvents = 'auto';
              document.getElementById('btnCapture').style.opacity = '1';
            }
          }).catch(err => {
            alert("Kesalahan jaringan: Foto gagal dikirim. Pastikan koneksi stabil.");
            document.getElementById('btnCapture').style.pointerEvents = 'auto';
            document.getElementById('btnCapture').style.opacity = '1';
          });
        }, err => {
          alert("Gagal mendapatkan lokasi for verifikasi.");
          document.getElementById('btnCapture').style.pointerEvents = 'auto';
          document.getElementById('btnCapture').style.opacity = '1';
        }, { enableHighAccuracy: true });
      } else {
        alert("Browser anda tidak mendukung GPS Geolocation.");
        document.getElementById('btnCapture').style.pointerEvents = 'auto';
        document.getElementById('btnCapture').style.opacity = '1';
      }
    };

    // SEND REPORT
    document.getElementById('btnKirimLaporan').onclick = function () {
      const deskripsi = document.getElementById('lap-deskripsi').value;
      if (!deskripsi) return alert("Mohon isi deskripsi kejadian.");
      if (!reportFotoData) return alert("Mohon ambil foto bukti kejadian.");

      this.disabled = true;
      this.innerHTML = '<i class="spin" data-lucide="loader-2"></i> Mengirim...';
      lucide.createIcons();

      navigator.geolocation.getCurrentPosition(pos => {
        fetch("proses_laporan.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            deskripsi: deskripsi,
            foto: reportFotoData,
            latitude: pos.coords.latitude,
            longitude: pos.coords.longitude
          })
        }).then(async r => {
          const res = await r.json();
          alert(res.message);
          if (res.success) location.reload();
          else {
            this.disabled = false;
            this.innerHTML = '<i data-lucide="send"></i> Kirim Laporan';
            lucide.createIcons();
          }
        }).catch(err => {
          alert("Gagal mengirim laporan. Periksa koneksi.");
          this.disabled = false;
          this.innerHTML = '<i data-lucide="send"></i> Kirim Laporan';
          lucide.createIcons();
        });
      }, err => {
        alert("Gagal mendapatkan lokasi.");
        this.disabled = false;
        this.innerHTML = '<i data-lucide="send"></i> Kirim Laporan';
        lucide.createIcons();
      }, { enableHighAccuracy: true });
    };

    // SERVICE WORKER REGISTRATION (PWA)
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js')
          .then(reg => console.log('Service Worker registered!', reg))
          .catch(err => console.log('Service Worker registration failed: ', err));
      });
    }
  </script>
</body>

</html>