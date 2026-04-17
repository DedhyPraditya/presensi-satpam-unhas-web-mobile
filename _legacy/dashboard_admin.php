<?php
require_once 'includes/helpers.php';
$page_title = "Ringkasan Dashboard";
$extra_css = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
include 'layout/header.php';

// ===== Ambil Pengaturan Shift =====
$cfg = $koneksi->query("SELECT * FROM pengaturan WHERE id=1")->fetch_assoc();
$shifts = [
    'non_shift_pagi'=>['masuk'=>$cfg['jam_masuk_non_shift_pagi'],'pulang'=>$cfg['jam_pulang_non_shift_pagi']],
    'shift_pagi'=>['masuk'=>$cfg['jam_masuk_shift_pagi'],'pulang'=>$cfg['jam_pulang_shift_pagi']],
    'shift_malam'=>['masuk'=>$cfg['jam_masuk_shift_malam'],'pulang'=>$cfg['jam_pulang_shift_malam']]
];

// ===== Hitung Statistik (Hari Ini) =====
$today = date('Y-m-d');
$total_satpam = $koneksi->query("SELECT COUNT(*) FROM users WHERE role='user' AND status='verified'")->fetch_row()[0];

// Ambil absen hari ini untuk hitung Hadir & Terlambat
$hadir_today = 0;
$terlambat_today = 0;
$q_stats = $koneksi->query("SELECT a.jam_masuk, u.jenis_kerja FROM absensi a JOIN users u ON a.user_id = u.id WHERE a.tanggal = '$today' AND a.jam_masuk IS NOT NULL");

while($rs = $q_stats->fetch_assoc()){
    $hadir_today++;
    // Tentukan shift menggunakan helper
    $st_dimainkan = tentukanShift($rs['jam_masuk'], $rs['jenis_kerja'], $cfg);
    
    // Tentukan key target
    $key_target = "jam_masuk_{$st_dimainkan}";
    $target_masuk = $cfg[$key_target] ?? '07:00:00';
    
    if(strtotime($rs['jam_masuk']) > strtotime($target_masuk)){
        $terlambat_today++;
    }
}
$belum_hadir = max(0, $total_satpam - $hadir_today);

// ===== Filter Range Tanggal (Default: Hari Ini) =====
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validasi tanggal
if(!strtotime($start_date)) $start_date = date('Y-m-01');
if(!strtotime($end_date)) $end_date = date('Y-m-t');

// Hitung period hari untuk tabel
$period = [];
$curr = $start_date;
while ($curr <= $end_date) {
    $period[] = $curr;
    $curr = date('Y-m-d', strtotime($curr . ' +1 day'));
}

// ===== Ambil Pos Lokasi =====
$pos = [];
$q = $koneksi->query("SELECT * FROM pos_lokasi ORDER BY id ASC");
while($r=$q->fetch_assoc()) $pos[$r['id']]=$r;

// ===== Ambil semua user kecuali admin =====
$users = $koneksi->query("SELECT * FROM users WHERE role != 'admin' ORDER BY nama ASC");
?>

<style>
#map-preview{height:380px;border-radius:16px;z-index: 1;}
</style>
  <div class="page-header">
    <div class="page-title">
      <h2>Laporan Absensi</h2>
      <p>Monitoring kehadiran satpam Universitas Hasanuddin</p>
    </div>
    <div class="card" style="margin-bottom: 0; padding: 10px 20px;">
      <form method="get" id="filterForm" style="display: flex; gap: 15px; align-items: center;">
        <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Periode:</span>
        <input type="date" name="start_date" value="<?= $start_date ?>" onchange="this.form.submit()" class="input" style="padding: 8px 12px; margin-bottom: 0; width: auto;">
        <i data-lucide="arrow-right" style="width: 16px; color: var(--text-muted);"></i>
        <input type="date" name="end_date" value="<?= $end_date ?>" onchange="this.form.submit()" class="input" style="padding: 8px 12px; margin-bottom: 0; width: auto;">
      </form>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="card stat-card">
      <div class="stat-icon" style="background: #e0f2fe; color: #0369a1;">
        <i data-lucide="users"></i>
      </div>
      <div class="stat-info">
        <h4>Total Satpam</h4>
        <div class="value"><?= $total_satpam ?></div>
      </div>
    </div>
    <div class="card stat-card">
      <div class="stat-icon" style="background: #dcfce7; color: #166534;">
        <i data-lucide="user-check"></i>
      </div>
      <div class="stat-info">
        <h4>Hadir Hari Ini</h4>
        <div class="value"><?= $hadir_today ?></div>
      </div>
    </div>
    <div class="card stat-card">
      <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">
        <i data-lucide="user-x"></i>
      </div>
      <div class="stat-info">
        <h4>Terlambat</h4>
        <div class="value"><?= $terlambat_today ?></div>
      </div>
    </div>
    <div class="card stat-card">
      <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
        <i data-lucide="clock"></i>
      </div>
      <div class="stat-info">
        <h4>Belum Hadir</h4>
        <div class="value"><?= $belum_hadir ?></div>
      </div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
    <!-- Peta -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="display: flex; align-items: center; gap: 10px;"><i data-lucide="map-pin" style="color: var(--primary);"></i> Titik Lokasi POS</h3>
        <span class="badge badge-info"><?= count($pos) ?> POS Aktif</span>
      </div>
      <div id="map-preview"></div>
    </div>

    <!-- Tabel -->
    <div class="card">
      <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;"><i data-lucide="clipboard-list" style="color: var(--primary);"></i> Laporan Absensi</h3>
      
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>Nama Personel</th>
              <th>Lokasi POS</th>
              <th>Tanggal</th>
              <th>Masuk</th>
              <th>Pulang</th>
              <th>Dokumentasi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $stmt_abs = $koneksi->prepare("SELECT * FROM absensi WHERE user_id=? AND tanggal BETWEEN ? AND ?");
            while($u = $users->fetch_assoc()):
                $uid = $u['id'];
                $absensi_user = [];
                
                $stmt_abs->bind_param("iss", $uid, $start_date, $end_date);
                $stmt_abs->execute();
                $q_abs = $stmt_abs->get_result();
                while($a = $q_abs->fetch_assoc()) $absensi_user[$a['tanggal']] = $a;

                foreach($period as $tgl):
                    $r = $absensi_user[$tgl] ?? [];
                    $jam_masuk = $r['jam_masuk'] ?? '-';
                    $jam_pulang = $r['jam_pulang'] ?? '-';

                    // Tentukan shift & target menggunakan helper baru
                    $st_masuk  = tentukanShift($jam_masuk, $u['jenis_kerja'], $cfg);
                    $st_pulang = tentukanShift($jam_pulang, $u['jenis_kerja'], $cfg);

                    $key_masuk  = "jam_masuk_{$st_masuk}";
                    $key_pulang = "jam_pulang_{$st_pulang}";

                    $target_masuk = $cfg[$key_masuk] ?? null;
                    $target_pulang = $cfg[$key_pulang] ?? null;
            ?>
            <tr>
              <td>
                <div style="font-weight: 600; color: var(--text-main);"><?= $u['nama'] ?></div>
                <div style="font-size: 12px; color: var(--text-muted);"><?= $u['nip'] ?></div>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 6px;">
                  <i data-lucide="map-pin" style="width: 14px; color: var(--text-muted);"></i>
                  <?= $pos[$u['id_pos']]['nama_pos'] ?? '<span class="text-muted">-</span>' ?>
                </div>
              </td>
              <td style="font-weight: 500;"><?= date('d M Y', strtotime($tgl)) ?></td>
              <td>
                <div style="font-weight: 700; margin-bottom: 4px;"><?= $jam_masuk ?></div>
                <?= cekStatus($target_masuk,$jam_masuk,'masuk') ?>
              </td>
              <td>
                <div style="font-weight: 700; margin-bottom: 4px;"><?= $jam_pulang ?></div>
                <?= cekStatus($target_pulang,$jam_pulang,'pulang') ?>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <?php if(!empty($r['foto_masuk'])): ?>
                      <img class="thumb" src="uploads/<?= $r['foto_masuk'] ?>" onclick="viewImage(this.src)" title="Klik untuk perbesar">
                    <?php else: ?>
                      <span class="text-muted">No Photo</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($r['foto_pulang'])): ?>
                      <img class="thumb" src="uploads/<?= $r['foto_pulang'] ?>" onclick="viewImage(this.src)" title="Klik untuk perbesar">
                  <?php endif; ?>
                  <?php if(empty($r['foto_masuk']) && empty($r['foto_pulang'])): ?>
                    <span style="color: #cbd5e1; font-size: 12px; font-style: italic;">No Photo</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize Map
var map = L.map('map-preview').setView([-5.1486, 119.4320], 14);
L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

<?php foreach($pos as $p): ?>
L.marker([<?= $p['latitude'] ?>, <?= $p['longitude'] ?>]).addTo(map).bindPopup("<b><?= addslashes($p['nama_pos']) ?></b>");
L.circle([<?= $p['latitude'] ?>, <?= $p['longitude'] ?>], {
  radius: <?= $p['radius'] ?>, 
  color: 'var(--primary)', 
  fillColor: 'var(--primary)',
  fillOpacity: 0.1,
  weight: 1
}).addTo(map);
<?php endforeach; ?>
</script>

<?php include 'layout/footer.php'; ?>
