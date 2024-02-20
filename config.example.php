<?php
$CFG = new stdClass();
$CFG->debug = true;

if ($CFG->debug) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
}

$CFG->db_host     = "localhost";
$CFG->db_user     = "";
$CFG->db_password = "";
$CFG->db_database = "openpanel";