<?php
require_once __DIR__ . "/core/autoload.php";

$path = $_SERVER["REQUEST_URI"];
$path = explode("?", $path)[0];

layout(function() {
  global $path;
  // return require_once( __DIR__ . "/pages" . $path);
  return __DIR__ . "/pages" . $path;
});