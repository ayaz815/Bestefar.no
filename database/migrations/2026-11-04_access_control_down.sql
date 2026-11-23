-- Rolls back 2026-11-04_access_control_up.sql. Deletes the new columns and the grants table
-- (and whatever was typed into them). Back up first.
DROP TABLE IF EXISTS `user_show_grants`;
ALTER TABLE `users`
  DROP COLUMN `client_type`, DROP COLUMN `plan`, DROP COLUMN `payment_registered`,
  DROP COLUMN `access_start`, DROP COLUMN `access_end`, DROP COLUMN `allow_audio`,
  DROP COLUMN `allow_image`, DROP COLUMN `allow_imagemusic`, DROP COLUMN `allow_demo`;
