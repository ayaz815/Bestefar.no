<?php
// Test harness: renders the real frontend page shell (views/frontend/index.php)
// outside CodeIgniter. Theme views become <!--view:name--> markers; the catalog
// view (views/frontend/sporreleker.php) is rendered for real with 2 sample rows.
//   php page_harness.php <application dir> <page_name>      -> whole page
//   php page_harness.php <application dir> partial          -> catalog view alone (AJAX sort/filter)
define('BASEPATH', 'test');
$app = rtrim($argv[1], '/');
$page = $argv[2];
define('FCPATH', dirname($app) . '/'); // httpd.www/

function base_url($path = '') { return 'https://bestefar.no/' . ltrim($path, '/'); }

$data = array(
	'page_name' => $page === 'partial' ? 'sporreleker' : $page,
	'quiz_type' => '', 'sort' => 'date', 'dir' => 'desc', 'access_message' => '',
	'quizzes' => array(
		array('external_id' => 'a1', 'name' => 'Elvis Presley', 'date' => '2026-09-25', 'quiz_type' => 'AudioQuiz',
			'question_count' => 16, 'info' => 'Rock', 'music_type' => 'Musikk'),
		array('external_id' => 'b2', 'name' => 'Om verdensrommet', 'date' => '2026-05-03', 'quiz_type' => 'BildeQuiz',
			'question_count' => 16, 'info' => '', 'music_type' => ''),
	),
);

class FakeLoader {
	public $app;
	public $data;
	public function view($name, $vars = array(), $return = false) {
		if ($name === 'frontend/sporreleker') {
			extract($this->data);
			include $this->app . '/views/frontend/sporreleker.php';
			return;
		}
		echo '<!--view:' . $name . '-->';
	}
}

class Page {
	public $load;
	public function render($file, $data) {
		extract($data);
		include $file;
	}
}

$p = new Page();
$p->load = new FakeLoader();
$p->load->app = $app;
$p->load->data = $data;
if ($page === 'partial') {
	$p->load->view('frontend/sporreleker');
} else {
	$p->render($app . '/views/frontend/index.php', $data);
}
