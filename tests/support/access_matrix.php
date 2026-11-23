<?php
// Test helper: runs access_can_open() over every combination of the lists on stdin
// ({users, quizzes, grant_sets, now}) in the order users > quizzes > grant sets >
// switch (on, off). Prints one "allowed|reason" line per case, so 100 000+ cases
// stay small. Usage: php access_matrix.php <application dir>
require $argv[1] . '/helpers/access_helper.php';
$in = json_decode(file_get_contents('php://stdin'), true);
$now = new DateTimeImmutable($in['now']);
$out = fopen('php://output', 'w');
foreach ($in['users'] as $user) {
	foreach ($in['quizzes'] as $quiz) {
		foreach ($in['grant_sets'] as $grants) {
			foreach (array(true, false) as $enabled) {
				$d = access_can_open($user, $quiz, $grants, $now, $enabled);
				fwrite($out, ($d['allowed'] ? '1' : '0') . '|' . $d['reason'] . "\n");
			}
		}
	}
}
