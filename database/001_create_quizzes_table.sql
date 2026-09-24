-- New table for the "Spørreleker" quiz catalog (see Catalog_Quiz.pdf).
-- One row per quiz; the public catalog page lists these, sorted by date,
-- with a category popup filter on quiz_type.

CREATE TABLE IF NOT EXISTS `quizzes` (
  `quizzes_id` int NOT NULL AUTO_INCREMENT,
  `quiz_type` enum('AudioQuiz','BildeQuiz','TekstQuiz','BilderMusikk') COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `question_count` int NOT NULL DEFAULT '0',
  `info` text COLLATE utf8mb4_general_ci,
  `content_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Active','Inactive','Draft') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Active',
  `q_order` int NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`quizzes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
