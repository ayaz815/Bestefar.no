-- Undo for 2026-11-23_admin_password_up.sql.
-- Only safe while every admin password is still plain text (no admin has logged in
-- with the new files). Once a password is a hash, shrinking the column cuts it and
-- that admin can no longer log in: restore the backup instead.

ALTER TABLE `admin` MODIFY `password` varchar(55) NOT NULL;
