<?php
require 'koneksi.php';
$sql = "CREATE TABLE IF NOT EXISTS laporan (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  tanggal date NOT NULL,
  jam time NOT NULL,
  deskripsi text NOT NULL,
  foto varchar(255) DEFAULT NULL,
  latitude double DEFAULT NULL,
  longitude double DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($koneksi->query($sql)) {
    echo "Table 'laporan' created successfully.";
} else {
    echo "Error creating table: " . $koneksi->error;
}
unlink(__FILE__);
