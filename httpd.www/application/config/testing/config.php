<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Online test site (ACCESS_CONTROL_PLAN.md Module 6). Loaded on top of config/config.php.
$config['base_url'] = 'https://staging.bestefar.no/';

// Without its own database file CodeIgniter would fall back to the LIVE database: refuse instead.
if (!is_file(__DIR__ . '/database.php')) {
	exit('Test site: application/config/testing/database.php is missing.');
}
