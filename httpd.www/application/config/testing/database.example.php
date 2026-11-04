<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Online test site database. Copy to database.php (kept out of git) and fill in the
// second one.com database, never the live bestefar_no one.


$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'TEST_DATABASE_USER',
	'password' => 'TEST_DATABASE_PASSWORD',
	'database' => 'TEST_DATABASE_NAME',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
