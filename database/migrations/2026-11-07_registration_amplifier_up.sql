-- Revised registration form (client, 7 Nov 2026): "Vi har forsterker og høyttalere".
-- Additive only. Run once in phpMyAdmin AFTER a backup and BEFORE uploading the new
-- Admin_model.php, or new registrations fail on the missing column.
-- NULL = customer registered before this question existed.

ALTER TABLE `users`
  ADD COLUMN `amplifier_speakers` varchar(3) DEFAULT NULL AFTER `projector`;
