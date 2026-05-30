-- =============================================
-- DATABASE INSTAGRAM API
-- Jalankan SQL ini di phpMyAdmin / MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS `instagram_api` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `instagram_api`;

-- Tabel untuk menyimpan access token Instagram
CREATE TABLE IF NOT EXISTS `access_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email user pemilik koneksi',
    `ig_user_id` VARCHAR(100) NOT NULL COMMENT 'Instagram User ID',
    `username` VARCHAR(255) DEFAULT NULL COMMENT 'Instagram Username',
    `name` VARCHAR(255) DEFAULT NULL COMMENT 'Nama akun',
    `profile_picture_url` TEXT DEFAULT NULL COMMENT 'URL foto profil Instagram',
    `followers_count` INT DEFAULT 0 COMMENT 'Jumlah followers terakhir',
    `media_count` INT DEFAULT 0 COMMENT 'Jumlah media terakhir',
    `access_token` TEXT NOT NULL COMMENT 'Access Token dari OAuth',
    `token_type` VARCHAR(50) DEFAULT 'bearer',
    `expires_at` DATETIME DEFAULT NULL COMMENT 'Kapan token expired',
    `page_id` VARCHAR(100) DEFAULT NULL COMMENT 'Facebook Page ID terkait',
    `page_access_token` TEXT DEFAULT NULL COMMENT 'Page Access Token',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_ig_user_id` (`ig_user_id`)
) ENGINE=InnoDB COMMENT='Menyimpan token akses Instagram';

-- Tabel untuk log webhook (semua event masuk)
CREATE TABLE IF NOT EXISTS `webhook_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `object` VARCHAR(100) DEFAULT NULL COMMENT 'Tipe object: instagram, page, dll',
    `entry_id` VARCHAR(100) DEFAULT NULL COMMENT 'ID dari entry',
    `event_type` VARCHAR(100) DEFAULT NULL COMMENT 'Tipe event: comments, messages, dll',
    `field` VARCHAR(100) DEFAULT NULL COMMENT 'Field yang berubah',
    `value` JSON DEFAULT NULL COMMENT 'Nilai/data dari webhook',
    `raw_payload` JSON DEFAULT NULL COMMENT 'Payload mentah dari Meta',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Log semua webhook event dari Meta/Instagram';

-- Tabel untuk menyimpan komentar
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `comment_id` VARCHAR(100) NOT NULL COMMENT 'ID komentar dari Instagram',
    `ig_user_id` VARCHAR(100) DEFAULT NULL COMMENT 'Instagram User ID pemilik akun target',
    `media_id` VARCHAR(100) DEFAULT NULL COMMENT 'ID media/post yang dikomentari',
    `parent_id` VARCHAR(100) DEFAULT NULL COMMENT 'ID komentar parent (jika reply)',
    `from_id` VARCHAR(100) DEFAULT NULL COMMENT 'ID user yang komentar',
    `from_username` VARCHAR(255) DEFAULT NULL COMMENT 'Username yang komentar',
    `text` TEXT DEFAULT NULL COMMENT 'Isi komentar',
    `like_count` INT DEFAULT 0 COMMENT 'Jumlah like komentar',
    `timestamp` DATETIME DEFAULT NULL COMMENT 'Waktu komentar dibuat',
    `is_from_webhook` TINYINT(1) DEFAULT 0 COMMENT '1=dari webhook, 0=dari API pull',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_comment_id` (`comment_id`)
) ENGINE=InnoDB COMMENT='Data komentar Instagram';

-- Tabel untuk menyimpan media/post
CREATE TABLE IF NOT EXISTS `media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `media_id` VARCHAR(100) NOT NULL COMMENT 'ID media dari Instagram',
    `ig_user_id` VARCHAR(100) DEFAULT NULL,
    `media_type` VARCHAR(50) DEFAULT NULL COMMENT 'IMAGE, VIDEO, CAROUSEL_ALBUM',
    `media_url` TEXT DEFAULT NULL,
    `permalink` TEXT DEFAULT NULL,
    `caption` TEXT DEFAULT NULL,
    `timestamp` DATETIME DEFAULT NULL,
    `like_count` INT DEFAULT 0,
    `comments_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_media_id` (`media_id`)
) ENGINE=InnoDB COMMENT='Data media/post Instagram';

-- Tabel untuk menyimpan pesan (DM) - Jika menggunakan Instagram Messaging API
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `message_id` VARCHAR(100) NOT NULL,
    `ig_user_id` VARCHAR(100) DEFAULT NULL COMMENT 'Instagram User ID pemilik akun target',
    `sender_id` VARCHAR(100) DEFAULT NULL,
    `recipient_id` VARCHAR(100) DEFAULT NULL,
    `message_text` TEXT DEFAULT NULL,
    `attachments` JSON DEFAULT NULL,
    `timestamp` DATETIME DEFAULT NULL,
    `is_from_webhook` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_message_id` (`message_id`)
) ENGINE=InnoDB COMMENT='Data pesan/DM Instagram';

-- Tabel template balasan cepat dan auto-reply
CREATE TABLE IF NOT EXISTS `reply_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_email` VARCHAR(255) DEFAULT NULL,
    `ig_user_id` VARCHAR(100) DEFAULT NULL,
    `name` VARCHAR(120) NOT NULL,
    `channel` VARCHAR(20) DEFAULT 'all',
    `keyword` VARCHAR(120) DEFAULT NULL,
    `response_text` TEXT NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `auto_reply` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Template balasan cepat dan aturan auto-reply';

-- Tabel log pengiriman auto-reply
CREATE TABLE IF NOT EXISTS `auto_reply_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT DEFAULT NULL,
    `ig_user_id` VARCHAR(100) DEFAULT NULL,
    `channel` VARCHAR(20) DEFAULT NULL,
    `target_id` VARCHAR(100) DEFAULT NULL,
    `request_payload` JSON DEFAULT NULL,
    `response_payload` JSON DEFAULT NULL,
    `status` VARCHAR(30) DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB COMMENT='Log upaya pengiriman auto-reply';
