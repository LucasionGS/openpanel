<?php
require(__DIR__ . "/../config.php");
$sql = new Database($CFG->db_host, $CFG->db_user, $CFG->db_password, $CFG->db_database);