<?php

// start the session and error reporting
ob_start();
require __DIR__ . '/customFunctions.php';
$start_successful = start_session();

error_reporting(-1);

// load file locations
require 'pathfinder.class.php';
$_PATH = new Pathfinder();

// load custom functions and parse
require $_PATH->get('Parse');

$_CONFIG = Parse::fromConfig($_PATH->get('Config'), true);

if (!$start_successful) {
    header("Location: " . $_PATH->get('Code') . '/relogin.php');
    exit;
}
