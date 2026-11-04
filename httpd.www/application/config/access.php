<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Master switch for the new customer access rules (see ACCESS_CONTROL_PLAN.md).
// FALSE = site behaves exactly as before. Do not switch on until the existing
// customers have been moved to a client type and the catalog (Module 3) is live.
$config['access_control_enabled'] = FALSE;
