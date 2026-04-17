-- Database Production: absensi_satpam_unhas
-- Unified Schema for Production

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+08:00";

-- 1. Tabel pos_lokasi (Menyatukan pengaturan_pos, pos_lokasi, dll)
CREATE TABLE IF NOT EXISTS `pos_lokasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pos` varchar(255) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `radius` int(11) NOT NULL DEFAULT 50,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data Awal POS
INSERT INTO `pos_lokasi` (`id`, `nama_pos`, `latitude`, `longitude`, `radius`) VALUES
(1, 'Pos Utama UNHAS', -5.148600, 119.432000, 50),
(2, 'Pos Pintu 1', -5.132500, 119.487000, 50);

-- 2. Tabel pengaturan (Jam Kerja)
CREATE TABLE IF NOT EXISTS `pengaturan` (
  `id` int(11) NOT NULL DEFAULT 1,
  `jam_masuk_non_shift_pagi` time DEFAULT '07:30:00',
  `jam_pulang_non_shift_pagi` time DEFAULT '17:00:00',
  `jam_masuk_shift_pagi` time DEFAULT '07:00:00',
  `jam_pulang_shift_pagi` time DEFAULT '19:00:00',
  `jam_masuk_shift_malam` time DEFAULT '19:00:00',
  `jam_pulang_shift_malam` time DEFAULT '07:00:00',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pengaturan` (`id`) VALUES (1) ON DUPLICATE KEY UPDATE id=1;

-- 3. Tabel users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `jenis_kerja` enum('non_shift','shift') NOT NULL DEFAULT 'non_shift',
  `status` enum('pending','verified') NOT NULL DEFAULT 'pending',
  `id_pos` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  KEY `id_pos` (`id_pos`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_pos`) REFERENCES `pos_lokasi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Default: admin / admin
INSERT INTO `users` (`nama`, `nip`, `password`, `role`, `jenis_kerja`, `status`) VALUES
('Admin UNHAS', 'admin', '$2y$10$I/DS0oPUR04eVkYh1H6ROOhMXtVJD2RQp43NsMAL5uZTirfnDWRVa', 'admin', 'shift', 'verified');

-- 4. Tabel absensi (Unified Table)
CREATE TABLE IF NOT EXISTS `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `ceklog_masuk` datetime DEFAULT NULL,
  `ceklog_pulang` datetime DEFAULT NULL,
  `jenis_kerja` enum('non_shift_pagi','shift_pagi','shift_malam') NOT NULL DEFAULT 'non_shift_pagi',
  `terlambat` varchar(10) DEFAULT NULL,
  `cepat_pulang` varchar(10) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `foto_masuk` varchar(255) DEFAULT NULL,
  `foto_pulang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
