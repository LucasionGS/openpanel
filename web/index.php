<?php
require __DIR__ . "/core/autoload.php";

$path = $_SERVER["REQUEST_URI"];
$path = explode("?", $path)[0];

try {
  require __DIR__ . "/config.php";
} catch (\Throwable $th) {
  if ($path !== "/install") {
    header("Location: /install");
    exit;
  }
}

layout(__DIR__ . "/pages" . $path);