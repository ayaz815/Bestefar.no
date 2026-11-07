-- Undo for 2026-11-07_registration_amplifier_up.sql. Removes the answers customers gave:
-- run only with the user's explicit permission and after a backup.

ALTER TABLE `users`
  DROP COLUMN `amplifier_speakers`;
