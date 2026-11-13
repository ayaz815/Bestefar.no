<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Copy to email_secret.php (gitignored) on the server and put in the SMTP
// password of support@bestefar.no (one.com mail settings). Without it the site
// still works, but no e-mail is sent.
$config['smtp_pass'] = '';
