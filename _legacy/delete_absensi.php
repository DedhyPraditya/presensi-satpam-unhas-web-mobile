<?php
require 'koneksi.php';

// Set JSON agar fetch bisa terbaca
header("Content-Type: application/json");

// Baca input JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Pastikan ID ada
if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'msg' => 'ID kosong']);
    exit;
}

$id = intval($data['id']);

// Eksekusi delete
$stmt = $koneksi->prepare("DELETE FROM absensi WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Query gagal']);
}

$stmt->close();
?>
