<?php
session_start();
require 'koneksi.php';

// Cek vendor autoload untuk Spreadsheet
$autoload = 'vendor/autoload.php';
if(file_exists($autoload)){
    require $autoload;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header('Location: login.php'); exit;
}

// Ambil filter dari form
$selected_user = $_GET['user_id'] ?? 'all';
$selected_pos  = $_GET['pos_id'] ?? 'all';
$start_date    = $_GET['start_date'] ?? date('Y-m-01');
$end_date      = $_GET['end_date'] ?? date('Y-m-t');

// Validasi tanggal
if(!strtotime($start_date)) $start_date = date('Y-m-01');
if(!strtotime($end_date)) $end_date = date('Y-m-t');

// Ambil list user dan pos untuk filter
$users_list = $koneksi->query("SELECT id,nama,nip FROM users WHERE role<>'admin' ORDER BY nama ASC");
$pos_list   = $koneksi->query("SELECT id,nama_pos FROM pos_lokasi ORDER BY nama_pos ASC");

// Ambil pengaturan shift
$cfg = $koneksi->query("SELECT * FROM pengaturan WHERE id=1")->fetch_assoc();
$shifts = [
    'shift_pagi'=>['masuk'=>$cfg['jam_masuk_shift_pagi'] ?? '07:00:00','pulang'=>$cfg['jam_pulang_shift_pagi'] ?? '19:00:00'],
    'shift_malam'=>['masuk'=>$cfg['jam_masuk_shift_malam'] ?? '19:00:00','pulang'=>$cfg['jam_pulang_shift_malam'] ?? '07:00:00']
];

// Fungsi excel sederhana jika PhpSpreadsheet tidak ada (CSV mode)
function exportCSV($koneksi, $users, $period, $shifts, $filename){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Nama', 'NIP', 'Tipe Kerja', 'Pos Jaga', 'Tanggal', 'Jam Masuk', 'Jam Pulang']);
    
    while($u=$users->fetch_assoc()){
        $uid = $u['id'];
        $absensi_user = [];
        $q_abs = $koneksi->query("SELECT * FROM absensi WHERE user_id=$uid AND tanggal BETWEEN '".$_GET['start_date']."' AND '".$_GET['end_date']."'");
        while($a=$q_abs->fetch_assoc()) $absensi_user[$a['tanggal']]=$a;

        $pos_nama = '-';
        if($u['id_pos']){
            $pq = $koneksi->query("SELECT nama_pos FROM pos_lokasi WHERE id=".$u['id_pos']);
            if($pq && $pr = $pq->fetch_assoc()) $pos_nama = $pr['nama_pos'];
        }

        foreach($period as $tgl){
            $r = $absensi_user[$tgl] ?? [];
            fputcsv($output, [
                $u['nama'], 
                $u['nip'], 
                str_replace('_', ' ', $u['jenis_kerja']),
                $pos_nama, 
                $tgl, 
                $r['jam_masuk']??'-', 
                $r['jam_pulang']??'-'
            ]);
        }
    }
    fclose($output);
    exit;
}

if(isset($_GET['export']) && $_GET['export']=='1'){
    $user_filter = ($selected_user=='all') ? "" : "AND id=".intval($selected_user);
    $pos_filter  = ($selected_pos=='all') ? "" : "AND id_pos=".intval($selected_pos);
    $users = $koneksi->query("SELECT * FROM users WHERE role<>'admin' $user_filter $pos_filter ORDER BY nama ASC");

    $period = [];
    $begin = new DateTime($start_date);
    $end = new DateTime($end_date);
    $end->modify('+1 day');
    foreach(new DatePeriod($begin,new DateInterval('P1D'),$end) as $date){
        $period[] = $date->format("Y-m-d");
    }

    $filename = "Laporan_Absensi_".$start_date."_".$end_date;

    if(!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')){
        exportCSV($koneksi, $users, $period, $shifts, $filename);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray(['Nama','NIP','Tipe Kerja','Pos Jaga','Tanggal','Jam Masuk','Jam Pulang'], NULL, 'A1');
    
    $rowNum = 2;
    while($u=$users->fetch_assoc()){
        $uid = $u['id'];
        $q_abs = $koneksi->query("SELECT * FROM absensi WHERE user_id=$uid AND tanggal BETWEEN '$start_date' AND '$end_date'");
        $abs = []; while($a=$q_abs->fetch_assoc()) $abs[$a['tanggal']]=$a;
        
        $pos_name = '-';
        if($u['id_pos']){
            $pr = $koneksi->query("SELECT nama_pos FROM pos_lokasi WHERE id=".$u['id_pos'])->fetch_assoc();
            if($pr) $pos_name = $pr['nama_pos'];
        }

        foreach($period as $tgl){
            $r = $abs[$tgl] ?? [];
            $sheet->setCellValue("A$rowNum", $u['nama']);
            $sheet->setCellValue("B$rowNum", $u['nip']);
            $sheet->setCellValue("C$rowNum", str_replace('_', ' ', $u['jenis_kerja']));
            $sheet->setCellValue("D$rowNum", $pos_name);
            $sheet->setCellValue("E$rowNum", $tgl);
            $sheet->setCellValue("F$rowNum", $r['jam_masuk'] ?? '-');
            $sheet->setCellValue("G$rowNum", $r['jam_pulang'] ?? '-');
            $rowNum++;
        }
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
<?php
$page_title = "Ekspor Laporan";
include 'layout/header.php';
?>

<div class="page-header">
    <div class="page-title">
        <h2>Unduh Laporan Presensi</h2>
        <p>Ekspor data kehadiran anggota satpam ke format Excel / CSV</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <h3 style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; color: var(--primary);">
        <i data-lucide="file-spreadsheet"></i> Konfigurasi Laporan
    </h3>
    <form method="get">
        <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Pilih Anggota Satpam</label>
        <select class="input" name="user_id" style="margin-bottom: 15px;">
            <option value="all">Semua Anggota</option>
            <?php while($u=$users_list->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>" <?= ($selected_user==$u['id'])?'selected':'' ?>><?= htmlspecialchars($u['nama']) ?> (<?= $u['nip'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Pilih POS Jaga</label>
        <select class="input" name="pos_id" style="margin-bottom: 15px;">
            <option value="all">Semua POS Jaga</option>
            <?php while($p=$pos_list->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" <?= ($selected_pos==$p['id'])?'selected':'' ?>><?= htmlspecialchars($p['nama_pos']) ?></option>
            <?php endwhile; ?>
        </select>

        <div style="display: flex; gap: 15px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Dari Tanggal</label>
                <input class="input" type="date" name="start_date" value="<?= $start_date ?>">
            </div>
            <div style="flex: 1;">
                <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Sampai Tanggal</label>
                <input class="input" type="date" name="end_date" value="<?= $end_date ?>">
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="preview" value="1" class="btn" style="flex: 1; background: #f1f5f9; color: #475569;">
                <i data-lucide="eye"></i> Preview
            </button>
            <button type="submit" name="export" value="1" class="btn btn-primary" style="flex: 1; background: var(--success);">
                <i data-lucide="download"></i> Download Excel
            </button>
        </div>
    </form>
</div>

<?php
// Logic Preview
if (isset($_GET['preview'])):
    $user_filter = ($selected_user == 'all') ? "" : "AND id=" . intval($selected_user);
    $pos_filter  = ($selected_pos == 'all') ? "" : "AND id_pos=" . intval($selected_pos);
    $preview_users = $koneksi->query("SELECT * FROM users WHERE role<>'admin' $user_filter $pos_filter ORDER BY nama ASC");

    $period = [];
    $begin = new DateTime($start_date);
    $end = new DateTime($end_date);
    $end->modify('+1 day');
    foreach (new DatePeriod($begin, new DateInterval('P1D'), $end) as $date) {
        $period[] = $date->format("Y-m-d");
    }
?>

<?php if ($preview_users && $preview_users->num_rows > 0): ?>
    <div class="card" style="margin-top: 30px;">
        <h3 style="margin-bottom: 20px; font-size: 16px;">Pratinjau Data (Preview)</h3>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Satpam</th>
                        <th>NIP</th>
                        <th>Tipe Kerja</th>
                        <th>Pos Jaga</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    while ($u = $preview_users->fetch_assoc()): 
                        $uid = $u['id'];
                        $q_abs = $koneksi->query("SELECT * FROM absensi WHERE user_id=$uid AND tanggal BETWEEN '$start_date' AND '$end_date'");
                        $abs = []; while ($a = $q_abs->fetch_assoc()) $abs[$a['tanggal']] = $a;
                        
                        $pos_name = '-';
                        if ($u['id_pos']) {
                            $pr = $koneksi->query("SELECT nama_pos FROM pos_lokasi WHERE id=" . $u['id_pos'])->fetch_assoc();
                            if ($pr) $pos_name = $pr['nama_pos'];
                        }

                        foreach ($period as $tgl):
                            $r = $abs[$tgl] ?? [];
                    ?>
                        <tr>
                            <td><div style="font-weight: 600;"><?= htmlspecialchars($u['nama']) ?></div></td>
                            <td class="text-muted"><?= $u['nip'] ?></td>
                            <td>
                                <span class="badge badge-info" style="text-transform: capitalize;">
                                    <?= str_replace('_', ' ', $u['jenis_kerja']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($pos_name) ?></td>
                            <td><?= date('d/m/y', strtotime($tgl)) ?></td>
                            <td style="font-weight: 600; color: var(--primary);"><?= $r['jam_masuk'] ?? '-' ?></td>
                            <td style="font-weight: 600; color: var(--accent);"><?= $r['jam_pulang'] ?? '-' ?></td>
                        </tr>
                    <?php endforeach; endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; // End check users ?>
<?php endif; // End check preview button ?>

<?php include 'layout/footer.php'; ?>
