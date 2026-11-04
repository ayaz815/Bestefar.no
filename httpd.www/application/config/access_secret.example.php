<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Copy to access_secret.php (gitignored) on the server and put in the same long
// random value as ACCESS_TOKEN_SECRET in the backend's .env on EC2.
// Generate one with:  php -r "echo bin2hex(random_bytes(32));"
$config['access_token_secret'] = '';
