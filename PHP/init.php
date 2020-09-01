<?php

require __DIR__ . '/definitions/general.php';
require __DIR__ . '/definitions/init.php';
require __DIR__ . '/definitions/parse.class.php';
require __DIR__ . '/vendor/tcon.php';

error_reporting(E_ALL);
ini_set('auto_detect_line_endings', true);
define_exp_constants();
chdir(ROOT);
date_default_timezone_set(get_config('timezone'));
prepare_exception_handler();

if (defined('CURR_EXP')) initialize_page();
