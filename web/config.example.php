<?php
require_once __DIR__ . '/autoloader.php'; # Important
$CFG = new stdClass();
$CFG->debug = true;

if ($CFG->debug) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

$CFG->db_host     = "mysql";
$CFG->db_user     = "openpanel";
$CFG->db_password = "openpanel";
$CFG->db_database = "openpanel";