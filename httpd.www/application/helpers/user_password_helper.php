<?php
defined('BASEPATH') OR define('BASEPATH', 'standalone'); // lets tests load this file without CodeIgniter

// Customer (users table) password handling. Old rows hold plain text; they are
// converted to a hash the first time that customer logs in.

if (!function_exists('user_password_hash')) {
	function user_password_hash($plain) {
		return password_hash((string) $plain, PASSWORD_DEFAULT);
	}
}

if (!function_exists('user_password_is_hashed')) {
	function user_password_is_hashed($stored) {
		return (string) $stored !== '' && strlen($stored) >= 60 && strpos($stored, '$2y$') === 0;
	}
}

// Checks a typed password against a stored one (hash or legacy plain text).
// Returns 'ok', 'ok_upgrade' (matched plain text, caller should store a hash) or 'bad'.
if (!function_exists('user_password_check')) {
	function user_password_check($plain, $stored) {
		$plain = (string) $plain;
		$stored = (string) $stored;
		if ($plain === '' || $stored === '') {
			return 'bad';
		}
		if (user_password_is_hashed($stored)) {
			if (!password_verify($plain, $stored)) {
				return 'bad';
			}
			return password_needs_rehash($stored, PASSWORD_DEFAULT) ? 'ok_upgrade' : 'ok';
		}
		return hash_equals($stored, $plain) ? 'ok_upgrade' : 'bad';
	}
}

// Picks the one customer row (from rows sharing an email) whose password matches.
// Returns array('row' => row|null, 'new_hash' => string|null).
if (!function_exists('user_password_pick_login')) {
	function user_password_pick_login(array $rows, $plain) {
		foreach ($rows as $row) {
			$result = user_password_check($plain, is_object($row) ? $row->password : $row['password']);
			if ($result === 'bad') {
				continue;
			}
			return array('row' => $row, 'new_hash' => $result === 'ok_upgrade' ? user_password_hash($plain) : null);
		}
		return array('row' => null, 'new_hash' => null);
	}
}
