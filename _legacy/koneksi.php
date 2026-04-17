<?php
/* 
KONEKSI DATABASE
----------------
Gunakan settingan ini untuk aaPanel
*/

// $host = "localhost";
// $user = "sql_satpam_unhas_madignet_cloud";
// $pass = "658f78916ed2c8";
// $db = "sql_satpam_unhas_madignet_cloud";


//Gunakan settingan ini untuk LARAGON lokal (Hapus komentar jika ingin pakai lokal)
$host = "localhost";
$user = "root";
$pass = "";
$db = "absensi_unhas";


$koneksi = new mysqli($host, $user, $pass, $db);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

$koneksi->set_charset("utf8mb4");

// Set Timezone (PHP & MySQL)
date_default_timezone_set('Asia/Makassar');
$koneksi->query("SET time_zone = '+08:00'");
?>