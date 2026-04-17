<?php
session_start();
require 'koneksi.php';
header('Content-Type: application/json');
date_default_timezone_set('Asia/Makassar');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$userid = intval($_SESSION['user_id']);
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit;
}

$deskripsi = $koneksi->real_escape_string($input['deskripsi'] ?? '');
$foto = $input['foto'] ?? '';
$lat = isset($input['latitude']) ? floatval($input['latitude']) : null;
$lng = isset($input['longitude']) ? floatval($input['longitude']) : null;
$tanggal = date('Y-m-d');
$jam = date('H:i:s');

if (empty($deskripsi) || empty($foto)) {
    echo json_encode(['success' => false, 'message' => 'Deskripsi dan Foto wajib diisi.']);
    exit;
}

// Simpan foto
$filename = 'LAP_' . $userid . '_' . time() . '.jpg';
$path = __DIR__ . '/uploads/' . $filename;
if (!is_dir(__DIR__ . '/uploads')) {
    @mkdir(__DIR__ . '/uploads', 0777, true);
}

$base64 = preg_replace('#^data:image/\w+;base64,#i', '', $foto);
if (@file_put_contents($path, base64_decode($base64)) === false) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan foto laporan.']);
    exit;
}

$stmt = $koneksi->prepare("INSERT INTO laporan (user_id, tanggal, jam, deskripsi, foto, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssssd", $userid, $tanggal, $jam, $input['deskripsi'], $filename, $lat, $lng);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '✔ Laporan kejadian berhasil dikirim.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan laporan ke database.']);
}
