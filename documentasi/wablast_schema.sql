-- Template untuk pesan WA
CREATE TABLE IF NOT EXISTS `wa_templates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `kategori` VARCHAR(50) NULL DEFAULT NULL COMMENT 'absensi, kalender, umum, dll',
  `pesan` TEXT NOT NULL,
  `variabel` TEXT NULL DEFAULT NULL COMMENT 'JSON array variabel yang bisa digunakan, contoh: ["nama", "tanggal"]',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_kategori` (`kategori`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign/Blast
CREATE TABLE IF NOT EXISTS `wa_campaigns` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(200) NOT NULL,
  `template_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `pesan` TEXT NOT NULL,
  `tipe_recipient` ENUM('siswa', 'wali', 'guru', 'custom') NOT NULL DEFAULT 'custom',
  `total_recipient` INT(11) NOT NULL DEFAULT 0,
  `total_sent` INT(11) NOT NULL DEFAULT 0,
  `total_delivered` INT(11) NOT NULL DEFAULT 0,
  `total_failed` INT(11) NOT NULL DEFAULT 0,
  `status` ENUM('draft', 'scheduled', 'sending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'draft',
  `scheduled_at` DATETIME NULL DEFAULT NULL,
  `sent_at` DATETIME NULL DEFAULT NULL,
  `created_by` INT(11) UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_template_id` (`template_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_by` (`created_by`),
  INDEX `idx_scheduled_at` (`scheduled_at`),
  FOREIGN KEY (`template_id`) REFERENCES `wa_templates` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detail pesan yang dikirim (log)
CREATE TABLE IF NOT EXISTS `wa_messages` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `recipient_type` ENUM('siswa', 'wali', 'guru', 'custom') NOT NULL,
  `recipient_id` INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'ID dari mastersiswa, masterguru, atau custom',
  `nomor_hp` VARCHAR(20) NOT NULL,
  `nama` VARCHAR(200) NULL DEFAULT NULL,
  `pesan` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'delivered', 'read', 'failed') NOT NULL DEFAULT 'pending',
  `status_message` VARCHAR(255) NULL DEFAULT NULL,
  `fonnte_message_id` VARCHAR(100) NULL DEFAULT NULL,
  `error_message` TEXT NULL DEFAULT NULL,
  `sent_at` DATETIME NULL DEFAULT NULL,
  `delivered_at` DATETIME NULL DEFAULT NULL,
  `read_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_campaign_id` (`campaign_id`),
  INDEX `idx_recipient` (`recipient_type`, `recipient_id`),
  INDEX `idx_nomor_hp` (`nomor_hp`),
  INDEX `idx_status` (`status`),
  INDEX `idx_sent_at` (`sent_at`),
  FOREIGN KEY (`campaign_id`) REFERENCES `wa_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO `wa_templates` (`nama`, `kategori`, `pesan`, `variabel`, `is_active`) VALUES
('Notifikasi Absensi Siswa', 'absensi', 'Yth. Orang Tua/Wali *{{nama}}*\n\nAnak Anda *{{nama_siswa}}* pada tanggal *{{tanggal}}* dengan status *{{status}}*.\n\nJam Masuk: {{jam_masuk}}\nJam Pulang: {{jam_keluar}}\n\nTerima kasih.', '["nama", "nama_siswa", "tanggal", "status", "jam_masuk", "jam_keluar"]', 1),
('Reminder Kalender Akademik', 'kalender', 'Yth. Orang Tua/Wali *{{nama}}*\n\nReminder: Besok tanggal *{{tanggal}}* adalah hari *{{status}}*.\n\nJam Masuk: {{jam_masuk}}\nJam Pulang: {{jam_keluar}}\n\nMohon persiapkan anak Anda untuk kehadiran tepat waktu.\n\nTerima kasih.', '["nama", "tanggal", "status", "jam_masuk", "jam_keluar"]', 1),
('Pesan Umum', 'umum', 'Yth. *{{nama}}*\n\n{{pesan}}\n\nTerima kasih.', '["nama", "pesan"]', 1);

-- Update existing templates to change "Jam Keluar:" to "Jam Pulang:"
UPDATE `wa_templates` 
SET `pesan` = REPLACE(`pesan`, 'Jam Keluar:', 'Jam Pulang:')
WHERE `pesan` LIKE '%Jam Keluar:%';

