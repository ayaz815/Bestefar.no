-- Undo for 2026-11-14_orders_up.sql. Deletes every order customers have sent:
-- run only with the user's explicit permission and after a backup.

DROP TABLE IF EXISTS `orders`;
