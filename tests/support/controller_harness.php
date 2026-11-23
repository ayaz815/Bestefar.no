<?php
// Test harness: runs a real website controller outside CodeIgniter with fake
// session / router / database. JSON scenario on stdin, JSON out (also when the
// controller exits early, via the shutdown function).
//   php controller_harness.php <application dir> <action>
//   actions:
//     ajax   {method, admin_login}      -> is the Ajax controller allowed to run that method?
//     shows  {param1, param2, rows}     -> what Home::shows does with those URL segments
define('BASEPATH', 'test');
$app = $argv[1];
$action = $argv[2];
$in = json_decode(file_get_contents('php://stdin'), true);
$out = array('status' => 200, 'exited' => false, 'constructed' => false);

register_shutdown_function(function () {
	global $out;
	$out['echo'] = ob_get_level() ? ob_get_clean() : '';
	echo json_encode($out);
});
ob_start();

class Show404 extends Exception {}
function show_404() { throw new Show404('404'); }
function base_url($path = '') { return 'http://test/' . $path; }
function redirect($to) { global $out; $out['redirect'] = $to; throw new Exception('redirect'); }
function set_status_header($code) { global $out; $out['status'] = $code; }

class FakeQuery {
	private $rows;
	function __construct($rows) { $this->rows = $rows; }
	function result_array() { return $this->rows; }
}
class FakeDb {
	public $where = array(), $rows = array();
	function select($c) { return $this; }
	function from($t) { $this->from = $t; return $this; }
	function where($k, $v) { $this->where[] = array($this->from, $k, $v); return $this; }
	function get() { return new FakeQuery($this->rows); }
}
class FakeSession {
	public $data = array();
	function userdata($k) { return isset($this->data[$k]) ? $this->data[$k] : null; }
}
class FakeRouter {
	public $method = 'index';
	function fetch_method() { return $this->method; }
}
class FakeOutput { function set_status_header($code) { set_status_header($code); } }
class FakeLoader {
	public $views = array();
	function library($n) {}
	function view($name, $data = array()) { $this->views[] = array('name' => $name, 'data' => $data); }
}
class FakeAdminModel { function get_row_id($t, $id) { return array(); } }
class CI_Controller {
	public $db, $session, $router, $output, $load, $Admin_model;
	function __construct() {
		global $deps;
		foreach ($deps as $k => $v) { $this->$k = $v; }
	}
}

$deps = array(
	'db' => new FakeDb(), 'session' => new FakeSession(), 'router' => new FakeRouter(),
	'output' => new FakeOutput(), 'load' => new FakeLoader(), 'Admin_model' => new FakeAdminModel(),
);

if ($action === 'ajax') {
	$deps['router']->method = $in['method'];
	if (!empty($in['admin_login'])) { $deps['session']->data['admin_login'] = $in['admin_login']; }
	require $app . '/controllers/Ajax.php';
	$out['exited'] = true; // stays true only if the constructor stops the request
	new Ajax();
	$out['exited'] = false;
	$out['constructed'] = true;
} elseif ($action === 'shows') {
	$deps['db']->rows = $in['rows'];
	require $app . '/controllers/Home.php';
	$home = new Home();
	try {
		$home->shows($in['param1'], $in['param2']);
		$view = end($deps['load']->views);
		$out['view'] = $view['name'];
		$out['param1'] = $view['data']['param1'];
		$out['param2'] = $view['data']['param2'];
	} catch (Show404 $e) {
		$out['status'] = 404;
	}
	$out['where'] = $deps['db']->where;
}
