-- Database Backup: full_backup_2025-11-28_03-05-05.sql
-- Generated: 2025-11-28 03:05:05
-- Database: db_team9_tabungan

-- Table: transaksi
DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_siswa` varchar(100) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_transaksi` enum('masuk','keluar') NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1591 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for table: transaksi
INSERT INTO `transaksi` VALUES ('1336', 'Ahmad Rizki', '2024-01-15', 'masuk', '50000.00', 'Uang jajan januari');
INSERT INTO `transaksi` VALUES ('1337', 'Siti Nurhaliza', '2024-01-16', 'masuk', '75000.00', 'Tabungan bulanan');
INSERT INTO `transaksi` VALUES ('1338', 'Budi Santoso', '2024-01-17', 'keluar', '25000.00', 'Beli alat tulis kelas');
INSERT INTO `transaksi` VALUES ('1339', 'Dewi Lestari', '2024-01-18', 'masuk', '100000.00', 'Uang raport');
INSERT INTO `transaksi` VALUES ('1340', 'Rudi Hermawan', '2024-01-19', 'keluar', '15000.00', 'Beli sapu pel');

