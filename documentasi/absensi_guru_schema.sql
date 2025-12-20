CREATE TABLE IF NOT EXISTS `absensi_guru` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nip` VARCHAR(50) NOT NULL,
  `tanggalabsen` DATE NOT NULL,
  `jammasuk` TIME NULL DEFAULT NULL,
  `jamkeluar` TIME NULL DEFAULT NULL,
  `durasijam` INT(11) NOT NULL DEFAULT 0,
  `durasimenit` INT(11) NOT NULL DEFAULT 0,
  `durasidetik` INT(11) NOT NULL DEFAULT 0,
  `status` ENUM('hadir', 'alpha', 'ijin', 'sakit') NOT NULL DEFAULT 'hadir',
  `keterangan` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_nip` (`nip`),
  INDEX `idx_tanggalabsen` (`tanggalabsen`),
  INDEX `idx_status` (`status`),
  INDEX `idx_nip_tanggal` (`nip`, `tanggalabsen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
