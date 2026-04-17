<?php
session_start();
require 'koneksi.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Makassar');

// Cek login
if(!isset($_SESSION['user_id'])){
    echo json_encode(['success'=>false,'message'=>'Silakan login terlebih dahulu.']);
    exit;
}

$userid = intval($_SESSION['user_id']);
$tanggal = date('Y-m-d');

// Ambil input JSON
$input = json_decode(file_get_contents("php://input"), true);
if(!$input){
    echo json_encode(['success'=>false,'message'=>'JSON kosong / tidak terbaca']);
    exit;
}

$type = $input['type'] ?? '';
$lat  = isset($input['latitude']) ? floatval($input['latitude']) : null;
$lng  = isset($input['longitude']) ? floatval($input['longitude']) : null;
$foto = $input['foto'] ?? '';
$jamKlik = $input['jam'] ?? date('H:i:s');
$datetimeKlik = date('Y-m-d H:i:s');

if($type==='' || $lat===null || $lng===null || $foto===''){
    echo json_encode(['success'=>false,'message'=>'Data tidak lengkap']);
    exit;
}

// Simpan foto
$filename = $userid.'_'.$type.'_'.time().'.jpg';
$path = __DIR__.'/uploads/'.$filename;
if(!is_dir(__DIR__.'/uploads')) {
    @mkdir(__DIR__.'/uploads',0777,true);
}
// Bersihkan data jika masih ada prefix
$base64 = preg_replace('#^data:image/\w+;base64,#i','',$foto);

if (@file_put_contents($path, base64_decode($base64)) === false) {
    echo json_encode(['success'=>false,'message'=>'Gagal menyimpan foto. Cek izin akses (chmod) folder uploads di aaPanel!']);
    exit;
}

// Ambil data user & pengaturan dengan Prepared Statement
$stmt_u = $koneksi->prepare("SELECT jenis_kerja FROM users WHERE id=?");
$stmt_u->bind_param("i", $userid);
$stmt_u->execute();
$u = $stmt_u->get_result()->fetch_assoc();
$userJenis = $u['jenis_kerja'] ?? 'non_shift';

$pengaturan = $koneksi->query("SELECT * FROM pengaturan LIMIT 1")->fetch_assoc();

// === KALKULASI SHIFT DAN TERLAMBAT (Menggunakan Helper) ===
require_once 'includes/helpers.php';
$shiftDimainkan = tentukanShift($jamKlik, $userJenis, $pengaturan);

// Ambil batas jam dari pengaturan berdasarkan shift yang dimainkan
$key_masuk  = ($userJenis == 'non_shift') ? 'jam_masuk_non_shift_pagi' : "jam_masuk_{$shiftDimainkan}";
$key_pulang = ($userJenis == 'non_shift') ? 'jam_pulang_non_shift_pagi' : "jam_pulang_{$shiftDimainkan}";

$batas_masuk  = $pengaturan[$key_masuk];
$batas_pulang = $pengaturan[$key_pulang];

$telat = null;
$cepat = null;

if($type == 'masuk'){
    $telat = (strtotime($jamKlik) > strtotime($batas_masuk)) ? 'Ya' : 'Tidak';
} else {
    $cepat = (strtotime($jamKlik) < strtotime($batas_pulang)) ? 'Ya' : 'Tidak';
}
// ======================================

// Cek absensi hari ini (Tabel Unified: absensi)
$q = $koneksi->query("SELECT * FROM absensi WHERE user_id=$userid AND tanggal='$tanggal' LIMIT 1");

if($q->num_rows===0){
    if($type!=='masuk'){
        echo json_encode(['success'=>false,'message'=>'Anda belum ceklok masuk.']);
        exit;
    }

    // Insert absensi (Unified Table)
    $stmt = $koneksi->prepare("INSERT INTO absensi 
        (user_id, tanggal, jam_masuk, ceklog_masuk, latitude, longitude, foto_masuk, jenis_kerja, terlambat)
        VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("isssddsss",$userid,$tanggal,$jamKlik,$datetimeKlik,$lat,$lng,$filename,$shiftDimainkan,$telat);
    
    if($stmt->execute()){
        echo json_encode(['success'=>true,'message'=>'✔ Ceklok MASUK berhasil.']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Gagal menyimpan data ke database.']);
    }
    exit;
}else{
    if($type!=='pulang'){
        echo json_encode(['success'=>false,'message'=>'Anda sudah ceklok masuk hari ini.']);
        exit;
    }

    $row = $q->fetch_assoc();
    $id_absen = $row['id'];

    if(!empty($row['jam_pulang'])){
        echo json_encode(['success'=>false,'message'=>'Anda sudah ceklok pulang hari ini.']);
        exit;
    }

    // Update absensi (Unified Table)
    $stmt = $koneksi->prepare("UPDATE absensi 
        SET jam_pulang=?, ceklog_pulang=?, foto_pulang=?, latitude=?, longitude=?, cepat_pulang=? WHERE id=?");
    $stmt->bind_param("sssddsi",$jamKlik,$datetimeKlik,$filename,$lat,$lng,$cepat,$id_absen);
    
    if($stmt->execute()){
        echo json_encode(['success'=>true,'message'=>'✔ Ceklok PULANG berhasil.']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Gagal memperbarui data absensi.']);
    }
    exit;
}
?>
