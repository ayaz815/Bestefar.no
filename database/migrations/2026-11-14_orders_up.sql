-- Order form "Bestilling" (ACCESS_CONTROL_PLAN.md, Module 7, 14 Nov 2026).
-- Additive only: one new table, nothing existing changes. Run once in phpMyAdmin
-- AFTER a backup and BEFORE uploading the order form files.
-- An order grants nothing by itself; the admin sets access in "Customer access".

CREATE TABLE IF NOT EXISTS `orders` (
  `orders_id`  int(11) NOT NULL AUTO_INCREMENT,
  `users_id`   int(11) NOT NULL,
  `plan`       varchar(20) NOT NULL,              -- per_show | audio_simple | all_shows
  `price`      int(11) NOT NULL DEFAULT 0,        -- kr, the price shown when the customer ordered
  `shows`      text DEFAULT NULL,                 -- JSON [{"value":"audio:<id>","name":"..."}], per_show only
  `start_date` date DEFAULT NULL,                 -- wanted start, optional
  `comment`    text DEFAULT NULL,
  `status`     varchar(20) NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`orders_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
