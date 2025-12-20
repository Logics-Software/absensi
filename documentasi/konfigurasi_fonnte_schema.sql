-- Table untuk menyimpan konfigurasi Fonnte WhatsApp API
-- Hanya 1 record yang bisa di-update (seperti table konfigurasi)

CREATE TABLE IF NOT EXISTS `konfigurasi_fonnte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(255) DEFAULT NULL,
  `api_url` varchar(255) DEFAULT 'https://api.fonnte.com',
  `device_id` varchar(50) DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default record (kosong, akan diisi melalui UI)
INSERT INTO `konfigurasi_fonnte` (`id`, `api_key`, `api_url`, `device_id`, `webhook_url`) 
VALUES (1, NULL, 'https://api.fonnte.com', NULL, NULL)
ON DUPLICATE KEY UPDATE id=id;

