-- Per-quiz "info" text for the (i) popup on the Spørreleker catalog. The
-- generator apps and api.bestefar.no have no such field yet, so this holds
-- it locally, keyed to whichever quiz it's about (quiz_type + external_id).

CREATE TABLE IF NOT EXISTS `quiz_info` (
  `quiz_info_id` int NOT NULL AUTO_INCREMENT,
  `quiz_type` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `external_id` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `info` text COLLATE utf8mb4_general_ci,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`quiz_info_id`),
  UNIQUE KEY `quiz_type_external_id` (`quiz_type`, `external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
