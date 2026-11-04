<?php
// Test harness: runs Access_model and the catalog view outside CodeIgniter with
// fake session / database / config. Reads a JSON scenario on stdin, prints JSON.
//   php access_harness.php <application dir> <action>
//   actions: annotate, open, render
define('BASEPATH', 'test');
$app = $argv[1];
$action = $argv[2];
$in = json_decode(file_get_contents('php://stdin'), true);

function base_url($path = '') { return 'https://bestefar.no/' . $path; }

class FakeQuery {
	private $rows;
	function __construct($rows) { $this->rows = array_values($rows); }
	function row_array() { return $this->rows ? $this->rows[0] : null; }
	function result_array() { return $this->rows; }
}
class FakeDb {
	public $tables, $updates = array(), $where = array();
	function __construct($tables) { $this->tables = $tables; }
	function get_where($table, $cond) {
		return new FakeQuery(array_filter(isset($this->tables[$table]) ? $this->tables[$table] : array(), function ($r) use ($cond) {
			foreach ($cond as $k => $v) { if ((string) $r[$k] !== (string) $v) return false; }
			return true;
		}));
	}
	function where($k, $v = null, $escape = true) { $this->where[] = $v === null ? $k : array($k => $v); return $this; }
	function update($table, $data) { $this->updates[] = array('table' => $table, 'where' => $this->where, 'data' => $data); $this->where = array(); return true; }
}
class FakeLoader {
	function database() {}
	function helper($name) { global $app; require_once $app . '/helpers/' . $name . '_helper.php'; }
}
class FakeConfig {
	public $enabled;
	function load() {}
	public $secret = '';
	function item($key) { return $key === 'access_token_secret' ? $this->secret : $this->enabled; }
}
class FakeSession {
	public $data;
	function userdata($k) { return isset($this->data[$k]) ? $this->data[$k] : null; }
	function flashdata($k) { return isset($this->data['flash_' . $k]) ? $this->data['flash_' . $k] : null; }
}
class CI_Model {
	public static $deps;
	function __construct() {}
	function __get($k) { return self::$deps[$k]; }
}

$config = new FakeConfig(); $config->enabled = !empty($in['enabled']);
$config->secret = isset($in['secret']) ? $in['secret'] : '';
function log_message($level, $msg) { global $logged; $logged[] = $msg; }
$session = new FakeSession(); $session->data = isset($in['session']) ? $in['session'] : array();
$db = new FakeDb(isset($in['tables']) ? $in['tables'] : array());
CI_Model::$deps = array('load' => new FakeLoader(), 'config' => $config, 'session' => $session, 'db' => $db);

require $app . '/models/Access_model.php';
class TestAccess_model extends Access_model {
	public static $tier = 'restricted';
	protected function fetch_tier($type, $quiz_id) { return self::$tier; }
}
if (isset($in['tier'])) TestAccess_model::$tier = $in['tier'];
$model = new TestAccess_model();

if ($action === 'annotate') {
	echo json_encode(array('quizzes' => $model->annotate_catalog($in['quizzes'])));
} elseif ($action === 'open') {
	$d = $model->open($in['type'], $in['id'], isset($in['target']) ? $in['target'] : 'player');
	echo json_encode(array('decision' => $d, 'updates' => $db->updates));
} elseif ($action === 'refused') {
	echo json_encode(array('message' => $model->refused_message($in['reason'])));
} elseif ($action === 'render') {
	// Renders the view the way CodeIgniter would ($this = controller with session).
	$view = new class($session) { public $session; function __construct($s) { $this->session = $s; }
		function render($file, $vars) { extract($vars); ob_start(); include $file; return ob_get_clean(); } };
	echo json_encode(array('html' => $view->render(!empty($in['view']) ? $in['view'] : $app . '/views/frontend/sporreleker.php', $in['vars'])));
}
