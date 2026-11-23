-- Access control, Module 1 (additive only). Run once in phpMyAdmin AFTER a backup.
-- Every new column defaults so existing customers keep working exactly as today:
-- client_type NULL = "old customer, not yet moved to the new system".

ALTER TABLE `users`
  ADD COLUMN `client_type` enum('test','full','paid') DEFAULT NULL,
  ADD COLUMN `plan` enum('per_show','audio_simple','all_shows') DEFAULT NULL,
  ADD COLUMN `payment_registered` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `access_start` datetime DEFAULT NULL,
  ADD COLUMN `access_end` datetime DEFAULT NULL,
  ADD COLUMN `allow_audio` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `allow_image` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `allow_imagemusic` tinyint(1) NOT NULL DEFAULT 0,
  ADD COLUMN `allow_demo` tinyint(1) NOT NULL DEFAULT 0;

-- One row per show a customer may open (birthday plan: 2 rows; test users: chosen shows).
-- days_valid counts from first_opened_at; expires_at is filled in on first open.
CREATE TABLE IF NOT EXISTS `user_show_grants` (
  `grant_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL,
  `quiz_type` enum('audio','image','imagemusic') NOT NULL,
  `quiz_id` varchar(24) NOT NULL,
  `days_valid` int(11) DEFAULT NULL,
  `first_opened_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`grant_id`),
  UNIQUE KEY `uniq_user_quiz` (`users_id`,`quiz_type`,`quiz_id`),
  KEY `idx_user` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

