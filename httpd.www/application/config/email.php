<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// SMTP settings for every e-mail the site sends (registration, orders).
// CodeIgniter's Email library reads this file automatically on load, so
// $this->load->library('email') is all a caller needs.
// (These used to be passed to a second load() call in Admin_model::add_new_user,
// which CodeIgniter skips once the library is loaded, so they were never used.)
$config['protocol']    = 'smtp';
$config['smtp_host']   = 'mailout.one.com';
$config['smtp_port']   = 587;
$config['smtp_user']   = 'support@bestefar.no';
$config['smtp_pass']   = ''; // set in email_secret.php (gitignored), see email_secret.example.php
$config['smtp_crypto'] = 'tls';
$config['mailtype']    = 'html';
$config['charset']     = 'utf-8';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";

if (is_file(__DIR__ . '/email_secret.php')) {
	include __DIR__ . '/email_secret.php';
}
