<?php
// create_admin.php  (jalankan sekali)
require 'koneksi.php';

// Data admin baru (array)
$admins = [
    [
        'nip' => 'Admin1',
        'nama' => 'Admin Pertama',
        'password' => 'harunita19',
        'jenis_kerja' => 'non_shift'
    ],
    [
        'nip' => 'Admin2',
        'nama' => 'Admin Kedua',
        'password' => 'aris123',
        'jenis_kerja' => 'non_shift'
    ]
];

foreach ($admins as $admin) {
    $nip = $admin['nip'];
    $nama = $admin['nama'];
    $pass_plain = $admin['password'];
    $jenis_kerja = $admin['jenis_kerja'];

    // Cek apakah admin sudah ada
    $q = $koneksi->query("SELECT id FROM users WHERE nip='$nip' LIMIT 1");
    if ($q && $q->num_rows > 0) {
        echo "Admin dengan NIP $nip sudah ada.<br>";
        continue;
    }

    // Hash password
    $hash = password_hash($pass_plain, PASSWORD_DEFAULT);

    // Insert admin
    $ins = $koneksi->query("INSERT INTO users (nama,nip,password,role,status,jenis_kerja) VALUES ('".$koneksi->real_escape_string($nama)."','$nip','".$koneksi->real_escape_string($hash)."','admin','verified','$jenis_kerja')");
    if ($ins) {
        echo "Admin berhasil dibuat.<br>NIP: $nip<br>Password: $pass_plain<br><br>";
    } else {
        echo "Gagal membuat admin $nip: " . $koneksi->error . "<br>";
    }
}

echo "Selesai. Hapus file create_admin.php sekarang untuk keamanan.";
?>
