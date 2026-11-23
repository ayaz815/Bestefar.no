-- Admin passwords as hashes (ACCESS_CONTROL_PLAN.md, Module 8 / Module 0 follow-up, 23 Nov 2026).
-- Widens admin.password so it can hold a hash (60 characters; it was varchar(55)).
-- Nothing else changes: each admin's password is turned into a hash the next time
-- they log in. Run in phpMyAdmin AFTER a backup. Safe to run before or after the upload.

ALTER TABLE `admin` MODIFY `password` varchar(255) NOT NULL;
