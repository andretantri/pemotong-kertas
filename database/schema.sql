-- ============================================
-- DATABASE SCHEMA
-- HMI Pemotong Kertas Roll - ESP32
-- ============================================

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `hmi_pemotong_kertas` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `hmi_pemotong_kertas`;

-- ============================================
-- TABEL: users
-- Menyimpan data user/admin
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Username: admin
-- Password: admin123
-- Note: Jika password tidak bekerja, jalankan reset_password.php untuk generate hash baru
INSERT INTO `users` (`username`, `password`, `nama`) 
VALUES ('admin', '$2y$10$znvVkLcd2XK.ut.Od76AM.aXT2ucN2VXaz4fBazwxtjjXklB.QYU.', 'Administrator');

-- ============================================
-- TABEL: job_potong
-- Menyimpan data pekerjaan potong
-- ============================================
CREATE TABLE IF NOT EXISTS `job_potong` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `panjang_mm` INT(11) NOT NULL COMMENT 'Panjang potong dalam mm',
  `jumlah_potong` INT(11) NOT NULL COMMENT 'Jumlah potong yang diminta',
  `status` ENUM('READY', 'RUNNING', 'STOPPED', 'DONE', 'ERROR') NOT NULL DEFAULT 'READY',
  `potong_selesai` INT(11) NOT NULL DEFAULT 0 COMMENT 'Jumlah potong yang sudah selesai',
  `user_id` INT(11) UNSIGNED NOT NULL,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `stopped_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_job_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABEL: log_potong
-- Menyimpan log detail setiap potong
-- ============================================
CREATE TABLE IF NOT EXISTS `log_potong` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` INT(11) UNSIGNED NOT NULL,
  `potong_ke` INT(11) NOT NULL COMMENT 'Urutan potong ke berapa',
  `panjang_mm` INT(11) NOT NULL,
  `status` ENUM('SUCCESS', 'FAILED', 'SKIPPED') NOT NULL DEFAULT 'SUCCESS',
  `waktu_potong` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`),
  KEY `waktu_potong` (`waktu_potong`),
  CONSTRAINT `fk_log_job` FOREIGN KEY (`job_id`) REFERENCES `job_potong` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INDEXES untuk optimasi query
-- ============================================
-- Index sudah dibuat di CREATE TABLE di atas

-- ============================================
-- TABEL: machine_config
-- Menyimpan konfigurasi mesin (roller, speed, dll)
-- ============================================
CREATE TABLE IF NOT EXISTS `machine_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `roller_diameter_mm` INT(11) NOT NULL DEFAULT 17 COMMENT 'Diameter roller dalam mm',
  `pull_distance_cm` INT(11) NOT NULL DEFAULT 5 COMMENT 'Jarak penarik dalam cm setiap siklus',
  `pull_delay_ms` INT(11) NOT NULL DEFAULT 500 COMMENT 'Delay penarik (milidetik)',
  `cut_delay_ms` INT(11) NOT NULL DEFAULT 500 COMMENT 'Delay pemotong (milidetik)',
  `step_delay_us` INT(11) NOT NULL DEFAULT 1200 COMMENT 'Step delay motor dalam mikrosecond',
  `pull_pause_ms` INT(11) NOT NULL DEFAULT 1000 COMMENT 'Pause setelah penarik selesai',
  `cut_pause_ms` INT(11) NOT NULL DEFAULT 2000 COMMENT 'Pause setelah pemotong selesai',
  `updated_by` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default machine config
INSERT INTO `machine_config` (`id`, `roller_diameter_mm`, `pull_distance_cm`, `pull_delay_ms`, `cut_delay_ms`, `step_delay_us`, `pull_pause_ms`, `cut_pause_ms`) 
VALUES (1, 17, 5, 500, 500, 1200, 1000, 2000);

-- ============================================
-- VIEW: v_job_summary
-- View untuk summary pekerjaan
-- ============================================
CREATE OR REPLACE VIEW `v_job_summary` AS
SELECT 
    j.id,
    j.panjang_mm,
    j.jumlah_potong,
    j.status,
    j.potong_selesai,
    j.started_at,
    j.stopped_at,
    j.completed_at,
    j.created_at,
    u.nama AS user_nama,
    COUNT(l.id) AS total_log
FROM job_potong j
LEFT JOIN users u ON j.user_id = u.id
LEFT JOIN log_potong l ON j.id = l.job_id
GROUP BY j.id;

