<?php
session_start();
require 'koneksi.php';
header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role']!=='admin'){
    echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$start_date = $data['start_date'] ?? null;
$end_date   = $data['end_date'] ?? null;

if(!$start_date || !$end_date){
    echo json_encode(['success'=>false,'message'=>'Tanggal tidak valid']); exit;
}

$stmt = $koneksi->prepare("DELETE FROM absensi WHERE tanggal BETWEEN ? AND ?");
$stmt->bind_param("ss",$start_date,$end_date);
if($stmt->execute()){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'message'=>'Gagal menghapus']);
}
$stmt->close();
?>
