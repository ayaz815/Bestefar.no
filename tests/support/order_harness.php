<?php
// Test harness: runs Order_model and the shared mailer (Admin_model::send_site_email)
// outside CodeIgniter with fake session / database / e-mail. JSON scenario on stdin, JSON out.
//   php order_harness.php <application dir> <action>
//   actions: place, customer, for_customer, mail, admin_login, admin_profile
define('BASEPATH', 'test');
$app = $argv[1];
$action = $argv[2];
$in = json_decode(file_get_contents('php://stdin'), true);
$logged = array();

function base_url($path = '') { return 'https://bestefar.no/' . $path; }
function log_message($level, $msg) { global $logged; $logged[] = $level . ': ' . $msg; }
function show_error($msg) { throw new Exception('show_error: ' . $msg); }

class FakeQuery {
	private $rows;
	function __construct($rows) { $this->rows = array_values($rows); }
	function row_array() { return $this->rows ? $this->rows[0] : null; }
	function row() { return $this->rows ? (object) $this->rows[0] : null; }
	function num_rows() { return count($this->rows); }
	function result_array() { return $this->rows; }
	function result() { return array_map(function ($r) { return (object) $r; }, $this->rows); }
}
class FakeDb {
	public $tables, $inserts = array(), $updates = array(), $order = null, $pending = array(), $from = null;
	function __construct($tables) { $this->tables = $tables; }
	function where($k, $v) { $this->pending[$k] = $v; return $this; }
	// Query-builder style used by older code: from()->where()->get().
	function from($table) { $this->from = $table; return $this; }
	function get() { $q = $this->get_where($this->from, $this->pending); $this->pending = array(); return $q; }
	function update($table, $data) { $this->updates[] = array('table' => $table, 'where' => $this->pending, 'data' => $data); $this->pending = array(); return true; }
	// Only used for SHOW COLUMNS: the scenario's "columns" rows.
	function query($sql) { global $in; return new FakeQuery(isset($in['columns']) ? $in['columns'] : array()); }
	function select($cols = "*") { return $this; }
	function order_by($col, $dir) { $this->order = array($col, $dir); return $this; }
	function get_where($table, $cond) {
		$rows = array_filter(isset($this->tables[$table]) ? $this->tables[$table] : array(), function ($r) use ($cond) {
			foreach ($cond as $k => $v) { if ((string) $r[$k] !== (string) $v) return false; }
			return true;
		});
		if ($this->order) {
			list($col, $dir) = $this->order;
			usort($rows, function ($a, $b) use ($col, $dir) { return strcmp($a[$col], $b[$col]) * ($dir === 'DESC' ? -1 : 1); });
			$this->order = null;
		}
		return new FakeQuery($rows);
	}
	function insert($table, $data) { $this->inserts[] = array('table' => $table, 'data' => $data); return true; }
	function table_exists($table) { return isset($this->tables[$table]); }
}
class FakeEmail {
	public $sent = array(), $fail = false, $current = array(), $loads = 0;
	function clear() { $this->current = array(); }
	function from($a, $n) { $this->current['from'] = $a; }
	function to($to) { $this->current['to'] = $to; }
	function subject($s) { $this->current['subject'] = $s; }
	function message($m) { $this->current['message'] = $m; }
	function send($auto_clear = TRUE) { if ($this->fail) return false; $this->sent[] = $this->current; return true; }
	function print_debugger($include = array()) { return 'smtp refused'; }
}
class FakeLoader {
	function database() {}
	function helper($name) { global $app; if ($name === 'date') return; require_once $app . '/helpers/' . $name . '_helper.php'; }
	function library($name, $config = null) { CI_Model::$deps['email']->loads++; }
	function model($name) {}
}
class FakeOutput { function set_header($h) {} }
class FakeSession {
	public $data;
	function userdata($k) { return isset($this->data[$k]) ? $this->data[$k] : null; }
	function set_userdata($k, $v) { $this->data[$k] = $v; }
}
class FakeInput {
	public $data;
	function post($k = null) { return $k === null ? $this->data : (isset($this->data[$k]) ? $this->data[$k] : null); }
}
class CI_Model {
	public static $deps;
	function __construct() {}
	function __get($k) { return self::$deps[$k]; }
}
class Access_model {
	const QUIZ_TYPES = array('AudioQuiz' => 'audio', 'BildeQuiz' => 'image', 'BilderMusikk' => 'imagemusic');
}

$db = new FakeDb(isset($in['tables']) ? $in['tables'] : array());
$email = new FakeEmail(); $email->fail = !empty($in['mail_fails']);
$session = new FakeSession(); $session->data = isset($in['session']) ? $in['session'] : array();
$input = new FakeInput(); $input->data = isset($in['post']) ? $in['post'] : array();
CI_Model::$deps = array('input' => $input, 'db' => $db, 'load' => new FakeLoader(), 'session' => $session, 'email' => $email, 'output' => new FakeOutput());

// The real Admin_model, with the catalog read from the scenario instead of the quiz API.
require $app . '/models/Admin_model.php';
class TestAdminModel extends Admin_model {
	function get_quizzes_catalog($quiz_type = '', $sort = '', $dir = 'desc') { global $in; return isset($in['catalog']) ? $in['catalog'] : array(); }
}
$admin = new TestAdminModel();
CI_Model::$deps['Admin_model'] = $admin;

require $app . '/models/Order_model.php';
$orders = new Order_model();

$out = array();
try {
	if ($action === 'place') {
		$choices = $orders->show_choices();
		$out['choices'] = $choices;
		$out['result'] = $orders->place($in['customer'], $in['post'], $choices);
	} elseif ($action === 'customer') {
		$out['customer'] = $orders->current_customer();
	} elseif ($action === 'for_customer') {
		$out['orders'] = $orders->for_customer($in['users_id']);
	} elseif ($action === 'mail') {
		$out['result'] = $admin->send_site_email($in['to'], $in['subject'], $in['html']);
	} elseif ($action === 'admin_login') {
		$row = $admin->admin_login_varify();
		$out['result'] = $row ? (array) $row : false;
	} elseif ($action === 'admin_profile') {
		$admin->update_admin_profile();
	}
} catch (Exception $ex) {
	$out['exception'] = $ex->getMessage();
}
$out['inserts'] = $db->inserts;
$out['updates'] = $db->updates;
$out['sent'] = $email->sent;
$out['library_loads'] = $email->loads;
$out['logged'] = $logged;
echo json_encode($out);
