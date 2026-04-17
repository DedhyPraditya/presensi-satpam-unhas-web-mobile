<?php 
session_start();
require 'koneksi.php';

if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: login.php");
    exit;
}

$msg = "";
$status = "success";

// UPDATE POS LOKASI
if(isset($_POST['save_pos'])){
    $id     = intval($_POST['id_pos']);
    $nama   = $_POST['nama_pos'];
    $lat    = (float)$_POST['lat_pos'];
    $lng    = (float)$_POST['lng_pos'];
    $radius = (int)$_POST['radius'];

    $stmt = $koneksi->prepare("UPDATE pos_lokasi SET nama_pos=?, latitude=?, longitude=?, radius=? WHERE id=?");
    $stmt->bind_param("sddii", $nama, $lat, $lng, $radius, $id);
    $stmt->execute();
    $msg = "Lokasi POS berhasil diperbarui.";
}

// TAMBAH POS LOKASI
if(isset($_POST['add_pos'])){
    $nama   = $_POST['nama_pos'];
    $lat    = (float)$_POST['lat_pos'];
    $lng    = (float)$_POST['lng_pos'];
    $radius = (int)$_POST['radius'];

    $stmt = $koneksi->prepare("INSERT INTO pos_lokasi (nama_pos, latitude, longitude, radius) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddi", $nama, $lat, $lng, $radius);
    $stmt->execute();
    $msg = "Lokasi POS baru berhasil ditambahkan.";
}

// HAPUS POS LOKASI
if(isset($_POST['delete_pos'])){
    $id = intval($_POST['id_pos']);
    $cek_count = $koneksi->query("SELECT COUNT(*) FROM pos_lokasi")->fetch_row()[0];
    if($cek_count > 1){
        $stmt = $koneksi->prepare("DELETE FROM pos_lokasi WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = "Lokasi POS berhasil dihapus.";
    } else {
        $msg = "Gagal: Minimal harus ada 1 lokasi POS dalam sistem.";
        $status = "error";
    }
}

// UPDATE JAM KERJA
if(isset($_POST['save_jam'])){
    $stmt = $koneksi->prepare("
        UPDATE pengaturan SET
            jam_masuk_non_shift_pagi=?,
            jam_pulang_non_shift_pagi=?,
            jam_masuk_shift_pagi=?,
            jam_pulang_shift_pagi=?,
            jam_masuk_shift_malam=?,
            jam_pulang_shift_malam=?
        WHERE id=1
    ");
    $stmt->bind_param("ssssss", 
        $_POST['jam_masuk_non_shift_pagi'],
        $_POST['jam_pulang_non_shift_pagi'],
        $_POST['jam_masuk_shift_pagi'],
        $_POST['jam_pulang_shift_pagi'],
        $_POST['jam_masuk_shift_malam'],
        $_POST['jam_pulang_shift_malam']
    );
    $stmt->execute();
    $msg = "Pengaturan jam kerja berhasil diperbarui.";
}

$pos_list = $koneksi->query("SELECT * FROM pos_lokasi ORDER BY id ASC");
$id_edit = $_GET['id'] ?? 1;
$pos_edit_q = $koneksi->query("SELECT * FROM pos_lokasi WHERE id=$id_edit");
$pos_edit = $pos_edit_q->fetch_assoc();

// Fallback jika ID tidak ditemukan
if(!$pos_edit){
    $pos_edit = $koneksi->query("SELECT * FROM pos_lokasi LIMIT 1")->fetch_assoc();
}

$jam = $koneksi->query("SELECT * FROM pengaturan WHERE id=1")->fetch_assoc();
?>
<?php
$page_title = "Konfigurasi Sistem";
$extra_css = ['https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'];
include 'layout/header.php';
?>

<style>
#map-preview, #map_mod {height:350px; border-radius:16px; margin:15px 0; border: 1px solid #e2e8f0; z-index: 1;}
.form-section { margin-bottom: 30px; }
.form-section h4 { margin-bottom: 15px; color: var(--primary); font-size: 16px; border-left: 4px solid var(--primary); padding-left: 12px; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px; }
@media (max-width: 600px) { .grid-2 { grid-template-columns: 1fr; } .card-grid { grid-template-columns: 1fr; } }
</style>
    <div class="page-header">
      <div class="page-title">
        <h2>Konfigurasi Sistem</h2>
        <p>Atur titik lokasi POS dan jadwal shift kerja</p>
      </div>
    </div>

    <?php if($msg): ?>
      <div class="card" style="background: #ecfdf5; border-color: #10b981; color: #166534; padding: 15px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
          <i data-lucide="check-circle" style="width: 20px;"></i>
          <span style="font-weight: 500;"><?= htmlspecialchars($msg) ?></span>
      </div>
    <?php endif; ?>

    <div class="card-grid">
      <!-- LOKASI POS JAGA OVERVIEW -->
      <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
            <div>
              <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <i data-lucide="map-pin" style="color: var(--primary);"></i> Daftar Pos Jaga
              </h3>
              <p style="font-size: 13px; color: var(--text-muted);">Daftar titik presensi yang terdaftar dalam sistem</p>
            </div>
            <button class="btn btn-primary" onclick="openPosModal()" style="padding: 8px 16px; font-size: 13px;">
              <i data-lucide="plus" style="width: 16px;"></i> Tambah
            </button>
          </div>
          
          <div class="data-list">
              <?php 
              $pos_list->data_seek(0); 
              while($p = $pos_list->fetch_assoc()): 
              ?>
              <div class="data-item">
                  <div class="data-item-info">
                      <h5><?= htmlspecialchars($p['nama_pos']) ?></h5>
                      <p><?= $p['latitude'] ?>, <?= $p['longitude'] ?> | Radius: <?= $p['radius'] ?>m</p>
                  </div>
                  <div class="action-btns">
                      <button class="icon-btn icon-btn-edit" title="Edit Lokasi" onclick='openPosModal(<?= json_encode($p) ?>)'>
                        <i data-lucide="edit-3" style="width: 16px;"></i>
                      </button>
                      <form method="post" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pos ini? Seluruh data yang terhubung mungkin akan terpengaruh.')">
                        <input type="hidden" name="id_pos" value="<?= $p['id'] ?>">
                        <button type="submit" name="delete_pos" class="icon-btn icon-btn-delete" title="Hapus Lokasi">
                          <i data-lucide="trash-2" style="width: 16px;"></i>
                        </button>
                      </form>
                  </div>
              </div>
              <?php endwhile; ?>
          </div>
      </div>

      <!-- JAM KERJA OVERVIEW -->
      <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
            <div>
              <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <i data-lucide="clock" style="color: var(--warning);"></i> Jadwal Shift Kerja
              </h3>
              <p style="font-size: 13px; color: var(--text-muted);">Target waktu masuk dan pulang aktif</p>
            </div>
            <button class="btn btn-primary" onclick="openJamModal()" style="padding: 8px 16px; font-size: 13px; background: #334155;">
              <i data-lucide="edit" style="width: 16px;"></i> Edit Jam
            </button>
          </div>

          <div class="data-list">
              <div class="data-item">
                  <div class="data-item-info">
                      <h5>Non-Shift (Pagi)</h5>
                      <p>Masuk: <?= substr($jam['jam_masuk_non_shift_pagi'],0,5) ?> - Pulang: <?= substr($jam['jam_pulang_non_shift_pagi'],0,5) ?></p>
                  </div>
                  <i data-lucide="sun" style="color: var(--warning); width: 20px;"></i>
              </div>
              <div class="data-item">
                  <div class="data-item-info">
                      <h5>Shift Pagi (12 Jam)</h5>
                      <p>Masuk: <?= substr($jam['jam_masuk_shift_pagi'],0,5) ?> - Pulang: <?= substr($jam['jam_pulang_shift_pagi'],0,5) ?></p>
                  </div>
                  <i data-lucide="sunrise" style="color: var(--success); width: 20px;"></i>
              </div>
              <div class="data-item">
                  <div class="data-item-info">
                      <h5>Shift Malam (12 Jam)</h5>
                      <p>Masuk: <?= substr($jam['jam_masuk_shift_malam'],0,5) ?> - Pulang: <?= substr($jam['jam_pulang_shift_malam'],0,5) ?></p>
                  </div>
                  <i data-lucide="moon" style="color: var(--primary); width: 20px;"></i>
              </div>
          </div>
      </div>
    </div>
<!-- MODAL POS JAGA (ADD/EDIT) -->
<div class="modal-overlay" id="posModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="posModalTitle" style="display: flex; align-items: center; gap: 10px; margin: 0;">
        <i data-lucide="plus-circle" style="color: var(--success);"></i> Tambah Pos Jaga Baru
      </h3>
      <div class="close-modal" onclick="closePosModal()">
        <i data-lucide="x"></i>
      </div>
    </div>
    <div class="modal-body">
      <form method="post">
          <input type="hidden" name="id_pos" id="pos_id">
          <div style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">NAMA LOKASI POS JAGA</label>
            <input class="input" name="nama_pos" id="pos_nama" style="width: 100%;" placeholder="Contoh: Pos Jaga Pintu Utama" required>
          </div>

          <div class="grid-2" style="margin-bottom: 20px;">
            <div>
              <label style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">LATITUDE</label>
              <input class="input" style="width: 100%;" id="lat_mod" name="lat_pos" value="-5.148600" required>
            </div>
            <div>
              <label style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">LONGITUDE</label>
              <input class="input" style="width: 100%;" id="lng_mod" name="lng_pos" value="119.432000" required>
            </div>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">RADIUS PRESENSI (METER)</label>
            <input class="input" style="width: 100%;" type="number" name="radius" id="pos_radius" value="50" required>
          </div>

          <div id="map_mod" style="height: 280px; border-radius: 16px; margin-bottom: 20px; border: 1px solid #e2e8f0; z-index: 1;"></div>
          <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px; text-align: center;">Klik pada peta atau geser marker untuk menyesuaikan lokasi.</p>

          <button class="btn btn-primary" id="posSubmitBtn" name="add_pos" style="width: 100%;">
            <i data-lucide="save"></i> Simpan Pos Jaga
          </button>
      </form>
    </div>
  </div>
<!-- MODAL JAM KERJA -->
<div class="modal-overlay" id="jamModal">
  <div class="modal-content" style="max-width: 700px;">
    <div class="modal-header">
      <h3 style="display: flex; align-items: center; gap: 10px; margin: 0;">
        <i data-lucide="clock" style="color: var(--warning);"></i> Pengaturan Jam Kerja Shift
      </h3>
      <div class="close-modal" onclick="closeJamModal()">
        <i data-lucide="x"></i>
      </div>
    </div>
    <div class="modal-body">
      <form method="post">
          <div class="form-section">
            <h4>Non-Shift (Senin - Jumat)</h4>
            <div class="grid-2">
              <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">JAM MASUK</label>
                <input class="input" type="time" name="jam_masuk_non_shift_pagi" value="<?= $jam['jam_masuk_non_shift_pagi'] ?>" style="width:100%">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">JAM PULANG</label>
                <input class="input" type="time" name="jam_pulang_non_shift_pagi" value="<?= $jam['jam_pulang_non_shift_pagi'] ?>" style="width:100%">
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Shift Pagi (07:00 - 19:00)</h4>
            <div class="grid-2">
              <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">JAM MASUK</label>
                <input class="input" type="time" name="jam_masuk_shift_pagi" value="<?= $jam['jam_masuk_shift_pagi'] ?>" style="width:100%">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">JAM PULANG</label>
                <input class="input" type="time" name="jam_pulang_shift_pagi" value="<?= $jam['jam_pulang_shift_pagi'] ?>" style="width:100%">
              </div>
            </div>
          </div>

          <div class="form-section">
            <h4>Shift Malam (19:00 - 07:00)</h4>
            <div class="grid-2">
              <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">JAM MASUK</label>
                <input class="input" type="time" name="jam_masuk_shift_malam" value="<?= $jam['jam_masuk_shift_malam'] ?>" style="width:100%">
              </div>
              <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">JAM PULANG</label>
                <input class="input" type="time" name="jam_pulang_shift_malam" value="<?= $jam['jam_pulang_shift_malam'] ?>" style="width:100%">
              </div>
            </div>
          </div>

          <button class="btn btn-primary" name="save_jam" style="width: 100%; margin-top: 10px; background: #334155;">
            <i data-lucide="check-circle"></i> Simpan Perubahan Jadwal Kerja
          </button>
      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Modal Pos Logic
function openPosModal(data = null) {
    const modal = document.getElementById('posModal');
    const title = document.getElementById('posModalTitle');
    const submitBtn = document.getElementById('posSubmitBtn');
    
    if(data) {
        title.innerHTML = '<i data-lucide="edit-3" style="color: var(--primary);"></i> Edit Pos Jaga';
        submitBtn.name = 'save_pos';
        submitBtn.style.background = 'var(--primary)';
        document.getElementById('pos_id').value = data.id;
        document.getElementById('pos_nama').value = data.nama_pos;
        document.getElementById('pos_radius').value = data.radius;
        document.getElementById('lat_mod').value = data.latitude;
        document.getElementById('lng_mod').value = data.longitude;
        markerMod.setLatLng([data.latitude, data.longitude]);
        mapMod.setView([data.latitude, data.longitude], 17);
    } else {
        title.innerHTML = '<i data-lucide="plus-circle" style="color: var(--success);"></i> Tambah Pos Jaga Baru';
        submitBtn.name = 'add_pos';
        submitBtn.style.background = 'var(--success)';
        document.getElementById('pos_id').value = '';
        document.getElementById('pos_nama').value = '';
        document.getElementById('pos_radius').value = '50';
        document.getElementById('lat_mod').value = '-5.148600';
        document.getElementById('lng_mod').value = '119.432000';
        markerMod.setLatLng([-5.1486, 119.4320]);
        mapMod.setView([-5.1486, 119.4320], 15);
    }
    
    modal.classList.add('active');
    lucide.createIcons();
    setTimeout(() => { mapMod.invalidateSize(); }, 300);
}

function closePosModal() { document.getElementById('posModal').classList.remove('active'); }

// Modal Jam Logic
function openJamModal() { document.getElementById('jamModal').classList.add('active'); }
function closeJamModal() { document.getElementById('jamModal').classList.remove('active'); }

// Auto-open if action=add
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'add') openPosModal();
}

// MAP IN MODAL
var mapMod = L.map('map_mod', {zoomControl: false}).setView([-5.1486, 119.4320], 15);
L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(mapMod);
var markerMod = L.marker([-5.1486, 119.4320], {draggable:true}).addTo(mapMod);

markerMod.on('dragend', function(){
    var c = markerMod.getLatLng();
    document.getElementById('lat_mod').value = c.lat.toFixed(6);
    document.getElementById('lng_mod').value = c.lng.toFixed(6);
});

mapMod.on('click', function(e) {
    markerMod.setLatLng(e.latlng);
    document.getElementById('lat_mod').value = e.latlng.lat.toFixed(6);
    document.getElementById('lng_mod').value = e.latlng.lng.toFixed(6);
});

document.getElementById('lat_mod').onchange = function(){ markerMod.setLatLng([this.value, document.getElementById('lng_mod').value]); mapMod.panTo([this.value, document.getElementById('lng_mod').value]); };
document.getElementById('lng_mod').onchange = function(){ markerMod.setLatLng([document.getElementById('lat_mod').value, this.value]); mapMod.panTo([document.getElementById('lat_mod').value, this.value]); };
</script>

<?php include 'layout/footer.php'; ?>
