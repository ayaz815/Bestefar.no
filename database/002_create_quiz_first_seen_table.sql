-- Bestefar's real audio quizzes live on api.bestefar.no and don't carry a
-- created/uploaded date. To still show a stable "Dato" for them in the
-- catalog, we remember the first time our own app saw each one.

CREATE TABLE IF NOT EXISTS `quiz_first_seen` (
  `quiz_first_seen_id` int NOT NULL AUTO_INCREMENT,
  `quiz_type` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `external_id` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `first_seen` datetime NOT NULL,
  PRIMARY KEY (`quiz_first_seen_id`),
  UNIQUE KEY `quiz_type_external_id` (`quiz_type`, `external_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
